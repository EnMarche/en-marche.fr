<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Address\Address;
use App\Committee\Filter\CommitteeDesignationsListFilter;
use App\Committee\Filter\CommitteeListFilter;
use App\Coordinator\Filter\CommitteeFilter;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\BaseGroup;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\District;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Geo\Zone;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use App\Geocoder\Coordinates;
use App\Intl\FranceCitiesBundle;
use App\Search\SearchParametersFilter;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationZoneEnum;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class CommitteeRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use GeoFilterTrait;
    use NearbyTrait;
    use ReferentTrait;
    use GeoZoneTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public const DEFAULT_MAX_RESULTS_LIST = 3;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Committee::class);
    }

    public function countElements(): int
    {
        return (int) $this
            ->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.status = :approved')
            ->setParameter('approved', Committee::APPROVED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Finds a Committee instance by its unique canonical name.
     */
    public function findOneByName(string $name): ?Committee
    {
        $canonicalName = Committee::canonicalize($name);

        return $this->findOneBy(['canonicalName' => $canonicalName]);
    }

    /**
     * @return Committee[]
     */
    public function findApprovedByAddress(Address $address, int $limit = null): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.postAddress.address = :address AND c.postAddress.postalCode = :postal_code')
            ->andWhere('ILIKE(c.postAddress.cityName, :city_name) = true AND c.postAddress.country = :country')
            ->andWhere('c.status = :approved')
            ->setParameters([
                'address' => $address->getAddress(),
                'postal_code' => $address->getPostalCode(),
                'city_name' => $address->getCityName().'%',
                'country' => $address->getCountry(),
                'approved' => BaseGroup::APPROVED,
            ])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByUuid(string $uuid): ?Committee
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * Finds approved Committee instances.
     *
     * @return Committee[]
     */
    public function findApprovedCommittees()
    {
        return $this->findBy(['status' => Committee::APPROVED]);
    }

    /**
     * Returns the most recent created Committee.
     */
    public function findMostRecentCommittee(): ?Committee
    {
        $query = $this
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findLastApprovedCommittees(int $count = self::DEFAULT_MAX_RESULTS_LIST): array
    {
        return $this
            ->createQueryBuilder('committee')
            ->where('committee.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('committee.approvedAt', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Committee[]
     */
    public function findNearbyCommittees(Coordinates $coordinates, int $count = self::DEFAULT_MAX_RESULTS_LIST)
    {
        $qb = $this
            ->createNearbyQueryBuilder($coordinates)
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setMaxResults($count)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findNearbyCommitteesFilteredByCountry(
        Coordinates $coordinates,
        string $country,
        string $postalCodePrefix = null,
        int $count = self::DEFAULT_MAX_RESULTS_LIST
    ): array {
        $qb = $this
            ->createNearbyQueryBuilder($coordinates)
            ->andWhere('n.status = :status')
            ->andWhere('n.postAddress.country = :country')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('country', $country)
        ;

        if ($postalCodePrefix) {
            $qb
                ->andWhere('n.postAddress.postalCode LIKE :postalCode')
                ->setParameter('postalCode', $postalCodePrefix.'%')
            ;
        }

        return $qb
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the total number of approved committees.
     */
    public function countApprovedCommittees(): int
    {
        $query = $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function getQueryBuilderForTags(array $referentTags): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $this->applyGeoFilter($qb, $referentTags, 'c');

        return $qb;
    }

    /**
     * @return Committee[]|PaginatorInterface
     */
    public function searchByFilter(CommitteeListFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return $this->configurePaginator($this->createFilterQueryBuilder($filter), $page, $limit);
    }

    /**
     * @return Committee[]|PaginatorInterface
     */
    public function searchRequestsByFilter(
        CommitteeListFilter $filter,
        int $page = 1,
        int $limit = 100
    ): PaginatorInterface {
        $queryBuilder = $this->createRequestsFilterQueryBuilder($filter->getZones() ?: $filter->getManagedZones())
            ->orderBy('c.createdAt', 'DESC')
            ->groupBy('c.id')
        ;

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    /**
     * @param Zone[] $zones
     */
    public function countRequestsForZones(array $zones, string $status = null): int
    {
        $qb = $this
            ->createRequestsFilterQueryBuilder($zones)
            ->select('COUNT(DISTINCT c.id)')
        ;

        if ($status) {
            $qb
                ->andWhere('c.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param Zone[] $zones
     */
    public function countForZones(array $zones): int
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->where('c.status = :status')
            ->setParameters([
                'status' => Committee::APPROVED,
            ])
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findManagedBy(Adherent $referent): array
    {
        if (!$referent->isReferent()) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $this->applyGeoFilter($qb, $referent->getManagedArea()->getTags()->toArray(), 'c');

        return $qb->getQuery()->getResult();
    }

    public function findManagedByCoordinator(Adherent $coordinator, CommitteeFilter $filter): array
    {
        if (!$coordinator->isCoordinatorCommitteeSector()) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $filter->setCoordinator($coordinator);
        $filter->apply($qb, 'c');

        return $qb->getQuery()->getResult();
    }

    public function countSitemapCommittees(): int
    {
        return (int) $this->createSitemapQb()
            ->select('COUNT(c) AS nb')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findSitemapCommittees(int $page, int $perPage): array
    {
        return $this->createSitemapQb()
            ->select('c.uuid', 'c.slug')
            ->orderBy('c.id')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    private function createSitemapQb(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
        ;
    }

    /**
     * @return Committee[]
     */
    public function searchCommittees(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this
                ->createNearbyQueryBuilder($coordinates)
                ->andWhere($this->getNearbyExpression().' < :distance_max')
                ->setParameter('distance_max', $search->getRadiusInMeters())
            ;
        } else {
            $qb = $this->createQueryBuilder('n');
        }

        if (!empty($query = $search->getQuery())) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        return $qb
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasCommitteeInStatus(Adherent $adherent, array $status): bool
    {
        $nb = $this->createQueryBuilder('c')
            ->select('COUNT(c) AS nb')
            ->where('c.createdBy = :creator')
            ->andWhere('c.status IN (:status)')
            ->setParameter('creator', $adherent->getUuid()->toString())
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $nb > 0;
    }

    public function findCommitteesUuidByCreatorUuids(array $creatorsUuid): array
    {
        $qb = $this->createQueryBuilder('c');

        $query = $qb
            ->select('c.uuid')
            ->where('c.createdBy IN (:creatorsUuid)')
            ->setParameter('creatorsUuid', $creatorsUuid)
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    public function findByPartialName(string $search, int $limit = 10): array
    {
        return $this
            ->createPartialNameQueryBuilder($search, 'committee')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPartialNameForReferent(Adherent $referent, string $search, int $limit = 10): array
    {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->applyGeoFilter($qb, $referent->getManagedArea()->getTags()->toArray(), $alias);

        return $qb->getQuery()->getResult();
    }

    public function findByPartialNameForDeputy(Adherent $deputy, string $search, int $limit = 10): array
    {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->applyGeoFilter($qb, [$deputy->getManagedDistrict()->getReferentTag()], $alias);

        return $qb->getQuery()->getResult();
    }

    public function findByPartialNameForSenator(Adherent $senator, string $search, int $limit = 10): array
    {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->applyGeoFilter($qb, [$senator->getSenatorArea()->getDepartmentTag()], $alias);

        return $qb->getQuery()->getResult();
    }

    public function findByPartialNameForSenatorialCandidate(
        Adherent $senatorialCandidate,
        string $search,
        int $limit = 10
    ): array {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->applyGeoFilter($qb, $senatorialCandidate->getSenatorialCandidateManagedArea()->getDepartmentTags()->toArray(), $alias);

        return $qb->getQuery()->getResult();
    }

    public function findByPartialNameForCandidate(array $referentTags, string $search, int $limit = 10): array
    {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->applyGeoFilter($qb, $referentTags, $alias);

        return $qb->getQuery()->getResult();
    }

    private function createPartialNameQueryBuilder(string $search, string $alias = 'c'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->where("ILIKE($alias.canonicalName, :search) = true")
            ->andWhere("$alias.status = :status")
            ->setParameter('search', '%'.mb_strtolower($search).'%')
            ->setParameter('status', Committee::APPROVED)
        ;
    }

    public function paginateAllApprovedCommittees(
        int $offset = 0,
        int $limit = SearchParametersFilter::DEFAULT_MAX_RESULTS
    ): Paginator {
        $query = $this->createQueryBuilder('e')
            ->andWhere('e.status = :approved')
            ->setParameter('approved', Committee::APPROVED)
            ->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return new Paginator($query);
    }

    public function countApprovedForReferent(Adherent $referent): int
    {
        return (int) $this->createQueryBuilder('committee')
            ->select('COUNT(committee) AS count')
            ->join('committee.referentTags', 'tag')
            ->where('committee.status = :status')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findApprovedForReferentAutocomplete(Adherent $referent, $value): array
    {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('committee')
            ->select('committee.uuid, committee.name')
            ->join('committee.referentTags', 'tag')
            ->where('committee.status = :status')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->orderBy('committee.name')
        ;

        if ($value) {
            $qb
                ->andWhere('ILIKE(committee.name, :searchedName) = true')
                ->setParameter('searchedName', $value.'%')
                ->setMaxResults(70)
            ;
        }

        return array_map(function (array $committee) {
            return [$committee['uuid'] => $committee['name']];
        }, $qb->getQuery()->getScalarResult());
    }

    public function findCitiesForReferentAutocomplete(Adherent $referent, $value): array
    {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('committee')
            ->select('DISTINCT committee.postAddress.cityName as city')
            ->join('committee.referentTags', 'tag')
            ->where('committee.status = :status')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->orderBy('city')
        ;

        if ($value) {
            $qb
                ->andWhere('ILIKE(committee.postAddress.cityName, :searchedCityName) = true')
                ->setParameter('searchedCityName', $value.'%')
            ;
        }

        return array_column($qb->getQuery()->getArrayResult(), 'city');
    }

    public function retrieveMostActiveCommitteesInReferentManagedArea(Adherent $referent, int $limit = 5): array
    {
        return $this->retrieveTopCommitteesInReferentManagedArea($referent, $limit);
    }

    public function retrieveLeastActiveCommitteesInReferentManagedArea(Adherent $referent, int $limit = 5): array
    {
        return $this->retrieveTopCommitteesInReferentManagedArea($referent, $limit, false);
    }

    /**
     * Finds committees in the district.
     *
     * @return Committee[]
     */
    public function findAllInDistrict(District $district): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin(District::class, 'd', Join::WITH, 'd.id = :district_id')
            ->innerJoin('d.geoData', 'gd')
            ->where('ST_Within(ST_Point(c.postAddress.longitude, c.postAddress.latitude), gd.geoShape) = true')
            ->andWhere('c.status = :status')
            ->setParameter('district_id', $district->getId())
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCommitteesForHost(Adherent $adherent): array
    {
        // Prevent SQL query if the adherent doesn't follow any committees yet.
        if (0 === \count($adherent->getMemberships())) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->innerJoin(CommitteeMembership::class, 'cm', Join::WITH, 'c = cm.committee')
            ->leftJoin('c.adherentMandates', 'am')
            ->where((new Orx())
                ->add('cm.privilege = :privilege AND cm.adherent = :adherent')
                ->add('am.adherent = :adherent AND am.committee IS NOT NULL AND am.quality = :supervisor AND am.finishAt IS NULL')
            )
            ->setParameter('adherent', $adherent)
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
            ->getQuery()
            ->getResult()
        ;
    }

    private function retrieveTopCommitteesInReferentManagedArea(
        Adherent $referent,
        int $limit = 5,
        bool $mostActive = true
    ): array {
        $this->checkReferent($referent);

        return $this->createQueryBuilder('committee')
            ->select('committee.name, COUNT(event) AS events, SUM(event.participantsCount) as HIDDEN participants')
            ->join(CommitteeEvent::class, 'event', Join::WITH, 'event.committee = committee.id')
            ->join('committee.referentTags', 'tag')
            ->where('tag.id IN (:tags)')
            ->andWhere('committee.status = :status')
            ->andWhere('event.beginAt >= :from')
            ->andWhere('event.beginAt < :until')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('from', (new Chronos('first day of this month'))->setTime(0, 0, 0))
            ->setParameter('until', (new Chronos('first day of next month'))->setTime(0, 0, 0))
            ->setMaxResults($limit)
            ->orderBy('events', $mostActive ? 'DESC' : 'ASC')
            ->addOrderBy('participants', $mostActive ? 'DESC' : 'ASC')
            ->addOrderBy('committee.id', 'ASC')
            ->groupBy('committee.id')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @return Committee[]
     */
    public function findAllWithoutStartedElection(Designation $designation, int $offset = 0, int $limit = 200): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.currentDesignation', 'd')
            ->where('(c.currentDesignation IS NULL OR (d.voteEndDate IS NOT NULL AND d.voteEndDate < :date))')
            ->andWhere('c.status = :status')
            ->setParameters([
                'status' => Committee::APPROVED,
                'date' => $designation->getCandidacyStartDate(),
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->groupBy('c')
        ;

        if (DesignationZoneEnum::toArray() !== array_intersect(DesignationZoneEnum::toArray(), $designation->getZones())) {
            $zoneCondition = new Orx();

            // Outre-Mer condition
            if (\in_array(DesignationZoneEnum::OUTRE_MER, $designation->getZones(), true) || \in_array(DesignationZoneEnum::FRANCE, $designation->getZones(), true)) {
                $zoneCondition->add(sprintf(
                    'c.postAddress.country = :fr AND SUBSTRING(c.postAddress.postalCode, 1, 3) %s (:outremer_codes)',
                    \in_array(DesignationZoneEnum::OUTRE_MER, $designation->getZones(), true) ? 'IN' : 'NOT IN'
                ));
                $qb->setParameter('outremer_codes', array_keys(FranceCitiesBundle::DOMTOM_INSEE_CODE));
            }

            // France vs FDE
            if ([DesignationZoneEnum::FRANCE, DesignationZoneEnum::FDE] !== array_intersect([DesignationZoneEnum::FRANCE, DesignationZoneEnum::FDE], $designation->getZones())) {
                $zoneCondition->add(sprintf(
                    'c.postAddress.country %s :fr',
                    \in_array(DesignationZoneEnum::FRANCE, $designation->getZones(), true) ? '=' : '!='
                ));
            }

            $qb
                ->andWhere($zoneCondition)
                ->setParameter('fr', Address::FRANCE)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findForAdherentWithCommitteeMandates(Adherent $adherent): array
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin('committee.adherentMandates', 'mandate')
            ->where('mandate.adherent = :adherent')
            ->andWhere('mandate.quality IS NULL AND mandate.finishAt IS NULL')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function createSelectByReferentTagsQueryBuilder(array $referentTags): QueryBuilder
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin('committee.referentTags', 'tag')
            ->andWhere('tag IN (:tags)')
            ->setParameter('tags', $referentTags)
            ->orderBy('committee.name')
        ;
    }

    private function createFilterQueryBuilder(CommitteeListFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('c AS committee')
            ->addSelect(sprintf('(%s) AS total_voters',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('COUNT(DISTINCT cm.id)')
                    ->from(CommitteeMembership::class, 'cm')
                    ->where('cm.committee = c AND cm.enableVote = :true')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(CASE WHEN candidacy1.id IS NOT NULL AND candidacy1.gender = :male THEN 1 ELSE 0 END)')
                    ->from(CommitteeElection::class, 'election1')
                    ->leftJoin('election1.candidacies', 'candidacy1')
                    ->innerJoin('election1.designation', 'designation1')
                    ->where('election1.committee = c AND designation1.candidacyStartDate <= :now')
                    ->andWhere('(designation1.voteEndDate IS NULL OR :now <= designation1.voteEndDate)')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(CASE WHEN candidacy2.id IS NOT NULL AND candidacy2.gender = :female THEN 1 ELSE 0 END)')
                    ->from(CommitteeElection::class, 'election2')
                    ->leftJoin('election2.candidacies', 'candidacy2')
                    ->innerJoin('election2.designation', 'designation2')
                    ->where('election2.committee = c AND designation2.candidacyStartDate <= :now')
                    ->andWhere('(designation2.voteEndDate IS NULL OR :now <= designation2.voteEndDate)')
                    ->getDQL()
            ))
            ->where('c.status = :status')
            ->setParameters([
                'status' => Committee::APPROVED,
                'male' => Genders::MALE,
                'female' => Genders::FEMALE,
                'now' => new \DateTime(),
                'true' => true,
            ])
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
            ->groupBy('c.id')
        ;

        $this->withGeoZones(
            $filter->getZones() ?: $filter->getManagedZones(),
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        return $qb;
    }

    private function createRequestsFilterQueryBuilder(array $zones): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->where('c.createdAt > :from')
            ->setParameter('from', new \DateTime('2021-01-01 00:00:00'))
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.createdAt > :from', $entityClassAlias));
            }
        );

        return $qb;
    }

    public function findAvailableForPartials(
        CommitteeDesignationsListFilter $filter,
        int $page = 1,
        int $limit = 100
    ): array {
        $qb = $this->createQueryBuilder('committee')
            ->addSelect(($having1 = 'SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality IS NULL THEN 1 ELSE 0 END)').' AS total_designed_adherents')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality IS NULL AND mandate.gender = :female THEN 1 ELSE 0 END) AS total_designed_adherents_female')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality IS NULL AND mandate.gender = :male THEN 1 ELSE 0 END) AS total_designed_adherents_male')
            ->addSelect(($having2 = 'SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality = :supervisor AND mandate.provisional = :false THEN 1 ELSE 0 END)').' AS total_supervisors')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality = :supervisor AND mandate.provisional = :false AND mandate.gender = :female THEN 1 ELSE 0 END) AS total_supervisors_female')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality = :supervisor AND mandate.provisional = :false AND mandate.gender = :male THEN 1 ELSE 0 END) AS total_supervisors_male')
            ->leftJoin('committee.adherentMandates', 'mandate', Join::WITH, 'mandate.finishAt IS NULL')
            ->leftJoin('committee.currentDesignation', 'designation')
            ->where('committee.status = :status')
            ->andWhere('committee.approvedAt <= :d30')
            ->andWhere('(designation IS NULL OR designation.voteEndDate < :now)')
            ->groupBy('committee.id')
            ->orderBy('committee.membersCount', 'DESC')
            ->setParameters([
                'female' => Genders::FEMALE,
                'male' => Genders::MALE,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
                'false' => false,
                'status' => Committee::APPROVED,
                'now' => new \DateTime(),
                'd30' => (new \DateTime())->modify('-30 days'),
            ])
            ->having("${having1} < 2 OR ${having2} < 2")
        ;

        if ($filter->getCommitteeName()) {
            $qb
                ->andWhere('ILIKE(committee.name, :committee_name) = true')
                ->setParameter('committee_name', '%'.$filter->getCommitteeName().'%')
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
                    $zoneQueryBuilder->andWhere(sprintf('%1$s.status = :status AND %1$s.approvedAt <= :d30', $entityClassAlias));
                }
            );
        }

        return $qb
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCommitteeForRecentCandidate(Designation $designation, Adherent $adherent): ?Committee
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin('committee.committeeElections', 'election')
            ->innerJoin('election.designation', 'designation', Join::WITH, 'designation.type = :designation_type')
            ->innerJoin('election.candidacies', 'candidacy', Join::WITH, 'candidacy.status = :candidacy_status AND candidacy.createdAt >= :candidacy_date')
            ->innerJoin('candidacy.committeeMembership', 'membership', Join::WITH, 'membership.adherent = :adherent')
            ->where('committee.status = :status')
            ->setParameters([
                'designation_type' => $designation->getType(),
                'candidacy_status' => CandidacyInterface::STATUS_CONFIRMED,
                'adherent' => $adherent,
                'candidacy_date' => new \DateTime('-3 months'),
                'status' => Committee::APPROVED,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findCommitteeForRecentVote(Designation $designation, Adherent $adherent): ?Committee
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin(ElectionEntity::class, 'election_entity', Join::WITH, 'election_entity.committee = committee')
            ->innerJoin('election_entity.election', 'election')
            ->innerJoin('election.designation', 'designation', Join::WITH, 'designation.type = :designation_type')
            ->innerJoin('election.electionRounds', 'election_round')
            ->innerJoin(Voter::class, 'voter', Join::WITH, 'voter.adherent = :adherent')
            ->innerJoin(Vote::class, 'vote', Join::WITH, 'vote.voter = voter AND vote.electionRound = election_round AND vote.votedAt >= :vote_date')
            ->where('committee.status = :status')
            ->setParameters([
                'designation_type' => $designation->getType(),
                'adherent' => $adherent,
                'vote_date' => new \DateTime('-3 months'),
                'status' => Committee::APPROVED,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
