<?php

namespace App\Command\VotingPlatform;

use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\TerritorialCouncil\Election as TerritorialCouncilElection;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VotersList;
use App\Repository\CommitteeCandidacyRepository;
use App\Repository\CommitteeElectionRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\TerritorialCouncil\CandidacyRepository as TerritorialCouncilCandidacyRepository;
use App\Repository\TerritorialCouncil\ElectionRepository as TerritorialCouncilElectionRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOpenEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfigureCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:step-2:configure';

    /** @var DesignationRepository */
    private $designationRepository;
    /** @var CommitteeElectionRepository */
    private $committeeElectionRepository;
    /** @var SymfonyStyle */
    private $io;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var ElectionRepository */
    private $electionRepository;
    /** @var CommitteeCandidacyRepository */
    private $committeeCandidacyRepository;
    /** @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;
    /** @var VoterRepository */
    private $voterRepository;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var TerritorialCouncilElectionRepository */
    private $territorialCouncilElectionRepository;
    /** @var TerritorialCouncilCandidacyRepository */
    private $territorialCouncilCandidacyRepository;

    protected function configure()
    {
        $this
            ->setDescription('Voting Platform: step 2: create Election and voters/candidates lists')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in minutes for designation selection (1 min by default)', 1)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getIncomingDesignations(
            $date->modify(sprintf('+%d minutes', (int) $input->getOption('interval')))
        );

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if ($designation->isCommitteeType()) {
                $this->configureCommitteeElections($designation);
            } elseif (DesignationTypeEnum::COPOL === $designation->getType()) {
                $this->configureCopolElections($designation);
            } else {
                $this->io->error(sprintf('Unhandled designation type "%s"', $designation->getType()));
            }
        }

        $this->io->progressFinish();
    }

    private function configureCommitteeElections(Designation $designation): void
    {
        $offset = 0;

        while ($committeeElections = $this->committeeElectionRepository->findAllByDesignation($designation, $offset)) {
            foreach ($committeeElections as $committeeElection) {
                $committee = $committeeElection->getCommittee();

                if ($this->electionRepository->hasElectionForCommittee($committee, $designation)) {
                    continue;
                }

                if (!$this->isValidCommitteeElection($committeeElection, $designation)) {
                    continue;
                }

                $this->io->progressAdvance();

                $election = $this->createNewElection($designation);
                $election->getElectionEntity()->setCommittee($committee);

                $this->configureNewElectionForCommittee($election);
            }

            $this->entityManager->clear();

            $designation = $this->entityManager->merge($designation);

            $offset += \count($committeeElections);
        }
    }

    private function configureCopolElections(Designation $designation): void
    {
        $offset = 0;

        while ($territorialCouncilElections = $this->territorialCouncilElectionRepository->findAllByDesignation($designation, $offset)) {
            foreach ($territorialCouncilElections as $coTerrElection) {
                $coTerr = $coTerrElection->getTerritorialCouncil();

                if ($this->electionRepository->hasElectionForTerritorialCouncil($coTerr, $designation)) {
                    continue;
                }

                if (!$this->isValidTerritorialCouncilElection($coTerrElection)) {
                    continue;
                }

                $this->io->progressAdvance();

                $election = $election = $this->createNewElection($designation);
                $election->getElectionEntity()->setTerritorialCouncil($coTerr);

                if (($poll = $coTerrElection->getElectionPoll()) && $choice = $poll->getTopChoice()) {
                    $election->setAdditionalPlaces($choice->getValue());
                    $election->setAdditionalPlacesGender($poll->getGender());
                }

                $this->configureNewElectionForTerritorialCouncil($election, $coTerrElection);
            }

            $this->entityManager->clear();

            $designation = $this->entityManager->merge($designation);

            $offset += \count($territorialCouncilElections);
        }
    }

    private function configureNewElectionForTerritorialCouncil(
        Election $election,
        TerritorialCouncilElection $coTerrElection
    ): void {
        $electionRound = $election->getCurrentRound();
        $coTerr = $coTerrElection->getTerritorialCouncil();

        // Create candidates groups
        $candidacies = $this->territorialCouncilCandidacyRepository->findAllConfirmedForElection($coTerrElection);

        $pools = [];

        foreach ($candidacies as $candidacy) {
            if ($candidacy->isTaken()) {
                continue;
            }

            $adherent = $candidacy->getMembership()->getAdherent();

            $group = new CandidateGroup();
            $group->addCandidate($candidate = new Candidate(
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $candidacy->getGender(),
                $adherent
            ));

            $candidate->setImagePath($candidacy->getImagePath());
            $candidate->setBiography($candidacy->getBiography());
            $candidate->setFaithStatement($candidacy->getFaithStatement());
            $candidacy->take();

            if ($binome = $candidacy->getBinome()) {
                $adherent = $binome->getMembership()->getAdherent();

                $group->addCandidate($candidate = new Candidate(
                    $adherent->getFirstName(),
                    $adherent->getLastName(),
                    $binome->getGender(),
                    $adherent
                ));

                $candidate->setImagePath($binome->getImagePath());
                $candidate->setBiography($binome->getBiography());
                $candidate->setFaithStatement($binome->getFaithStatement());
                $binome->take();
            }

            if (!isset($pools[$candidacy->getQuality()])) {
                $pools[$candidacy->getQuality()] = new ElectionPool($candidacy->getQuality());
            }

            $pools[$candidacy->getQuality()]->addCandidateGroup($group);
        }

        foreach ($pools as $pool) {
            if ($pool->getCandidateGroups()) {
                $electionRound->addElectionPool($pool);
                $election->addElectionPool($pool);
            }
        }

        $list = $this->createVoterList(
            $election,
            array_map(function (TerritorialCouncilMembership $membership) { return $membership->getAdherent(); }, $coTerr->getMemberships()->toArray())
        );

        $this->entityManager->persist($list);
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
    }

    private function configureNewElectionForCommittee(Election $election): void
    {
        $electionRound = $election->getCurrentRound();
        $committee = $election->getElectionEntity()->getCommittee();

        // Create candidates groups
        $candidacies = $this->committeeCandidacyRepository->findConfirmedByCommittee($committee, $designation = $election->getDesignation());
        $pools = [];

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
            $pools = [
                $femalePool = new ElectionPool(ElectionPoolCodeEnum::FEMALE),
                $malePool = new ElectionPool(ElectionPoolCodeEnum::MALE),
            ];

            foreach ($candidacies as $candidacy) {
                $adherent = $candidacy->getCommitteeMembership()->getAdherent();

                $group = new CandidateGroup();
                $group->addCandidate($candidate = new Candidate(
                    $adherent->getFirstName(),
                    $adherent->getLastName(),
                    $candidacy->getGender(),
                    $adherent
                ));

                $candidate->setImagePath($candidacy->getImagePath());
                $candidate->setBiography($candidacy->getBiography());

                if ($candidate->isFemale()) {
                    $femalePool->addCandidateGroup($group);
                } else {
                    $malePool->addCandidateGroup($group);
                }
            }
        } elseif (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
            $pools = [
                $pool = new ElectionPool(ElectionPoolCodeEnum::COMMITTEE_SUPERVISOR),
            ];

            foreach ($candidacies as $candidacy) {
                if ($candidacy->isTaken()) {
                    continue;
                }

                $adherent = $candidacy->getCommitteeMembership()->getAdherent();

                $group = new CandidateGroup();
                $group->addCandidate($candidate = new Candidate(
                    $adherent->getFirstName(),
                    $adherent->getLastName(),
                    $candidacy->getGender(),
                    $adherent
                ));

                $candidate->setImagePath($candidacy->getImagePath());
                $candidate->setBiography($candidacy->getBiography());
                $candidate->setFaithStatement($candidacy->getFaithStatement());

                $pool->addCandidateGroup($group);
                $candidacy->take();

                if ($binome = $candidacy->getBinome()) {
                    $adherent = $binome->getCommitteeMembership()->getAdherent();

                    $group->addCandidate($candidate = new Candidate(
                        $adherent->getFirstName(),
                        $adherent->getLastName(),
                        $binome->getGender(),
                        $adherent
                    ));

                    $candidate->setImagePath($binome->getImagePath());
                    $candidate->setBiography($binome->getBiography());
                    $candidate->setFaithStatement($binome->getFaithStatement());

                    $binome->take();
                }
            }
        }

        foreach ($pools as $pool) {
            if ($pool->getCandidateGroups()) {
                $electionRound->addElectionPool($pool);
                $election->addElectionPool($pool);
            }
        }

        $memberships = $this->committeeMembershipRepository->findVotingForElectionMemberships($committee, $designation);

        $list = $this->createVoterList(
            $election,
            array_map(function (CommitteeMembership $membership) { return $membership->getAdherent(); }, $memberships)
        );

        // Mark as Ghost voter adherent who can vote in many committees
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
            foreach ($list->getVoters() as $voter) {
                if (\count($voter->getVotersListsForDesignation($designation)) > 1) {
                    $voter->setIsGhost(true);
                }
            }
        }

        $this->entityManager->persist($list);
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
    }

    private function isValidCommitteeElection(CommitteeElection $committeeElection, Designation $designation): bool
    {
        $committee = $committeeElection->getCommittee();

        // validate voters
        if (!$this->committeeMembershipRepository->committeeHasVotersForElection($committee, $designation)) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('Committee "%s" does not have any voters', $committee->getSlug()));
            }

            return false;
        }

        // validate candidatures
        if (!$this->committeeCandidacyRepository->hasConfirmedCandidacies($committee, $designation)) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('Committee "%s" does not have at least 1 candidate', $committee->getSlug()));
            }

            return false;
        }

        return true;
    }

    private function createNewElection(Designation $designation): Election
    {
        return new Election(
            $this->entityManager->getPartialReference(Designation::class, $designation->getId()),
            null,
            [new ElectionRound()],
            new ElectionEntity()
        );
    }

    private function createVoterList(Election $election, array $adherents): VotersList
    {
        $list = new VotersList($election);

        foreach ($adherents as $adherent) {
            $list->addVoter($this->voterRepository->findForAdherent($adherent) ?? new Voter($adherent));
        }

        return $list;
    }

    private function isValidTerritorialCouncilElection(TerritorialCouncilElection $coTerrElection): bool
    {
        $coTerr = $coTerrElection->getTerritorialCouncil();

        // validate voters
        if ($coTerr->getMemberships()->isEmpty()) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('CoTerr "%s" does not have any voters', $coTerr->getUuid()->toString()));
            }

            return false;
        }

        // validate candidatures
        $candidacies = $this->territorialCouncilCandidacyRepository->findAllConfirmedForElection($coTerrElection);

        if (0 === \count($candidacies)) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('CoTerr "%s" does not have at least 1 candidate', $coTerr->getUuid()->toString()));
            }

            return false;
        }

        return true;
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
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setElectionRepository(ElectionRepository $electionRepository): void
    {
        $this->electionRepository = $electionRepository;
    }

    /** @required */
    public function setCommitteeCandidacyRepository(CommitteeCandidacyRepository $committeeCandidacyRepository): void
    {
        $this->committeeCandidacyRepository = $committeeCandidacyRepository;
    }

    /** @required */
    public function setCommitteeMembershipRepository(CommitteeMembershipRepository $committeeMembershipRepository): void
    {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    /** @required */
    public function setVoterRepository(VoterRepository $voterRepository): void
    {
        $this->voterRepository = $voterRepository;
    }

    /** @required */
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /** @required */
    public function setTerritorialCouncilElectionRepository(
        TerritorialCouncilElectionRepository $territorialCouncilElectionRepository
    ): void {
        $this->territorialCouncilElectionRepository = $territorialCouncilElectionRepository;
    }

    /** @required */
    public function setTerritorialCouncilCandidacyRepository(
        TerritorialCouncilCandidacyRepository $territorialCouncilCandidacyRepository
    ): void {
        $this->territorialCouncilCandidacyRepository = $territorialCouncilCandidacyRepository;
    }
}
