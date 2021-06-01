<?php

namespace App\VotingPlatform\Election\Listener;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\VoteResult;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformElectionVoteConfirmationMessage;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Election\Event\NewVote;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use App\VotingPlatform\Election\VoteCommandStateEnum;
use App\VotingPlatform\Election\VoteCommandStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FinishVoteCommandListener implements EventSubscriberInterface
{
    private $entityManager;
    private $security;
    private $voterRepository;
    private $electionRepository;
    private $candidateGroupRepository;
    private $storage;
    private $mailer;
    private $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        VoterRepository $voterRepository,
        ElectionRepository $electionRepository,
        CandidateGroupRepository $candidateGroupRepository,
        VoteCommandStorage $storage,
        MailerService $transactionalMailer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->voterRepository = $voterRepository;
        $this->electionRepository = $electionRepository;
        $this->candidateGroupRepository = $candidateGroupRepository;
        $this->storage = $storage;
        $this->mailer = $transactionalMailer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            sprintf('workflow.voting_process.completed.%s', VoteCommandStateEnum::TO_FINISH) => ['persistVote'],
        ];
    }

    public function persistVote(Event $event): void
    {
        /** @var VoteCommand $command */
        $command = $event->getSubject();

        if (!$command instanceof VoteCommand) {
            return;
        }

        $election = $this->electionRepository->findOneByUuid($command->getElectionUuid());

        if (!$election instanceof Election) {
            return;
        }

        $electionRound = $election->getCurrentRound();

        // 1. create vote history for the current voter
        $vote = $this->generateVote($electionRound);

        // 2. generate a unique key to save the vote result with
        $voterKey = VoteResult::generateVoterKey();

        // 3. create vote result with unique key
        $voteResult = $this->createVoteResult($electionRound, $command, $voterKey);

        // 4. delete voters from other voters lists for the same designation
        $voter = $vote->getVoter();
        foreach ($voter->getVotersListsForDesignation($election->getDesignation()) as $list) {
            if ($list->getId() === $election->getVotersList()->getId()) {
                continue;
            }

            $list->removeVoter($voter);
        }
        $voter->setIsGhost(false);

        $this->entityManager->persist($vote);
        $this->entityManager->persist($voteResult);

        $this->entityManager->flush();

        $this->saveVoterKeyInSession($voterKey);

        $this->sendVoteConfirmationEmail($vote, $voterKey);

        $this->eventDispatcher->dispatch(new NewVote($election, $voter));
    }

    private function generateVote(ElectionRound $electionRound): Vote
    {
        /** @var Adherent $adherent */
        $adherent = $this->security->getUser();
        $voter = $this->voterRepository->findForAdherent($adherent);

        return new Vote($voter, $electionRound);
    }

    private function createVoteResult(ElectionRound $electionRound, VoteCommand $command, string $voterKey): VoteResult
    {
        $voteResult = new VoteResult($electionRound, $voterKey);

        foreach ($command->getChoicesByPools() as $poolId => $choice) {
            /** @var ElectionPool $pool */
            $pool = $this->entityManager->getPartialReference(ElectionPool::class, $poolId);

            if ($command->isMajorityVote()) {
                foreach ($choice as $candidateGroupUuid => $mention) {
                    if (!$group = $this->candidateGroupRepository->findOneByUuid($candidateGroupUuid)) {
                        throw new \RuntimeException(sprintf('Candidate group not found with uuid "%s"', $choice));
                    }

                    $voteChoice = new VoteChoice($pool);
                    $voteChoice->setCandidateGroup($group);
                    $voteChoice->setMention($mention);

                    $voteResult->addVoteChoice($voteChoice);
                }
            } else {
                $voteChoice = new VoteChoice($pool);

                if (VoteChoice::BLANK_VOTE_VALUE === $choice) {
                    $voteChoice->setIsBlank(true);
                } else {
                    if (!$group = $this->candidateGroupRepository->findOneByUuid($choice)) {
                        throw new \RuntimeException(sprintf('Candidate group not found with uuid "%s"', $choice));
                    }

                    $voteChoice->setCandidateGroup($group);
                }

                $voteResult->addVoteChoice($voteChoice);
            }
        }

        return $voteResult;
    }

    private function sendVoteConfirmationEmail(Vote $vote, string $voterKey): void
    {
        $this->mailer->sendMessage(VotingPlatformElectionVoteConfirmationMessage::create($vote, $voterKey));
    }

    private function saveVoterKeyInSession(string $voterKey): void
    {
        $this->storage->saveLastVoterKey($voterKey);
    }
}
