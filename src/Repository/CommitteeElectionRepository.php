<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Committee\Filter\CommitteeDesignationsListFilter;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Vote;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CommitteeElectionRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeElection::class);
    }

    /**
     * @return CommitteeElection[]
     */
    public function findAllByDesignation(Designation $designation, int $offset = 0, ?int $limit = 200): array
    {
        return $this->createQueryBuilder('ce')
            ->addSelect('c')
            ->innerJoin('ce.committee', 'c')
            ->where('ce.designation = :designation')
            ->andWhere('c.status = :approved')
            ->setParameters([
                'designation' => $designation,
                'approved' => Committee::APPROVED,
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CommitteeElection[]
     */
    public function findAllToNotify(Designation $designation, int $offset = 0, ?int $limit = 200): array
    {
        return $this->createQueryBuilder('ce')
            ->addSelect('c')
            ->innerJoin('ce.committee', 'c')
            ->innerJoin('ce.candidacies', 'candidacy')
            ->where('ce.designation = :designation')
            ->andWhere('ce.adherentNotified = :false')
            ->setParameters([
                'designation' => $designation,
                'false' => false,
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findElections(
        CommitteeDesignationsListFilter $filter,
        int $page = 1,
        int $limit = 50
    ): PaginatorInterface {
        $qb = $this
            ->createQueryBuilder('committee_election')
            ->addSelect('committee', 'designation')
            ->addSelect(sprintf('(%s) AS total_confirmed_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(CASE WHEN sub_committee_candidacy_1.id IS NOT NULL AND sub_committee_candidacy_1.gender = :male THEN 1 ELSE 0 END)')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_1')
                    ->where('sub_committee_candidacy_1.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_1.status = :confirmed')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_confirmed_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(CASE WHEN sub_committee_candidacy_2.id IS NOT NULL AND sub_committee_candidacy_2.gender = :female THEN 1 ELSE 0 END)')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_2')
                    ->where('sub_committee_candidacy_2.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_2.status = :confirmed')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_draft_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(CASE WHEN sub_committee_candidacy_3.id IS NOT NULL AND sub_committee_candidacy_3.gender = :male THEN 1 ELSE 0 END)')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_3')
                    ->where('sub_committee_candidacy_3.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_3.status = :draft')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_draft_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(CASE WHEN sub_committee_candidacy_4.id IS NOT NULL AND sub_committee_candidacy_4.gender = :female THEN 1 ELSE 0 END)')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_4')
                    ->where('sub_committee_candidacy_4.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_4.status = :draft')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS winners',
                $this->getEntityManager()->createQueryBuilder()
                    ->select("STRING_AGG(CONCAT(sub_voting_platform_candidate.gender, '|', sub_voting_platform_candidate.firstName, '|', sub_voting_platform_candidate.lastName), ',')")
                    ->from(Candidate::class, 'sub_voting_platform_candidate')
                    ->innerJoin('sub_voting_platform_candidate.candidateGroup', 'sub_candidate_group', Join::WITH, 'sub_candidate_group.elected = :true')
                    ->innerJoin('sub_candidate_group.electionPool', 'sub_election_pool')
                    ->innerJoin('sub_election_pool.election', 'sub_voting_platform_election')
                    ->innerJoin('sub_voting_platform_election.electionEntity', 'sub_voting_platform_election_entity')
                    ->andWhere('sub_voting_platform_election_entity.committee = committee')
                    ->andWhere('sub_voting_platform_election.designation = designation')
                    ->getDQL())
            )
            ->addSelect(sprintf('(%s) AS voters',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('COUNT(1)')
                    ->from(Vote::class, 'sub_voting_platform_vote')
                    ->innerJoin('sub_voting_platform_vote.electionRound', 'sub_voting_platform_election_round', Join::WITH, 'sub_voting_platform_election_round.isActive = :true')
                    ->innerJoin('sub_voting_platform_election_round.election', 'sub_voting_platform_election2')
                    ->innerJoin('sub_voting_platform_election2.electionEntity', 'sub_voting_platform_election_entity2')
                    ->andWhere('sub_voting_platform_election_entity2.committee = committee')
                    ->andWhere('sub_voting_platform_election2.designation = designation')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS voting_platform_election_uuid',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('sub_voting_platform_election3.uuid')
                    ->from(Election::class, 'sub_voting_platform_election3')
                    ->innerJoin('sub_voting_platform_election3.electionEntity', 'sub_voting_platform_election_entity3')
                    ->andWhere('sub_voting_platform_election_entity3.committee = committee')
                    ->andWhere('sub_voting_platform_election3.designation = designation')
                    ->getDQL()
            ))
            ->innerJoin('committee_election.committee', 'committee')
            ->innerJoin('committee_election.designation', 'designation')
            ->where('committee.status = :approved')
            ->setParameters([
                'approved' => Committee::APPROVED,
                'male' => Genders::MALE,
                'female' => Genders::FEMALE,
                'confirmed' => CandidacyInterface::STATUS_CONFIRMED,
                'draft' => CandidacyInterface::STATUS_DRAFT,
                'true' => true,
            ])
            ->orderBy('designation.voteStartDate', 'DESC')
        ;

        if ($filter->getCommitteeName()) {
            $qb
                ->andWhere('ILIKE(committee.name, :committee_name) = true')
                ->setParameter('committee_name', '%'.$filter->getCommitteeName().'%')
            ;
        }

        if ($committee = $filter->getCommittee()) {
            $qb
                ->andWhere('committee.id = :committee_id')
                ->setParameter('committee_id', $committee->getId())
            ;
        }

        if ($filter->getZones()) {
            $this->withGeoZones(
                $filter->getZones(),
                $qb,
                'committee',
                Committee::class,
                'c2',
                'zones',
                'z2',
                function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                    $zoneQueryBuilder->andWhere(sprintf('%s.status = :approved', $entityClassAlias));
                }
            );
        }

        return $this->configurePaginator($qb, $page, $limit);
    }
}
