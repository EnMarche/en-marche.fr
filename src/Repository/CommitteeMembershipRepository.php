<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Collection\AdherentCollection;
use App\Collection\CommitteeMembershipCollection;
use App\Committee\Filter\ListFilterObject;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\BaseGroup;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Subscription\SubscriptionTypeEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class CommitteeMembershipRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeMembership::class);
    }

    public function findActivityMemberships(Adherent $adherent, int $page = 1, int $limit = 5): PaginatorInterface
    {
        $queryBuilder = $this
            ->createQueryBuilder('cm')
            ->innerJoin('cm.committee', 'c')
            ->where('cm.adherent = :adherent')
            ->andWhere('c.status = :approved')
            ->andWhere('c.approvedAt IS NOT NULL')
            ->setParameter('adherent', $adherent)
            ->setParameter('approved', BaseGroup::APPROVED)
        ;

        return $this->configurePaginator(
            $queryBuilder,
            $page,
            $limit
        );
    }

    public function findMemberships(Adherent $adherent): CommitteeMembershipCollection
    {
        $query = $this
            ->createQueryBuilder('cm')
            ->where('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    public function findMembershipsForActiveCommittees(Adherent $adherent): CommitteeMembershipCollection
    {
        $query = $this
            ->createQueryBuilder('cm')
            ->addSelect('committee')
            ->addSelect('CASE WHEN (am.quality = :supervisor AND am.provisional = :false) THEN 1 '
                .'WHEN (am.quality = :supervisor AND am.provisional = :true) THEN 2 '
                .'WHEN (cm.privilege = :host) THEN 3 '
                .'WHEN (am.committee IS NOT NULL) THEN 4 '
                .'ELSE 5 END '
                .'AS HIDDEN score')
            ->innerJoin('cm.committee', 'committee')
            ->leftJoin(
                CommitteeAdherentMandate::class,
                'am',
                Join::WITH,
                'am.adherent = cm.adherent AND am.committee = cm.committee AND am.finishAt IS NULL'
            )
            ->where('cm.adherent = :adherent')
            ->andWhere('committee.status = :status')
            ->orderBy('score', 'ASC')
            ->setParameters([
                'adherent' => $adherent,
                'status' => Committee::APPROVED,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
                'host' => CommitteeMembership::COMMITTEE_HOST,
                'true' => true,
                'false' => false,
            ])
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    public function findMembership(Adherent $adherent, Committee $committee): ?CommitteeMembership
    {
        $query = $this
            ->createMembershipQueryBuilder($adherent, $committee)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * @return CommitteeMembership[]
     */
    public function findVotingMemberships(Committee $committee): array
    {
        return $this
            ->createQueryBuilder('cm')
            ->addSelect('adherent')
            ->innerJoin('cm.adherent', 'adherent')
            ->where('cm.committee = :committee')
            ->andWhere('cm.enableVote = :true')
            ->setParameters([
                'committee' => $committee,
                'true' => true,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Creates the query builder to fetch the membership relationship between
     * an adherent and a committee.
     */
    private function createMembershipQueryBuilder(Adherent $adherent, Committee $committee): QueryBuilder
    {
        return $this
            ->createQueryBuilder('cm')
            ->where('cm.adherent = :adherent')
            ->andWhere('cm.committee = :committee')
            ->setParameter('adherent', $adherent)
            ->setParameter('committee', $committee)
        ;
    }

    public function countMembers(Committee $committee, array $privileges): int
    {
        return (int) $this->createQueryBuilder('cm')
            ->select('COUNT(cm.uuid)')
            ->where('cm.committee = :committee')
            ->andWhere('cm.privilege IN (:privileges)')
            ->setParameter('committee', $committee)
            ->setParameter('privileges', $privileges)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns the list of all hosts memberships of a committee.
     */
    public function findHostMemberships(Committee $committee): CommitteeMembershipCollection
    {
        return $this->findPrivilegedMemberships($committee, [CommitteeMembership::COMMITTEE_HOST]);
    }

    public function findForHostEmail(Committee $committee): AdherentCollection
    {
        return $this->createAdherentCollection(
            $this->createQueryBuilder('cm')
                ->select('cm', 'adherent')
                ->leftJoin('cm.adherent', 'adherent')
                ->leftJoin('adherent.adherentMandates', 'am')
                ->leftJoin('adherent.subscriptionTypes', 'st')
                ->where('cm.committee = :committee')
                ->andWhere((new Orx())
                    ->add('cm.privilege = :host')
                    ->add('am.committee = :committee AND am.quality = :supervisor AND am.finishAt IS NULL')
                    ->add('st.code = :subscription_code')
                )
                ->orderBy('am.quality', 'DESC')
                ->addOrderBy('cm.privilege', 'DESC')
                ->addOrderBy('cm.joinedAt', 'ASC')
                ->setParameter('committee', $committee)
                ->setParameter('subscription_code', SubscriptionTypeEnum::LOCAL_HOST_EMAIL)
                ->setParameter('host', CommitteeMembership::COMMITTEE_HOST)
                ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
                ->getQuery()
        );
    }

    /**
     * Returns the list of all privileged memberships of a committee.
     *
     * @param Committee $committee  The committee
     * @param array     $privileges An array of privilege constants (see {@link : CommitteeMembership}
     */
    private function findPrivilegedMemberships(Committee $committee, array $privileges): CommitteeMembershipCollection
    {
        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->where('cm.committee = :committee')
            ->andWhere($qb->expr()->in('cm.privilege', $privileges))
            ->orderBy('cm.joinedAt', 'ASC')
            ->setParameter('committee', $committee)
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    /**
     * Returns the list of all members of a committee.
     *
     * @return Adherent[]|AdherentCollection
     */
    public function findMembers(Committee $committee): AdherentCollection
    {
        return $this->createAdherentCollection($this->createCommitteeMembershipsQueryBuilder($committee)->getQuery());
    }

    /**
     * Returns the list of all members of $sourceCommittee
     * that does not already belongs to list of all members of $destinationCommittee
     *
     * NOTE: this returns poorly hydrated instances of Adherent, but is optimized for merging of committees
     *
     * @return CommitteeMembership[]
     */
    public function findMembersToMerge(Committee $sourceCommittee, Committee $destinationCommittee): array
    {
        return $this
            ->createQueryBuilder('cm_src')
            ->select('PARTIAL adherent.{id, uuid, emailAddress, firstName, lastName}')
            ->addSelect('PARTIAL cm_src.{id, joinedAt}')
            ->innerJoin('cm_src.adherent', 'adherent')
            ->leftJoin(CommitteeMembership::class, 'cm_dest', Join::WITH, 'cm_dest.adherent = adherent AND cm_dest.committee = :dest_committee')
            ->where('cm_dest.id IS NULL AND cm_src.committee = :src_committee')
            ->setParameters([
                'src_committee' => $sourceCommittee,
                'dest_committee' => $destinationCommittee,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the list of all committee memberships of a committee.
     */
    public function findCommitteeMemberships(Committee $committee): CommitteeMembershipCollection
    {
        return new CommitteeMembershipCollection(
            $this
                ->createCommitteeMembershipsQueryBuilder($committee)
                ->addSelect('a')
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * @return CommitteeMembership[]|PaginatorInterface|iterable
     */
    public function getCommitteeMembershipsPaginator(
        Committee $committee,
        ListFilterObject $filter = null,
        int $page = 1,
        ?int $limit = 30
    ): iterable {
        $qb = $this
            ->createCommitteeMembershipsQueryBuilder($committee)
            ->groupBy('cm.id')
        ;

        if ($filter) {
            if ($filter->getAgeMin() || $filter->getAgeMax()) {
                $now = new \DateTimeImmutable();

                if ($filter->getAgeMin()) {
                    $qb
                        ->andWhere('a.birthdate <= :min_birth_date')
                        ->setParameter('min_birth_date', $now->sub(new \DateInterval(sprintf('P%dY', $filter->getAgeMin()))))
                    ;
                }

                if ($filter->getAgeMax()) {
                    $qb
                        ->andWhere('a.birthdate >= :min_birth_date')
                        ->setParameter('min_birth_date', $now->sub(new \DateInterval(sprintf('P%dY', $filter->getAgeMax()))))
                    ;
                }
            }

            if ($gender = $filter->getGender()) {
                $qb
                    ->andWhere('a.gender = :gender')
                    ->setParameter('gender', $gender)
                ;
            }

            if (null !== $filter->isCertified()) {
                $qb
                    ->andWhere(sprintf('a.certifiedAt IS %s NULL', $filter->isCertified() ? 'NOT' : ''))
                ;
            }

            if (null !== $filter->isSubscribed()) {
                $subscriptionCondition = 'STRING_AGG(st.code, \',\') LIKE :subscription_code';
                if (false === $filter->isSubscribed()) {
                    $subscriptionCondition = 'STRING_AGG(st.code, \',\') IS NULL OR STRING_AGG(st.code, \',\') NOT LIKE :subscription_code';
                }

                $qb
                    ->leftJoin('a.subscriptionTypes', 'st')
                    ->having($subscriptionCondition)
                    ->setParameter('subscription_code', '%'.SubscriptionTypeEnum::LOCAL_HOST_EMAIL.'%')
                ;
            }

            if ($filter->getFirstName()) {
                $qb
                    ->andWhere('a.firstName = :first_name')
                    ->setParameter('first_name', $filter->getFirstName())
                ;
            }

            if ($filter->getLastName()) {
                $qb
                    ->andWhere('a.lastName = :last_name')
                    ->setParameter('last_name', $filter->getLastName())
                ;
            }

            if ($filter->getRegisteredSince()) {
                $qb
                    ->andWhere('a.registeredAt >= :registered_since')
                    ->setParameter('registered_since', $filter->getRegisteredSince())
                ;
            }

            if ($filter->getRegisteredUntil()) {
                $qb
                    ->andWhere('a.registeredAt <= :registered_until')
                    ->setParameter('registered_until', $filter->getRegisteredUntil())
                ;
            }

            if ($filter->getJoinedSince()) {
                $qb
                    ->andWhere('cm.joinedAt >= :joined_since')
                    ->setParameter('joined_since', $filter->getJoinedSince())
                ;
            }

            if ($filter->getJoinedUntil()) {
                $qb
                    ->andWhere('cm.joinedAt <= :joined_until')
                    ->setParameter('joined_until', $filter->getJoinedUntil())
                ;
            }

            if ($filter->getCity()) {
                $qb
                    ->andWhere('(a.postAddress.cityName = :city OR a.postAddress.postalCode = :city)')
                    ->setParameter('city', $filter->getCity())
                ;
            }

            if ($filter->getVotersOnly()) {
                $qb
                    ->andWhere('cm.enableVote = :enable_vote')
                    ->setParameter('enable_vote', true)
                ;
            }

            if ($filter->getSort()) {
                $qb->orderBy('cm.'.$filter->getSort(), $filter->getOrder() ?? 'ASC');
            }
        }

        if (!$limit) {
            return $qb->getQuery()->getResult();
        }

        return $this->configurePaginator($qb, $page, $limit);
    }

    /**
     * Creates a QueryBuilder instance to fetch memberships of a committee.
     *
     * @param Committee $committee The committee for which the memberships to fetch belong
     * @param string    $alias     The custom root alias for the query
     */
    private function createCommitteeMembershipsQueryBuilder(Committee $committee, string $alias = 'cm'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->innerJoin($alias.'.adherent', 'a')
            ->addSelect(sprintf('(%s) AS HIDDEN score', $this->getEntityManager()->createQueryBuilder()
                ->from(CommitteeMembership::class, 'cm2')
                ->select(
                    'CASE
                        WHEN am2.quality = :supervisor AND am2.provisional = false THEN 1
                        WHEN am2.quality = :supervisor AND am2.provisional = true THEN 2
                        WHEN cm2.privilege = :host THEN 3
                        WHEN am2.committee IS NOT NULL THEN 4
                        ELSE 5
                    END AS priority')
                ->where('cm2.id = cm.id')
                ->innerJoin('cm2.adherent', 'a2')
                ->leftJoin('a2.adherentMandates', 'am2', Join::WITH, 'am2.committee = cm2.committee AND am2.finishAt IS NULL')
                ->getDQL()
            ))
            ->where($alias.'.committee = :committee')
            ->orderBy('score', 'ASC')
            ->setParameters([
                'committee' => $committee,
                'host' => CommitteeMembership::COMMITTEE_HOST,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
            ])
        ;
    }

    /**
     * Creates an AdherentCollection instance with the results of a Query.
     *
     * The query must return a list of CommitteeMembership entities.
     *
     * @param Query $query The query to execute
     */
    private function createAdherentCollection(Query $query): AdherentCollection
    {
        return new AdherentCollection(
            array_map(
                function (CommitteeMembership $membership) {
                    return $membership->getAdherent();
                },
                $query->getResult()
            )
        );
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostFirstName(string $firstName): array
    {
        return $this->findCommitteesUuidByHost([
            'firstName' => $firstName,
        ]);
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostLastName(string $lastName): array
    {
        return $this->findCommitteesUuidByHost([
            'lastName' => $lastName,
        ]);
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostEmailAddress(string $emailAddress): array
    {
        return $this->findCommitteesUuidByHost([
            'emailAddress' => $emailAddress,
        ]);
    }

    public function findCommitteesUuidByHost(array $criteria): array
    {
        $qb = $this
            ->createQueryBuilder('cm')
            ->select('c.uuid')
            ->innerJoin('cm.committee', 'c')
            ->innerJoin('cm.adherent', 'a')
            ->leftJoin('c.adherentMandates', 'am')
            ->andWhere((new Orx())
                ->add('cm.privilege = :host')
                ->add('am.quality = :supervisor AND am.finishAt IS NULL')
            )
            ->setParameters([
                'host' => CommitteeMembership::COMMITTEE_HOST,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
            ])
        ;

        if (isset($criteria['firstName'])) {
            $qb
                ->andWhere('ILIKE(a.firstName, :firstName) = true')
                ->setParameter('firstName', '%'.$criteria['firstName'].'%')
            ;
        }

        if (isset($criteria['lastName'])) {
            $qb
                ->andWhere('ILIKE(a.lastName, :lastName) = true')
                ->setParameter('lastName', '%'.$criteria['lastName'].'%')
            ;
        }

        if (isset($criteria['emailAddress'])) {
            $qb
                ->andWhere('ILIKE(a.emailAddress, :emailAddress) = true')
                ->setParameter('emailAddress', '%'.$criteria['emailAddress'].'%')
            ;
        }

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($qb->getQuery()->getArrayResult(), 'uuid'));
    }

    public function isAdherentInCommittee(Adherent $adherent, Committee $committee): bool
    {
        return 0 !== $this->count(['adherent' => $adherent, 'committee' => $committee]);
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getCandidacyMemberships(CommitteeElection $election): array
    {
        return $this->createQueryBuilder('m')
            ->addSelect('a', 'c')
            ->innerJoin('m.committeeCandidacies', 'c')
            ->innerJoin('m.adherent', 'a')
            ->where('c.committeeElection = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getResult()
        ;
    }

    public function updateVoteStatusForAdherents(Committee $committee, array $adherents, bool $value): void
    {
        $this->createQueryBuilder('cm')
            ->update()
            ->set('cm.enableVote', ':value')
            ->Where('cm IN (:memberships)')
            ->setParameters([
                'memberships' => $this->findBy([
                    'adherent' => $adherents,
                    'committee' => $committee,
                ]),
                'value' => true === $value ?: null,
            ])
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return CommitteeMembership[]
     */
    public function findVotingForElectionMemberships(
        Committee $committee,
        Designation $designation,
        bool $withCertified = true
    ): array {
        return $this->createQueryBuilderForVotingMemberships($committee, $designation, $withCertified)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CommitteeMembership[]
     */
    public function findVotingForSupervisorMembershipsToNotify(Committee $committee, Designation $designation): array
    {
        return $this->createQueryBuilderForVotingMemberships($committee, $designation, false)
            ->innerJoin('adherent.subscriptionTypes', 'subscription_type', Join::WITH, 'subscription_type.code = :subscription_code')
            ->andWhere('adherent.notifiedForElection = :false')
            ->setParameter('subscription_code', SubscriptionTypeEnum::LOCAL_HOST_EMAIL)
            ->setParameter('false', false)
            ->getQuery()
            ->getResult()
        ;
    }

    public function committeeHasVotersForElection(Committee $committee, Designation $designation): bool
    {
        return 0 < (int) $this->createQueryBuilderForVotingMemberships($committee, $designation, false)
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CommitteeMembership[]
     */
    public function findAvailableMemberships(CommitteeCandidacy $candidacy, string $query): array
    {
        $membership = $candidacy->getCommitteeMembership();
        $refDate = $candidacy->getElection()->getVoteEndDate() ?? new \DateTime();

        return $this
            ->createQueryBuilder('membership')
            ->addSelect('adherent')
            ->innerJoin('membership.adherent', 'adherent')
            ->leftJoin('membership.committeeCandidacies', 'candidacy', Join::WITH, 'candidacy.committeeMembership = membership AND candidacy.committeeElection = :election')
            ->where('membership.committee = :committee')
            ->andWhere('candidacy IS NULL OR candidacy.status = :candidacy_draft_status')
            ->andWhere('membership.id != :membership_id')
            ->andWhere('adherent.gender = :gender AND adherent.status = :adherent_status')
            ->andWhere('(ILIKE(adherent.firstName, :query) = true OR ILIKE(adherent.lastName, :query) = true)')
            ->andWhere('adherent.certifiedAt IS NOT NULL AND adherent.registeredAt <= :registration_limit_date AND membership.joinedAt <= :limit_date')
            ->setParameters([
                'query' => sprintf('%s%%', $query),
                'candidacy_draft_status' => CandidacyInterface::STATUS_DRAFT,
                'election' => $candidacy->getElection(),
                'committee' => $membership->getCommittee(),
                'membership_id' => $membership->getId(),
                'gender' => $candidacy->isFemale() ? Genders::MALE : Genders::FEMALE,
                'adherent_status' => Adherent::ENABLED,
                'registration_limit_date' => (clone $refDate)->modify('-3 months'),
                'limit_date' => (clone $refDate)->modify('-30 days'),
            ])
            ->orderBy('adherent.lastName')
            ->addOrderBy('adherent.firstName')
            ->getQuery()
            ->getResult()
        ;
    }

    private function createQueryBuilderForVotingMemberships(
        Committee $committee,
        Designation $designation,
        bool $onlyCertified = true
    ): QueryBuilder {
        $refDate = \DateTimeImmutable::createFromMutable($designation->getVoteEndDate());

        $qb = $this->createQueryBuilder('membership')
            ->innerJoin('membership.adherent', 'adherent')
            ->where('membership.committee = :committee')
            ->andWhere('membership.joinedAt <= :joined_at_min')
            ->andWhere('adherent.registeredAt <= :registered_at_min'.($onlyCertified ? ' AND adherent.certifiedAt IS NOT NULL' : ''))
            ->setParameters([
                'committee' => $committee,
                'joined_at_min' => $refDate->modify('-30 days'),
                'registered_at_min' => $refDate->modify('-3 months'),
            ])
        ;

        if (!$designation->isLimited()) {
            $forbiddenAdherents = array_column($this->getEntityManager()->createQueryBuilder()
                ->from(CommitteeCandidacy::class, 'candidacy')
                ->select('adherent.id as adherent_id, candidacy.id as candidacy_id')
                ->innerJoin('candidacy.committeeElection', 'election')
                ->innerJoin('candidacy.committeeMembership', 'membership')
                ->innerJoin('membership.adherent', 'adherent')
                ->innerJoin('adherent.memberships', 'other_membership', Join::WITH, 'other_membership.committee = :committee')
                ->where('election.designation = :designation')
                ->andWhere('candidacy.status = :status')
                ->andWhere('election.committee != :committee')
                ->setParameters([
                    'committee' => $committee,
                    'status' => CandidacyInterface::STATUS_CONFIRMED,
                    'designation' => $designation,
                ])
                ->getQuery()
                ->getArrayResult(), 'adherent_id'
            );

            if ($forbiddenAdherents) {
                $qb
                    ->andWhere('adherent.id NOT IN (:candidate_adherents)')
                    ->setParameter('candidate_adherents', $forbiddenAdherents)
                ;
            }
        }

        return $qb;
    }
}
