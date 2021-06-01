<?php

namespace App\DataFixtures\ORM;

use App\Entity\CommitteeCandidacy;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VoteResult;
use App\Entity\VotingPlatform\VotersList;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\MajorityVoteMentionEnum;
use App\VotingPlatform\Election\ResultCalculator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class LoadVotingPlatformElectionData extends Fixture implements DependentFixtureInterface
{
    public const ELECTION_UUID1 = 'd678c30a-a94b-4ecf-8cfc-0e06d1fb16df';
    public const ELECTION_UUID2 = '278ec098-e5f2-45e3-9faf-a9b2cb9305fd';
    public const ELECTION_UUID3 = 'b81c3585-c802-48f6-9dca-19d1d4e08c44';
    public const ELECTION_UUID4 = '642ee8e6-916b-43b2-b0f9-8a821e5d9a2b';
    public const ELECTION_UUID5 = '13dd81bf-df09-487b-813d-8cbec95189aa';
    public const ELECTION_UUID6 = '071a3abc-fb4c-43c0-91b9-7cbeb1e02b92';
    public const ELECTION_UUID7 = 'b58e5538-c6e7-10a4-88c3-de59305e61a8';
    public const ELECTION_UUID8 = '138140c6-1dd2-11b2-b23f-2b71345a2be1';
    public const ELECTION_UUID9 = '39949c8f-d233-1e20-905e-5e214c6a12f2';
    public const ELECTION_UUID10 = '4095339f-3aad-18ba-924f-adffe2085cd6';
    public const ELECTION_UUID11 = '13814072-1dd2-11b2-9593-b97d988be702';

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var ObjectManager
     */
    private $manager;
    private $voters = [];
    private $resultCalculator;

    public function __construct(ResultCalculator $resultCalculator)
    {
        $this->resultCalculator = $resultCalculator;
    }

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create('fr_FR');
        $this->manager = $manager;

        $this->loadCommitteeAdherentElections();

        $manager->flush();
    }

    private function loadCommitteeAdherentElections(): void
    {
        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-1'),
            Uuid::fromString(self::ELECTION_UUID1),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-6')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $this->loadVoters($election);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-2'),
            Uuid::fromString(self::ELECTION_UUID2),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-5')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID3),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-4')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID4),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->close();

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-4'),
            Uuid::fromString(self::ELECTION_UUID7),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->close();

        // -------------------------------------------

        // Election with started second round
        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID5),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-3')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->startSecondRound($round->getElectionPools());

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-7'),
            Uuid::fromString(self::ELECTION_UUID8),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity(null, $coTerr = $this->getReference('coTerr_92')));
        $this->loadTerritorialCouncilElectionCandidates($election, $coTerr);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-8'),
            Uuid::fromString(self::ELECTION_UUID9),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-13')));
        $this->loadCommitteeSupervisorElectionCandidates($election);
        $this->manager->persist($this->loadCommitteeSupervisorElectionVoters($election));

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-8'),
            Uuid::fromString(self::ELECTION_UUID10),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-14')));
        $this->loadCommitteeSupervisorElectionCandidates($election);
        $this->manager->persist($this->loadCommitteeSupervisorElectionVoters($election));

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-9'),
            Uuid::fromString(self::ELECTION_UUID11),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-15')));
        $this->loadCommitteeSupervisorElectionCandidates($election);
        $this->manager->persist($votersList = $this->loadCommitteeSupervisorElectionVoters($election));
        $this->loadMajorityVotes($round, $votersList, [
            [4, 1, 1],
            [4, 4, 0],
            [1, 2, 3],
            [4, 0, 1],
            [2, 3, 4],
        ]);
        $this->resultCalculator->computeElectionResult($election);

        // -------------------------------------------

        $this->manager->flush();
    }

    private function loadCommitteeAdherentElectionCandidates(Election $election): void
    {
        $currentRound = $election->getCurrentRound();

        $femalePool = new ElectionPool(ElectionPoolCodeEnum::FEMALE);
        $malePool = new ElectionPool(ElectionPoolCodeEnum::MALE);

        $currentRound->addElectionPool($femalePool);
        $currentRound->addElectionPool($malePool);

        $election->addElectionPool($femalePool);
        $election->addElectionPool($malePool);

        foreach ($this->getCandidates() as $candidate) {
            $group = new CandidateGroup();
            $group->addCandidate(clone $candidate);

            if ($candidate->isFemale()) {
                $femalePool->addCandidateGroup($group);
            } else {
                $malePool->addCandidateGroup($group);
            }
        }
    }

    private function loadCommitteeSupervisorElectionCandidates(Election $election): void
    {
        $currentRound = $election->getCurrentRound();

        $pool = new ElectionPool(ElectionPoolCodeEnum::COMMITTEE_SUPERVISOR);
        $currentRound->addElectionPool($pool);
        $election->addElectionPool($pool);

        $committee = $election->getElectionEntity()->getCommittee();
        $committeeElection = $committee->getCurrentElection();

        foreach ($committeeElection->getCandidacies() as $committeeCandidacy) {
            /** @var CommitteeCandidacy $committeeCandidacy */
            if (!$committeeCandidacy->isConfirmed() || $committeeCandidacy->isTaken()) {
                continue;
            }

            $group = new CandidateGroup();

            $group->addCandidate($candidate = new Candidate(
                $committeeCandidacy->getFirstName(),
                $committeeCandidacy->getLastName(),
                $committeeCandidacy->getGender(),
                $committeeCandidacy->getAdherent()
            ));
            $candidate->setImagePath($committeeCandidacy->getImagePath());
            $candidate->setBiography($committeeCandidacy->getBiography());
            $candidate->setFaithStatement($committeeCandidacy->getFaithStatement());
            $committeeCandidacy->take();

            if ($committeeCandidaciesGroup = $committeeCandidacy->getCandidaciesGroup()) {
                foreach ($committeeCandidaciesGroup->getCandidacies() as $committeeCandidacy) {
                    if ($committeeCandidacy->isTaken()) {
                        continue;
                    }

                    $group->addCandidate($candidate = new Candidate(
                        $committeeCandidacy->getFirstName(),
                        $committeeCandidacy->getLastName(),
                        $committeeCandidacy->getGender(),
                        $committeeCandidacy->getAdherent()
                    ));
                    $candidate->setImagePath($committeeCandidacy->getImagePath());
                    $candidate->setBiography($committeeCandidacy->getBiography());
                    $candidate->setFaithStatement($committeeCandidacy->getFaithStatement());
                    $committeeCandidacy->take();
                }
            }

            $pool->addCandidateGroup($group);
        }
    }

    private function loadCommitteeSupervisorElectionVoters(Election $election): VotersList
    {
        $list = new VotersList($election);

        foreach ($election->getElectionPools() as $pool) {
            foreach ($pool->getCandidateGroups() as $candidateGroup) {
                foreach ($candidateGroup->getCandidates() as $candidate) {
                    $adherent = $candidate->getAdherent();
                    $list->addVoter($this->voters[$adherent->getId()] ?? $this->voters[$adherent->getId()] = new Voter($adherent));
                }
            }
        }

        return $list;
    }

    /**
     * @return Candidate[]
     */
    private function getCandidates(): array
    {
        $candidates = [
            $candidate1 = new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE, $this->getReference('adherent-1')),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE, $this->getReference('adherent-2')),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE, $this->getReference('adherent-3')),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE, $this->getReference('adherent-4')),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE, $this->getReference('adherent-5')),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE, $this->getReference('adherent-6')),
            $candidate2 = new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE, $this->getReference('adherent-7')),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE, $this->getReference('adherent-8')),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE, $this->getReference('adherent-9')),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE, $this->getReference('adherent-10')),
        ];

        $candidate1->setBiography($this->faker->paragraph(10));
        $candidate2->setBiography($this->faker->paragraph(10));

        /** @var CommitteeCandidacy $committeeCandidacy */
        $committeeCandidacy = $this->getReference('committee-candidacy-committee_adherent-1');

        foreach ($candidates as $index => $candidate) {
            if (0 === $index % 2) {
                $candidate->setImagePath($committeeCandidacy->getImagePath());
            }
        }

        shuffle($candidates);

        return $candidates;
    }

    private function loadVoters(Election $election): VotersList
    {
        $adherent1 = $this->getReference('assessor-1');
        $adherent2 = $this->getReference('adherent-20');

        $list = new VotersList($election);
        $list->addVoter($this->voters[$adherent1->getId()] ?? $this->voters[$adherent1->getId()] = new Voter($adherent1));
        $list->addVoter($this->voters[$adherent2->getId()] ?? $this->voters[$adherent2->getId()] = new Voter($adherent2));

        for ($i = 1; $i <= 9; ++$i) {
            $list->addVoter(new Voter());
        }

        $this->manager->persist($list);

        return $list;
    }

    private function loadResults(ElectionRound $electionRound, VotersList $votersList): void
    {
        $pools = $electionRound->getElectionPools();
        $counters = [];

        foreach ($votersList->getVoters() as $i => $voter) {
            // simulate abstention
            if (0 === $i % 7 || \in_array($voter, $this->voters, true)) {
                continue;
            }

            $result = new VoteResult($electionRound, VoteResult::generateVoterKey());

            foreach ($pools as $y => $pool) {
                $candidateGroups = $pool->getCandidateGroups();
                $totalGroups = \count($candidateGroups);

                $choice = new VoteChoice($pool);

                if (0 === $i % 10) {
                    $choice->setIsBlank(true);
                } else {
                    $choice->setCandidateGroup($candidateGroups[$index = rand(1, rand(1, $totalGroups) - 1)]);
                    !isset($counters[$pool->getId()][$index]) ? $counters[$pool->getId()][$index] = 1 : ++$counters[$pool->getId()][$index];
                }

                $result->addVoteChoice($choice);
            }

            $this->manager->persist(new Vote($voter, $electionRound));
            $this->manager->persist($result);
        }

        $this->manager->flush();

        $this->resultCalculator->computeElectionResult($electionRound->getElection());
    }

    private function loadTerritorialCouncilElectionCandidates(
        Election $election,
        TerritorialCouncil $territorialCouncil
    ): void {
        $currentElection = $territorialCouncil->getCurrentElection();
        /** @var TerritorialCouncilMembership[] $memberships */
        $memberships = $territorialCouncil->getMemberships()->toArray();
        $voterList = new VotersList($election);
        $this->manager->persist($voterList);
        $pools = [];

        foreach ($memberships as $membership) {
            $adherent = $membership->getAdherent();
            $voterList->addVoter($this->voters[$adherent->getId()] ?? $this->voters[$adherent->getId()] = new Voter($adherent));

            if ($candidacy = $membership->getCandidacyForElection($currentElection)) {
                if ($candidacy->isConfirmed()) {
                    $pools[$candidacy->getQuality()][] = $candidacy;
                }
            }
        }

        $currentRound = $election->getCurrentRound();

        foreach ($pools as $key => $candidacies) {
            $pool = new ElectionPool($key);

            foreach ($candidacies as $candidacy) {
                /** @var Candidacy $candidacy */
                if ($candidacy->isTaken()) {
                    continue;
                }

                $group = new CandidateGroup();

                $group->addCandidate($candidate = new Candidate(
                    $candidacy->getFirstName(),
                    $candidacy->getLastName(),
                    $candidacy->getGender(),
                    $candidacy->getAdherent()
                ));
                $candidate->setImagePath($candidacy->getImagePath());
                $candidate->setFaithStatement($candidacy->getFaithStatement());
                $candidate->setBiography($candidacy->getBiography());
                $candidacy->take();

                if ($candidaciesGroup = $candidacy->getCandidaciesGroup()) {
                    foreach ($candidaciesGroup->getCandidacies() as $candidacy) {
                        if ($candidacy->isTaken()) {
                            continue;
                        }

                        $group->addCandidate($candidate = new Candidate(
                            $candidacy->getFirstName(),
                            $candidacy->getLastName(),
                            $candidacy->getGender(),
                            $candidacy->getAdherent()
                        ));
                        $candidate->setImagePath($candidacy->getImagePath());
                        $candidate->setFaithStatement($candidacy->getFaithStatement());
                        $candidate->setBiography($candidacy->getBiography());
                        $candidacy->take();
                    }
                }

                $pool->addCandidateGroup($group);
            }

            $currentRound->addElectionPool($pool);
            $election->addElectionPool($pool);
        }
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeData::class,
            LoadCommitteeCandidacyData::class,
            LoadDesignationData::class,
            LoadTerritorialCouncilCandidacyData::class,
        ];
    }

    private function loadMajorityVotes(ElectionRound $electionRound, VotersList $votersList, array $votesData): void
    {
        $voters = $votersList->getVoters();
        shuffle($voters);

        foreach ($votesData as $voteRow) {
            $voter = array_shift($voters);
            $this->manager->persist(new Vote($voter, $electionRound));

            $result = new VoteResult($electionRound, VoteResult::generateVoterKey());

            foreach ($electionRound->getElectionPools() as $pool) {
                foreach ($voteRow as $i => $mentionIndex) {
                    $result->addVoteChoice($choice = new VoteChoice($pool));
                    $choice->setMention(MajorityVoteMentionEnum::ALL[$mentionIndex]);
                    $choice->setCandidateGroup($pool->getCandidateGroups()[$i]);
                }
            }

            $this->manager->persist($result);
        }

        $this->manager->flush();
    }
}
