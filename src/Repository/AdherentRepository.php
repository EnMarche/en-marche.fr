<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\BoardMember\BoardMemberFilter;
use App\Collection\AdherentCollection;
use App\Coordinator\CoordinatorManagedAreaUtils;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\BoardMember\BoardMember;
use App\Entity\CitizenProject;
use App\Entity\City;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\District;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ReferentManagedArea;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Statistics\StatisticsParametersFilter;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\AreaUtils;
use App\Utils\RepositoryUtils;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdherentRepository extends ServiceEntityRepository implements UserLoaderInterface, UserProviderInterface
{
    use NearbyTrait;
    use ReferentTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }
    use PaginatorTrait;
    use GeoFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adherent::class);
    }

    public function countElements(): int
    {
        return (int) $this
            ->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countAdherents(): int
    {
        return (int) $this
            ->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.adherent = true')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Finds an Adherent instance by its email address.
     */
    public function findOneByEmail(string $email): ?Adherent
    {
        return $this->findOneBy(['emailAddress' => $email]);
    }

    public function findIdentifiersByEmail(string $email): ?array
    {
        return $this->createQueryBuilder('a')
            ->select('a.id, a.uuid, a.emailAddress')
            ->where('a.emailAddress = :email_address')
            ->setParameter('email_address', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds an active Adherent instance by its email address.
     */
    public function findOneActiveByEmail(string $email): ?Adherent
    {
        return $this->createQueryBuilder('adherent')
            ->where('adherent.emailAddress = :email')
            ->andWhere('adherent.status = :status')
            ->setParameters([
                'email' => $email,
                'status' => Adherent::ENABLED,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds an active Adherent instance by its id.
     */
    public function findOneActiveById(int $id): ?Adherent
    {
        return $this->createQueryBuilder('adherent')
            ->where('adherent.id = :id')
            ->andWhere('adherent.status = :status')
            ->setParameters([
                'id' => $id,
                'status' => Adherent::ENABLED,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function isAdherent(string $email): bool
    {
        return (bool) $this
            ->createQueryBuilder('adherent')
            ->select('COUNT(adherent)')
            ->where('adherent.emailAddress = :email')
            ->andWhere('adherent.adherent = :true')
            ->andWhere('adherent.status = :status')
            ->setParameters([
                'email' => $email,
                'true' => true,
                'status' => Adherent::ENABLED,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Finds an Adherent instance by its unique UUID.
     *
     * @return Adherent|null
     */
    public function findByUuid(string $uuid)
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findByEmails(array $emails): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.emailAddress IN (:emails)')
            ->setParameter('emails', $emails)
            ->orderBy('a.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function loadUserByUsername($username)
    {
        return $this
            ->createQueryBuilder('a')
            ->addSelect('pma')
            ->addSelect('ccpa')
            ->addSelect('cca')
            ->addSelect('cm')
            ->addSelect('c')
            ->addSelect('cpm')
            ->addSelect('cp')
            ->addSelect('bm')
            ->addSelect('ama')
            ->addSelect('jma')
            ->addSelect('mca')
            ->addSelect('rtm')
            ->addSelect('ma')
            ->addSelect('rda')
            ->addSelect('scma')
            ->addSelect('ref_tags')
            ->addSelect('lre')
            ->leftJoin('a.procurationManagedArea', 'pma')
            ->leftJoin('a.assessorManagedArea', 'ama')
            ->leftJoin('a.jecouteManagedArea', 'jma')
            ->leftJoin('a.coordinatorCitizenProjectArea', 'ccpa')
            ->leftJoin('a.coordinatorCommitteeArea', 'cca')
            ->leftJoin('a.municipalChiefManagedArea', 'mca')
            ->leftJoin('a.referentTeamMember', 'rtm')
            ->leftJoin('a.managedArea', 'ma')
            ->leftJoin('ma.tags', 'ref_tags')
            ->leftJoin('a.memberships', 'cm')
            ->leftJoin('cm.committee', 'c')
            ->leftJoin('a.citizenProjectMemberships', 'cpm')
            ->leftJoin('cpm.citizenProject', 'cp')
            ->leftJoin('a.boardMember', 'bm')
            ->leftJoin('a.receivedDelegatedAccesses', 'rda')
            ->leftJoin('a.senatorialCandidateManagedArea', 'scma')
            ->leftJoin('a.lreArea', 'lre')
            ->where('a.emailAddress = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = \get_class($user);
        $username = $user->getUsername();

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('User of type "%s" and identified by "%s" is not supported by this provider.', $class, $username));
        }

        if (!$user = $this->loadUserByUsername($username)) {
            throw new UsernameNotFoundException(sprintf('Unable to find Adherent user identified by "%s".', $username));
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return Adherent::class === $class;
    }

    /**
     * Returns the total number of active Adherent accounts.
     */
    public function countActiveAdherents(): int
    {
        $query = $this
            ->createQueryBuilder('a')
            ->select('COUNT(a.uuid)')
            ->where('a.status = :status')
            ->andWhere('a.adherent = true')
            ->setParameter('status', Adherent::ENABLED)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult();
    }

    /**
     * Finds the list of adherent matching the given list of UUIDs.
     */
    public function findList(array $uuids): AdherentCollection
    {
        if (!$uuids) {
            return new AdherentCollection();
        }

        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->where($qb->expr()->in('a.uuid', $uuids))
            ->getQuery()
        ;

        return new AdherentCollection($query->getResult());
    }

    /**
     * Finds the list of referents.
     *
     * @return Adherent[]
     */
    public function findReferents(): array
    {
        return $this
            ->createReferentQueryBuilder()
            ->getQuery()
            ->getResult()
        ;
    }

    public function findReferent(string $identifier): ?Adherent
    {
        $qb = $this->createReferentQueryBuilder();

        if (Uuid::isValid($identifier)) {
            $qb
                ->andWhere('a.uuid = :uuid')
                ->setParameter('uuid', Uuid::fromString($identifier)->toString())
            ;
        } else {
            $qb
                ->andWhere('LOWER(a.emailAddress) = :email')
                ->join('a.managedArea', 'managedArea')
                ->join('managedArea.tags', 'tags')
                ->setParameter('email', $identifier)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function createReferentQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('a')
            ->innerJoin('a.managedArea', 'managed_area')
            ->innerJoin('managed_area.tags', 'managed_area_tag')
            ->orderBy('LOWER(managed_area_tag.name)', 'ASC')
        ;
    }

    public function findReferentsByCommittee(Committee $committee): AdherentCollection
    {
        $qb = $this
            ->createReferentQueryBuilder()
            ->andWhere('managed_area_tag IN (:tags)')
            ->setParameter('tags', $committee->getReferentTags())
        ;

        return new AdherentCollection($qb->getQuery()->getResult());
    }

    public function findCoordinatorsByCitizenProject(CitizenProject $citizenProject): AdherentCollection
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin('a.coordinatorCitizenProjectArea', 'ccpa')
            ->where('ccpa.codes IS NOT NULL')
            ->andWhere(":code = ANY_OF(string_to_array(ccpa.codes, ','))")
            ->andWhere('LENGTH(ccpa.codes) > 0')
            ->orderBy('LOWER(ccpa.codes)', 'ASC')
            ->setParameter('code', CoordinatorManagedAreaUtils::getCodeFromCitizenProject($citizenProject))
        ;

        return new AdherentCollection($qb->getQuery()->getResult());
    }

    /**
     * Finds the list of adherents managed by the given referent.
     *
     * @return Adherent[]
     */
    public function findAllManagedBy(Adherent $referent): array
    {
        if (!$referent->isReferent()) {
            return [];
        }

        return $this->createQueryBuilder('a')
            ->select('a', 'm')
            ->leftJoin('a.memberships', 'm')
            ->innerJoin('a.referentTags', 'tag')
            ->andWhere('tag IN (:tags)')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->orderBy('a.registeredAt', 'DESC')
            ->addOrderBy('a.firstName', 'ASC')
            ->addOrderBy('a.lastName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchBoardMembers(BoardMemberFilter $filter, Adherent $excludedMember): array
    {
        return $this
            ->createBoardMemberFilterQueryBuilder($filter, $excludedMember)
            ->getQuery()
            ->getResult()
        ;
    }

    public function paginateBoardMembers(BoardMemberFilter $filter, Adherent $excludedMember): Paginator
    {
        $qb = $this
            ->createBoardMemberFilterQueryBuilder($filter, $excludedMember)
            ->setFirstResult($filter->getOffset())
            ->setMaxResults(BoardMemberFilter::PER_PAGE)
        ;

        return new Paginator($qb);
    }

    private function createBoardMemberQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('a')
            ->addSelect('bm')
            ->addSelect('ap')
            ->addSelect('ccpa')
            ->addSelect('cca')
            ->addSelect('cm')
            ->addSelect('bmr')
            ->innerJoin('a.boardMember', 'bm')
            ->leftJoin('a.procurationManagedArea', 'ap')
            ->leftJoin('a.coordinatorCitizenProjectArea', 'ccpa')
            ->leftJoin('a.coordinatorCommitteeArea', 'cca')
            ->leftJoin('a.memberships', 'cm')
            ->innerJoin('bm.roles', 'bmr')
        ;
    }

    private function createBoardMemberFilterQueryBuilder(
        BoardMemberFilter $filter,
        Adherent $excludedMember
    ): QueryBuilder {
        $qb = $this->createBoardMemberQueryBuilder();

        $qb->andWhere('a != :member');
        $qb->setParameter('member', $excludedMember);

        if ($queryGender = $filter->getQueryGender()) {
            $qb->andWhere('a.gender = :gender');
            $qb->setParameter('gender', $queryGender);
        }

        if ($queryAgeMinimum = $filter->getQueryAgeMinimum()) {
            $dateMaximum = new \DateTime('now');
            $dateMaximum->modify('-'.$queryAgeMinimum.' years');

            $qb->andWhere('a.birthdate <= :dateMaximum');
            $qb->setParameter('dateMaximum', $dateMaximum->format('Y-m-d'));
        }

        if ($queryAgeMaximum = $filter->getQueryAgeMaximum()) {
            $dateMinimum = new \DateTime('now');
            $dateMinimum->modify('-'.$queryAgeMaximum.' years');

            $qb->andWhere('a.birthdate >= :dateMinimum');
            $qb->setParameter('dateMinimum', $dateMinimum->format('Y-m-d'));
        }

        if ($queryFirstName = $filter->getQueryFirstName()) {
            $qb->andWhere('a.firstName LIKE :firstName');
            $qb->setParameter('firstName', '%'.$queryFirstName.'%');
        }

        if ($queryLastName = $filter->getQueryLastName()) {
            $qb->andWhere('a.lastName LIKE :lastName');
            $qb->setParameter('lastName', '%'.$queryLastName.'%');
        }

        if ($queryPostalCode = $filter->getQueryPostalCode()) {
            $queryPostalCode = array_map('trim', explode(',', $queryPostalCode));

            $postalCodeExpression = $qb->expr()->orX();

            foreach ($queryPostalCode as $key => $postalCode) {
                $postalCodeExpression->add('a.postAddress.postalCode LIKE :postalCode_'.$key);
                $qb->setParameter('postalCode_'.$key, $postalCode.'%');
            }

            $qb->andWhere($postalCodeExpression);
        }

        if (\count($queryAreas = $filter->getQueryAreas())) {
            $areasExpression = $qb->expr()->orX();

            foreach ($queryAreas as $key => $area) {
                $areasExpression->add('bm.area = :area_'.$key);
                $qb->setParameter('area_'.$key, $area);
            }

            $qb->andWhere($areasExpression);
        }

        if (\count($queryRoles = $filter->getQueryRoles())) {
            $rolesExpression = $qb->expr()->orX();

            foreach ($queryRoles as $key => $role) {
                $rolesExpression->add('bmr.code = :board_member_role_'.$key);
                $qb->setParameter('board_member_role_'.$key, $role);
            }

            $qb->andWhere($rolesExpression);
        }

        return $qb;
    }

    public function findSavedBoardMember(BoardMember $owner): AdherentCollection
    {
        $qb = $this
            ->createBoardMemberQueryBuilder()
            ->where(':member MEMBER OF bm.owners')
            ->setParameter('member', $owner)
        ;

        return new AdherentCollection($qb->getQuery()->getResult());
    }

    /**
     * @return string[]
     */
    public function findAdherentsUuidByFirstName(string $firstName): array
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->select('a.uuid')
            ->where('LOWER(a.firstName) LIKE :firstName')
            ->setParameter('firstName', '%'.strtolower($firstName).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    /**
     * @return string[]
     */
    public function findAdherentsUuidByLastName(string $lastName): array
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->select('a.uuid')
            ->where('LOWER(a.lastName) LIKE :lastName')
            ->setParameter('lastName', '%'.strtolower($lastName).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    /**
     * @return string[]
     */
    public function findAdherentsUuidByEmailAddress(string $emailAddress): array
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->select('a.uuid')
            ->where('LOWER(a.emailAddress) LIKE :emailAddress')
            ->setParameter('emailAddress', '%'.strtolower($emailAddress).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    public function findAdherentsByName(array $tags, ?string $name): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.referentTags', 'referent_tags')
        ;

        if ($name) {
            $qb
                ->orWhere('CONCAT(LOWER(a.firstName), \' \', LOWER(a.lastName)) LIKE :name')
                ->setParameter('name', '%'.strtolower($name).'%')
            ;
        }

        $this->applyGeoFilter($qb, $tags, 'a', null, null, 'referent_tags');

        return $qb->getQuery()->getResult();
    }

    public function countByGender(): array
    {
        return $this->createQueryBuilder('a', 'a.gender')
            ->select('a.gender, COUNT(a) AS count')
            ->where('a.adherent = true')
            ->andWhere('a.status = :status')
            ->setParameter('status', Adherent::ENABLED)
            ->groupBy('a.gender')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function countByGenderManagedBy(Adherent $referent): array
    {
        $this->checkReferent($referent);

        return $this->createQueryBuilder('a', 'a.gender')
            ->select('a.gender, COUNT(DISTINCT a) AS count')
            ->innerJoin('a.referentTags', 'tag')
            ->where('tag.id IN (:tags)')
            ->andWhere('a.adherent = true')
            ->andWhere('a.status = :status')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Adherent::ENABLED)
            ->groupBy('a.gender')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function countSupervisorsByGenderForReferent(Adherent $referent): array
    {
        $this->checkReferent($referent);

        $result = $this->createQueryBuilder('adherent', 'adherent.gender')
            ->select('adherent.gender, COUNT(DISTINCT adherent) AS count')
            ->join('adherent.memberships', 'membership')
            ->join('membership.committee', 'committee')
            ->join('committee.adherentMandates', 'mandate')
            ->join('committee.referentTags', 'tag')
            ->where('tag.id IN (:tags)')
            ->andWhere('committee.status = :status')
            ->andWhere('mandate.adherent = adherent AND mandate.committee IS NOT NULL AND mandate.quality = :supervisor AND mandate.finishAt IS NULL')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
            ->groupBy('adherent.gender')
            ->getQuery()
            ->getArrayResult()
        ;

        return $this->formatCount($result);
    }

    public function countMembersByGenderForReferent(Adherent $referent): array
    {
        $this->checkReferent($referent);

        $result = $this->createQueryBuilder('adherent', 'adherent.gender')
            ->select('adherent.gender, COUNT(DISTINCT adherent) AS count')
            ->join('adherent.memberships', 'membership')
            ->join('membership.committee', 'committee')
            ->innerJoin('committee.referentTags', 'tag')
            ->where('tag.id IN (:tags)')
            ->andWhere('committee.status = :status')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Committee::APPROVED)
            ->groupBy('adherent.gender')
            ->getQuery()
            ->getArrayResult()
        ;

        return $this->formatCount($result);
    }

    public function countCommitteeMembersInReferentManagedArea(
        Adherent $referent,
        StatisticsParametersFilter $filter = null,
        int $months = 5
    ): array {
        $this->checkReferent($referent);

        $query = $this->createQueryBuilder('adherent', 'adherent.gender')
            ->select("COUNT(DISTINCT adherent.id) AS count, DATE_FORMAT(event.beginAt, 'YYYYMM') as yearmonth")
            ->join('adherent.memberships', 'membership')
            ->join('membership.committee', 'committee')
            ->innerJoin('committee.referentTags', 'tag')
            ->where('tag IN (:tags)')
            ->andWhere('committee.status = :status')
            ->andWhere('membership.joinedAt >= :from')
            ->andWhere('membership.joinedAt <= :until')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('until', (new Chronos('now'))->setTime(23, 59, 59, 999))
            ->setParameter('from', (new Chronos("first day of -$months months"))->setTime(0, 0, 0, 000))
            ->groupBy('yearmonth')
        ;

        $query = RepositoryUtils::addStatstFilter($filter, $query)->getQuery();
        $query->useResultCache(true, 3600); // 1 hour

        return RepositoryUtils::aggregateCountByMonth($query->getArrayResult());
    }

    private function formatCount(array $count): array
    {
        array_walk($count, function (&$item) {
            $item = (int) $item['count'];
        });

        $count['total'] = array_sum($count);

        return $count;
    }

    public function countMembersManagedBy(Adherent $referent, \DateTimeInterface $until): int
    {
        $this->assertReferent($referent);

        $query = $this->createQueryBuilder('adherent')
            ->select('COUNT(DISTINCT adherent) AS count')
            ->innerJoin('adherent.referentTags', 'tag')
            ->where('tag IN (:tags)')
            ->andWhere('adherent.activatedAt <= :until')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('until', $until)
            ->getQuery()
        ;

        // Let's cache past data as they are never going to change
        $firstDayOfMonth = (new Chronos('first day of this month'))->setTime(0, 0);
        if ($firstDayOfMonth > $until) {
            $query->useResultCache(true, 5184000); // 60 days
        }

        return (int) $query->getSingleScalarResult();
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function assertReferent(Adherent $referent): void
    {
        if (!$referent->isReferent()) {
            throw new \InvalidArgumentException('Adherent must be a referent.');
        }
    }

    /**
     * Finds enabled adherents in the deputy district that subscribed to deputy emails.
     */
    public function createQueryBuilderForDistrict(District $district): QueryBuilder
    {
        return $this->createQueryBuilder('adherent')
            ->distinct()
            ->join('adherent.referentTags', 'tag')
            ->join('adherent.subscriptionTypes', 'subscriptionType')
            ->where('tag = :tag')
            ->andWhere('adherent.status = :status')
            ->andWhere('subscriptionType.code = :citizen_project_subscription_type')
            ->setParameter('tag', $district->getReferentTag())
            ->setParameter('status', Adherent::ENABLED)
            ->setParameter('citizen_project_subscription_type', SubscriptionTypeEnum::DEPUTY_EMAIL)
        ;
    }

    /**
     * Count enabled adherents in the deputy district.
     */
    public function countAllInDistrict(District $district): int
    {
        return $this->createQueryBuilderForDistrict($district)
            ->select('COUNT(adherent)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function createDispatcherIteratorForDistrict(District $district, $offset = null): IterableResult
    {
        $qb = $this->createQueryBuilderForDistrict($district)
            ->select('partial adherent.{id, firstName, lastName, emailAddress}')
        ;

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)->iterate();
    }

    public function countInManagedArea(ReferentManagedArea $managedArea): int
    {
        return $this->createQueryBuilder('adherent')
            ->select('COUNT(adherent)')
            ->innerJoin('adherent.referentTags', 'tag')
            ->where('tag IN (:tags)')
            ->andWhere('adherent.status = :status')
            ->setParameter('tags', $managedArea->getTags())
            ->setParameter('status', Adherent::ENABLED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countSubscriberInManagedArea(ReferentManagedArea $managedArea): int
    {
        return $this->createQueryBuilder('adherent')
            ->select('COUNT(DISTINCT(adherent))')
            ->innerJoin('adherent.referentTags', 'tag')
            ->innerJoin('adherent.subscriptionTypes', 'subscriptiontypes')
            ->where('tag IN (:tags)')
            ->andWhere('adherent.status = :status')
            ->setParameter('tags', $managedArea->getTags())
            ->setParameter('status', Adherent::ENABLED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function refresh(Adherent $adherent): void
    {
        $this->getEntityManager()->refresh($adherent);
    }

    public function findPaginatedForInseeCodes(
        array $inseeCodes,
        int $page = 1,
        int $maxItemPerPage = 10
    ): PaginatorInterface {
        $qb = $this
            ->createQueryBuilder('a')
            ->where("SUBSTRING_INDEX(a.postAddress.city, '-', -1) = ANY_OF(:insee_codes)")
            ->andWhere('a.adherent = true')
            ->setParameter('insee_codes', $inseeCodes)
        ;

        return $this->configurePaginator($qb, $page, $maxItemPerPage);
    }

    public function findOneForMatching(string $emailAddress, string $firstName, string $lastName): ?Adherent
    {
        return $this
            ->createQueryBuilder('adherent')
            ->andWhere('adherent.emailAddress = :emailAddress')
            ->andWhere('adherent.firstName = :firstName')
            ->andWhere('adherent.lastName = :lastName')
            ->setParameter('emailAddress', $emailAddress)
            ->setParameter('firstName', $firstName)
            ->setParameter('lastName', $lastName)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findIdByUuids(array $uuids): array
    {
        return array_column(
            $this->createQueryBuilder('a')
                ->select('a.id')
                ->where('a.uuid IN (:uuids)')
                ->setParameter('uuids', $uuids)
                ->getQuery()
                ->getResult(),
            'id'
        );
    }

    public function getCrmParisRecords(): array
    {
        $sql = <<<SQL
            SELECT
                a.uuid,
                a.first_name,
                a.last_name,
                a.email_address,
                a.phone,
                a.address_address AS address,
                a.address_postal_code AS postal_code,
                a.address_city_name AS city,
                IF(
                    5 = LENGTH(a.address_postal_code),
                    CAST(SUBSTRING(a.address_postal_code, 4, 2) AS UNSIGNED),
                    NULL
                ) AS district,
                a.gender,
                DATE_FORMAT(a.birthdate, '%d/%m/%Y') AS birthdate,
                a.address_latitude AS latitude,
                a.address_longitude AS longitude,
                a.interests,
                COALESCE(
                    (
                        SELECT 1
                        FROM adherent_subscription_type ast
                        INNER JOIN subscription_type st
                            ON ast.subscription_type_id = st.id
                        WHERE ast.adherent_id = a.id
                        AND st.code = :sms_mms_subscription_code
                        LIMIT 1
                    ),
                    0
                ) AS sms_mms
            FROM adherents a
            WHERE a.address_country = :country_code_france
            AND a.address_postal_code LIKE :prefix_postalcode_paris
            AND EXISTS (
                SELECT 1
                FROM adherent_subscription_type ast
                INNER JOIN subscription_type st
                    ON ast.subscription_type_id = st.id
                WHERE ast.adherent_id = a.id
                AND st.code = :candidate_email_subscription_code
                LIMIT 1
            )
            ;
SQL;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('country_code_france', AreaUtils::CODE_FRANCE);
        $stmt->bindValue('prefix_postalcode_paris', AreaUtils::PREFIX_POSTALCODE_PARIS_DISTRICTS.'%');
        $stmt->bindValue('candidate_email_subscription_code', SubscriptionTypeEnum::CANDIDATE_EMAIL);
        $stmt->bindValue('sms_mms_subscription_code', SubscriptionTypeEnum::MILITANT_ACTION_SMS);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return Adherent[]
     */
    public function findAssessorsForVotePlaces(array $votePlaces): array
    {
        return array_column(
            $this->createQueryBuilder('a')
                ->addSelect('pl.id AS votePlaceId')
                ->innerJoin('a.assessorRole', 'ar')
                ->innerJoin('ar.votePlace', 'pl')
                ->where('pl IN (:places)')
                ->setParameter('places', $votePlaces)
                ->getQuery()
                ->getResult(),
            0,
            'votePlaceId'
        );
    }

    /**
     * @var City[]|array
     *
     * @return Adherent[]
     */
    public function findMunicipalManagersForCities(array $cities): array
    {
        /** @var Adherent[]|array $adherents */
        $adherents = $this
            ->createQueryBuilder('a')
            ->innerJoin('a.municipalManagerRole', 'mmr')
            ->innerJoin('mmr.cities', 'c')
            ->where('c IN (:cities)')
            ->setParameter('cities', $cities)
            ->getQuery()
            ->getResult()
        ;

        $data = [];
        foreach ($cities as $city) {
            foreach ($adherents as $adherent) {
                if ($adherent->getMunicipalManagerRole()->getCities()->contains($city)) {
                    $data[$city->getId()] = $adherent;
                }
            }
        }

        return $data;
    }

    public function findDuplicateCertified(
        string $firstName,
        string $lastName,
        \DateTimeInterface $birthDate,
        Adherent $ignoredAdherent
    ): ?Adherent {
        return $this
            ->createQueryBuilder('a')
            ->andWhere('a.firstName = :first_name')
            ->andWhere('a.lastName = :last_name')
            ->andWhere('a.birthdate = :birth_date')
            ->andWhere('a.certifiedAt IS NOT NULL')
            ->andWhere('a != :ignored_adherent')
            ->setParameters([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $birthDate,
                'ignored_adherent' => $ignoredAdherent,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByTerritorialCouncilAndQuality(
        TerritorialCouncil $territorialCouncil,
        string $quality,
        Adherent $exceptOf = null
    ): ?Adherent {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.territorialCouncilMembership', 'tcm')
            ->leftJoin('tcm.qualities', 'quality')
            ->where('tcm.territorialCouncil = :tc')
            ->andWhere('quality.name = :quality')
            ->setParameter('tc', $territorialCouncil)
            ->setParameter('quality', $quality)
        ;

        if ($exceptOf) {
            $qb
                ->andWhere('a.id != :adherent')
                ->setParameter('adherent', $exceptOf)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findSimilarProfilesForElectedRepresentative(ElectedRepresentative $electedRepresentative)
    {
        return $this->createQueryBuilder('a')
            ->where('LOWER(a.firstName) = :firstName AND LOWER(a.lastName) = :lastName')
            ->orWhere('a.birthdate = :birthDate')
            ->setParameters([
                'firstName' => $electedRepresentative->getFirstName(),
                'lastName' => $electedRepresentative->getLastName(),
                'birthDate' => $electedRepresentative->getBirthDate(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForProvisionalSupervisorAutocomplete(
        string $name,
        ?string $gender,
        array $zones,
        int $limit = 10
    ): array {
        if (!$zones || !$name) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
            ->select('DISTINCT a.id', 'a.firstName', 'a.lastName', 'a.gender', 'a.registeredAt')
            ->innerJoin('a.zones', 'zone')
            ->innerJoin('zone.parents', 'parent')
            ->where('(zone IN (:zones) OR parent IN (:zones))')
            ->andWhere('CONCAT(LOWER(a.firstName), \' \', LOWER(a.lastName)) LIKE :name')
            ->andWhere('a.status = :status AND a.birthdate <= :dateMax')
            ->setParameters([
                'zones' => $zones,
                'status' => Adherent::ENABLED,
                'name' => '%'.strtolower($name).'%',
                'dateMax' => new \DateTime('-18 years'),
            ])
            ->setMaxResults($limit)
        ;

        if ($gender) {
            $qb
                ->andWhere('a.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findNameByUuid(string $uuid): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.firstName', 'a.lastName')
            ->where('a.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findCommitteeSupervisors(Committee $committee): array
    {
        return $this->createCommitteeSupervisorsQueryBuilder($committee)->getQuery()->getResult();
    }

    public function countCommitteeSupervisors(Committee $committee): int
    {
        return (int) $this->createCommitteeSupervisorsQueryBuilder($committee)
            ->select('COUNT(DISTINCT a.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findCommitteeHosts(Committee $committee, bool $withoutSupervisors = false): AdherentCollection
    {
        return new AdherentCollection(
            $this->createCommitteeHostsQueryBuilder($committee, $withoutSupervisors)
                ->getQuery()
                ->getResult()
        );
    }

    public function countCommitteeHosts(Committee $committee, bool $withoutSupervisors = false): int
    {
        return (int) $this->createCommitteeHostsQueryBuilder($committee, $withoutSupervisors)
            ->select('COUNT(DISTINCT a.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns whether or not the given adherent is already an host of at least
     * one committee.
     */
    public function hostCommittee(Adherent $adherent, Committee $committee = null): bool
    {
        $result = (int) $this->createCommitteeHostsQueryBuilder($committee)
            ->select('COUNT(DISTINCT a.id)')
            ->resetDQLPart('orderBy')
            ->andWhere('a.id = :adherent_id')
            ->setParameter('adherent_id', $adherent->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $result > 0;
    }

    private function createCommitteeSupervisorsQueryBuilder(Committee $committee): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.adherentMandates', 'am')
            ->where('am.committee = :committee AND am.quality = :supervisor AND am.finishAt IS NULL')
            ->setParameters([
                'committee' => $committee,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
            ])
        ;
    }

    private function createCommitteeHostsQueryBuilder(
        ?Committee $committee = null,
        bool $withoutSupervisors = false
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('a');

        $cmCondition = '';
        $amCondition = '';
        if ($committee) {
            $cmCondition = 'cm.committee = :committee AND ';
            $amCondition = 'am.committee = :committee AND ';
            $qb->setParameter('committee', $committee);
        }

        if ($withoutSupervisors) {
            return $qb
                ->leftJoin(CommitteeMembership::class, 'cm', Join::WITH, $cmCondition.'cm.adherent = a')
                ->where($cmCondition.'cm.privilege = :privilege')
                ->addOrderBy('cm.privilege', 'ASC')
                ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ;
        }

        return $qb
            ->leftJoin('a.adherentMandates', 'am', Join::WITH, $amCondition.'am.adherent = a')
            ->leftJoin(CommitteeMembership::class, 'cm', Join::WITH, $cmCondition.'cm.adherent = a')
            ->where((new Orx())
                ->add('cm.privilege = :privilege')
                ->add('am.quality = :supervisor AND am.finishAt IS NULL')
            )
            ->orderBy('am.quality', 'DESC')
            ->addOrderBy('am.provisional', 'ASC')
            ->addOrderBy('cm.privilege', 'DESC')
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
        ;
    }
}
