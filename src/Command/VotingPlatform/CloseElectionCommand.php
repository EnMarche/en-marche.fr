<?php

namespace App\Command\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Repository\CommitteeElectionRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\ResultCalculator;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformSecondRoundNotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CloseElectionCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:step-3:close-election';

    /** @var SymfonyStyle */
    private $io;
    /** @var ElectionRepository */
    private $electionRepository;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var DesignationRepository */
    private $designationRepository;
    /** @var CommitteeElectionRepository */
    private $committeeElectionRepository;
    /** @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;
    /** @var ResultCalculator */
    private $resultManager;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    protected function configure()
    {
        $this->setDescription('Voting Platform: step 3: close election');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->closeElections();

        $this->notifyForEndForCandidacy();
    }

    private function closeElections(): void
    {
        $date = new \DateTime();

        $this->io->progressStart();

        while ($elections = $this->electionRepository->getElectionsToCloseOrWithoutResults($date, 50)) {
            foreach ($elections as $election) {
                $this->doCloseElection($election);

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        }

        $this->io->progressFinish();
    }

    private function doCloseElection(Election $election): void
    {
        // 1. compute election result
        $electionResult = $this->resultManager->computeElectionResult($election);

        // 2. close election or start the second round
        if ($election->isOpen()) {
            if ($election->canClose()) {
                $election->close();

                $this->entityManager->flush();

                $this->eventDispatcher->dispatch(new VotingPlatformElectionVoteIsOverEvent($election));
            } else {
                $election->startSecondRound($electionResult->getNotElectedPools($election->getCurrentRound()));

                $this->entityManager->flush();

                $this->eventDispatcher->dispatch(new VotingPlatformSecondRoundNotificationEvent($election));
            }
        }

        // 3. persist results
        $this->entityManager->flush();
    }

    private function notifyForEndForCandidacy(): void
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getWithFinishCandidacyPeriod($date);

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
                $this->notifyCommitteeElections($designation);
            }
        }

        $this->io->progressFinish();
    }

    public function notifyCommitteeElections(Designation $designation): void
    {
        while ($committeeElections = $this->committeeElectionRepository->findAllToNotify($designation)) {
            foreach ($committeeElections as $committeeElection) {
                $memberships = $this->committeeMembershipRepository->findVotingMemberships($committee = $committeeElection->getCommittee());

                foreach ($memberships as $membership) {
                    $this->eventDispatcher->dispatch(new CommitteeElectionCandidacyPeriodIsOverEvent(
                        $membership->getAdherent(),
                        $designation,
                        $committee
                    ));
                }

                $committeeElection->setAdherentNotified(true);

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        }
    }

    /** @required */
    public function setElectionRepository(ElectionRepository $electionRepository): void
    {
        $this->electionRepository = $electionRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setDesignationRepository(DesignationRepository $designationRepository): void
    {
        $this->designationRepository = $designationRepository;
    }

    /** @required */
    public function setCommitteeElectionRepository(CommitteeElectionRepository $committeeElectionRepository): void
    {
        $this->committeeElectionRepository = $committeeElectionRepository;
    }

    /** @required */
    public function setCommitteeMembershipRepository(CommitteeMembershipRepository $committeeMembershipRepository): void
    {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    /** @required */
    public function setResultManager(ResultCalculator $resultManager): void
    {
        $this->resultManager = $resultManager;
    }

    /** @required */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
