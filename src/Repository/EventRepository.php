<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\District;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\CommitteeEvent;
use App\Entity\ReferentTag;
use App\Event\EventTypeEnum;
use App\Geocoder\Coordinates;
use App\Search\SearchParametersFilter;
use App\Statistics\StatisticsParametersFilter;
use App\Utils\RepositoryUtils;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use GeoZoneTrait;
    use GeoFilterTrait;
    use NearbyTrait;
    use ReferentTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public const TYPE_PAST = 'past';
    public const TYPE_UPCOMING = 'upcoming';
    public const TYPE_ALL = 'all';

    public function __construct(ManagerRegistry $registry, string $className = CommitteeEvent::class)
    {
        parent::__construct($registry, $className);
    }

    public function countElements(bool $onlyPublished = true): int
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->leftJoin('e.category', 'ec')
            ->select('COUNT(e)')
        ;

        if ($onlyPublished) {
            $qb->where('e.published = :published')
                ->andWhere('ec.status = :enabled')
                ->setParameter('published', true)
                ->setParameter('enabled', BaseEventCategory::ENABLED)
            ;
        }

        return (int) $qb->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findOneBySlug(string $slug): ?CommitteeEvent
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->andWhere('e.published = :published')
            ->andWhere('c.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('published', true)
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findMostRecentEvent(): ?CommitteeEvent
    {
        $query = $this
            ->createQueryBuilder('ce')
            ->where('ce.published = :published')
            ->setParameter('published', true)
            ->orderBy('ce.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findOneByUuid(string $uuid): ?BaseEvent
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function findOnePublishedBySlug(string $slug): ?BaseEvent
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneActiveBySlug(string $slug): ?BaseEvent
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('statuses', CommitteeEvent::ACTIVE_STATUSES)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findStartedEventBetweenDatesForTags(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $referentTags
    ): array {
        if (!$referentTags) {
            return [];
        }

        return $this
            ->createQueryBuilder('event')
            ->addSelect('adherent')
            ->join('event.organizer', 'adherent')
            ->where('event.beginAt < :end_date AND event.finishAt > :start_date')
            ->andWhere('event.status = :status')
            ->setParameters([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => BaseEvent::STATUS_SCHEDULED,
            ])
            ->leftJoin('event.committee', 'committee')
            ->leftJoin('committee.referentTags', 'committeeReferentTags')
            ->leftJoin('adherent.referentTags', 'adherentReferentTags')
            ->andWhere((new Orx())
                ->add('committee IS NOT NULL AND committeeReferentTags IN (:tags)')
                ->add('committee IS NULL AND adherentReferentTags IN (:tags)')
            )
            ->setParameter('tags', $referentTags)
            ->getQuery()
            ->getResult()
        ;
    }

    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('e.published = :published')
            ->setParameter('published', true)
        ;
    }

    /**
     * @return BaseEvent[]
     */
    public function findAllInDistrict(District $district): array
    {
        return $this->_em->getRepository(BaseEvent::class)->createQueryBuilder('e')
            ->select('e', 'o')
            ->leftJoin('e.organizer', 'o')
            ->innerJoin(District::class, 'd', Join::WITH, 'd.id = :district_id')
            ->innerJoin('d.geoData', 'gd')
            ->where('e.published = :published')
            ->andWhere("ST_Within(ST_GeomFromText(CONCAT('POINT(',e.postAddress.longitude,' ',e.postAddress.latitude,')')), gd.geoShape) = 1")
            ->setParameter('district_id', $district->getId())
            ->orderBy('e.beginAt', 'DESC')
            ->addOrderBy('e.name', 'ASC')
            ->setParameter('published', true)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CommitteeEvent[]
     */
    public function findUpcomingEvents(int $category = null): array
    {
        $qb = $this->createUpcomingEventsQueryBuilder();

        if ($category) {
            $qb->andWhere('ec.id = :category')->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    public function countUpcomingEvents(): int
    {
        return (int) $this
            ->createUpcomingEventsQueryBuilder()
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findEventsByOrganizer(Adherent $organizer): array
    {
        $query = $this
            ->createQueryBuilder('e')
            ->andWhere('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function findEventsByOrganizerPaginator(
        Adherent $organizer,
        int $page = 1,
        int $limit = 50
    ): PaginatorInterface {
        return $this->configurePaginator(
            $this
                ->createQueryBuilder('e')
                ->andWhere('e.organizer = :organizer')
                ->setParameter('organizer', $organizer)
                ->orderBy('e.createdAt', 'DESC'),
            $page,
            $limit,
            static function (Query $query) {
                $query
                    ->useResultCache(true)
                    ->setResultCacheLifetime(1800)
                ;
            }
        );
    }

    public function findAllPublished(int $page = 1, int $limit = 50): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('event')
            ->select('event', 'organizer')
            ->leftJoin('event.organizer', 'organizer')
            ->where('event.published = :published')
            ->orderBy('event.beginAt', 'DESC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }

    private function createUpcomingEventsQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->select('e', 'ec', 'c', 'o')
            ->leftJoin('e.category', 'ec')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.published = :published')
            ->andWhere($qb->expr()->in('e.status', BaseEvent::ACTIVE_STATUSES))
            ->andWhere('e.beginAt >= :today')
            ->andWhere('ec.status = :status')
            ->orderBy('e.beginAt', 'ASC')
            ->setParameter('published', true)
            ->setParameter('today', date('Y-m-d'))
            ->setParameter('status', BaseEventCategory::ENABLED)
        ;
    }

    public function countSitemapEvents(): int
    {
        return (int) $this
            ->createSitemapQueryBuilder()
            ->select('COUNT(c) AS nb')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findSitemapEvents(int $page, int $perPage): array
    {
        return $this
            ->createSitemapQueryBuilder()
            ->select('e.uuid', 'e.slug', 'e.updatedAt')
            ->orderBy('e.id')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    private function createSitemapQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('c.status = :status')
            ->andWhere('e.published = :published')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('published', true)
        ;
    }

    /**
     * @return CommitteeEvent[]
     */
    public function searchEvents(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this
                ->createNearbyQueryBuilder($coordinates)
                ->andWhere($this->getNearbyExpression().' < :distance_max')
                ->andWhere('n.beginAt > :today')
                ->setParameter('distance_max', $search->getRadius())
                ->setParameter('today', new \DateTime('today'))
                ->orderBy('n.beginAt', 'asc')
                ->addOrderBy('distance_between', 'asc')
            ;
        } else {
            $qb = $this->createQueryBuilder('n');
        }

        $qb->andWhere('n.published = :published')
            ->setParameter('published', true)
        ;

        if (!empty($query = $search->getQuery())) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        if ($category = $search->getEventCategory()) {
            $qb->andWhere('n.category = :category');
            $qb->setParameter('category', $category);
        }

        if ($search->getReferentEvents()) {
            $qb->andWhere('n.committee IS NULL');
        }

        return $qb
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchAllEvents(SearchParametersFilter $search): array
    {
        $sql = <<<'SQL'
SELECT events.uuid AS event_uuid, events.organizer_id AS event_organizer_id, events.committee_id AS event_committee_id, 
events.name AS event_name, events.category_id AS event_category_id, events.description AS event_description, 
events.begin_at AS event_begin_at, events.finish_at AS event_finish_at, 
events.capacity AS event_capacity, events.is_for_legislatives AS event_is_for_legislatives, 
events.created_at AS event_created_at, events.participants_count AS event_participants_count, events.slug AS event_slug,
events.type AS event_type, events.address_address AS event_address_address, 
events.address_country AS event_address_country, events.address_city_name AS event_address_city_name, 
events.address_city_insee AS event_address_city_insee, events.address_postal_code AS event_address_postal_code, 
events.address_latitude AS event_address_latitude, events.address_longitude AS event_address_longitude, events.time_zone AS timeZone,
event_category.name AS event_category_name, 
committees.uuid AS committee_uuid, committees.name AS committee_name, committees.slug AS committee_slug, 
committees.description AS committee_description, committees.created_by AS committee_created_by, 
committees.address_address AS committee_address_address, committees.address_country AS committee_address_country, 
committees.address_city_name AS committee_address_city_name, committees.address_city_insee AS committee_address_city_insee, 
committees.address_postal_code AS committee_address_postal_code, committees.address_latitude AS committee_address_latitude, 
committees.address_longitude AS committee_address_longitude, adherents.uuid AS adherent_uuid, 
adherents.email_address AS adherent_email_address, adherents.password AS adherent_password, adherents.old_password AS adherent_old_password, 
adherents.gender AS adherent_gender, adherents.first_name AS adherent_first_name, 
adherents.last_name AS adherent_last_name, adherents.birthdate AS adherent_birthdate, 
adherents.address_address AS adherent_address_address, adherents.address_country AS adherent_address_country, 
adherents.address_city_name AS adherent_address_city_name, adherents.address_city_insee AS adherent_address_city_insee, 
adherents.address_postal_code AS adherent_address_postal_code, adherents.address_latitude AS adherent_address_latitude, 
adherents.address_longitude AS adherent_address_longitude, adherents.position AS adherent_position,
(6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) AS distance 
FROM events 
LEFT JOIN adherents ON adherents.id = events.organizer_id
LEFT JOIN committees ON committees.id = events.committee_id
LEFT JOIN events_categories AS event_category ON event_category.id = events.category_id AND events.type IN (:base_event_types)
WHERE (events.address_latitude IS NOT NULL 
    AND events.address_longitude IS NOT NULL 
    AND (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) < :distance_max 
    AND events.begin_at > :today 
    AND events.published = :published
    AND events.status = :scheduled
    AND event_category.id IS NOT NULL
    )
    __filter_query__ 
    __filter_category__
    __filter_referent_events__
ORDER BY events.begin_at ASC, distance ASC 
LIMIT :max_results 
OFFSET :first_result
SQL;

        if (!empty($searchQuery = $search->getQuery())) {
            $filterQuery = 'AND events.name like :query';
        }

        if ($category = $search->getEventCategory()) {
            $filterCategory = 'AND events.category_id = :category';
        }

        if ($search->getReferentEvents()) {
            $filterReferentEvents = 'AND events.committee_id IS NULL';
        }

        $sql = preg_replace(
            ['/__filter_query__/', '/__filter_category__/', '/__filter_referent_events__/'],
            [$filterQuery ?? '', $filterCategory ?? '', $filterReferentEvents ?? ''],
            $sql
        );

        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        if ($search->getCityCoordinates()) {
            $query->setParameter('distance_max', $search->getRadius());
            $query->setParameter('today', new \DateTime('now - 1 hour'));
        }

        if (!empty($searchQuery)) {
            $query->setParameter('query', '%'.$searchQuery.'%');
        }

        if ($category) {
            $query->setParameter('category', $category);
        }

        $query->setParameter('latitude', $search->getCityCoordinates()->getLatitude());
        $query->setParameter('longitude', $search->getCityCoordinates()->getLongitude());
        $query->setParameter('published', 1, \PDO::PARAM_INT);
        $query->setParameter('scheduled', BaseEvent::STATUS_SCHEDULED);
        $query->setParameter('base_event_types', [EventTypeEnum::TYPE_COMMITTEE, EventTypeEnum::TYPE_DEFAULT]);
        $query->setParameter('first_result', $search->getOffset(), \PDO::PARAM_INT);
        $query->setParameter('max_results', $search->getMaxResults(), \PDO::PARAM_INT);

        return $query->getResult('EventHydrator');
    }

    public function removeOrganizerEvents(Adherent $organizer, string $type = self::TYPE_ALL, $anonymize = false)
    {
        $type = strtolower($type);
        $qb = $this->createQueryBuilder('e');
        if ($anonymize) {
            $qb->update()
                ->set('e.organizer', ':new_value')
                ->setParameter('new_value', null)
            ;
        } else {
            $qb->delete();
        }

        $qb
            ->where('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
        ;

        if (\in_array($type, [self::TYPE_UPCOMING, self::TYPE_PAST], true)) {
            if (self::TYPE_PAST === $type) {
                $qb->andWhere('e.beginAt <= :date');
            } else {
                $qb->andWhere('e.beginAt >= :date');
            }
            // The extra 24 hours enable to include events in foreign
            // countries that are on different timezones.
            $qb->setParameter('date', new \DateTime('-24 hours'));
        }

        return $qb->getQuery()->execute();
    }

    public function paginate(int $offset = 0, int $limit = SearchParametersFilter::DEFAULT_MAX_RESULTS): Paginator
    {
        $query = $this->createQueryBuilder('e')
            ->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return new Paginator($query);
    }

    public function findCitiesForReferentAutocomplete(Adherent $referent, $value): array
    {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('event')
            ->select('DISTINCT event.postAddress.cityName as city')
            ->join('event.referentTags', 'tag')
            ->where('event.status = :status')
            ->andWhere('event.committee IS NOT NULL')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->orderBy('city')
        ;

        if ($value) {
            $qb
                ->andWhere('event.postAddress.cityName LIKE :searchedCityName')
                ->setParameter('searchedCityName', $value.'%')
            ;
        }

        return array_column($qb->getQuery()->getArrayResult(), 'city');
    }

    public function queryCountByMonth(Adherent $referent, int $months = 5): QueryBuilder
    {
        return $this->createQueryBuilder('event')
            ->select('COUNT(DISTINCT event.id) AS count, YEAR_MONTH(event.beginAt) AS yearmonth')
            ->innerJoin('event.referentTags', 'tag')
            ->where('tag IN (:tags)')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->andWhere('event.beginAt >= :from')
            ->andWhere('event.beginAt <= :until')
            ->setParameter('from', (new Chronos("first day of -$months months"))->setTime(0, 0, 0, 000))
            ->setParameter('until', (new Chronos('now'))->setTime(23, 59, 59, 999))
            ->groupBy('yearmonth')
        ;
    }

    public function countParticipantsInReferentManagedAreaByMonthForTheLastSixMonths(Adherent $referent): array
    {
        $this->checkReferent($referent);

        $eventsCount = $this->createQueryBuilder('event')
            ->select('YEAR_MONTH(event.beginAt) AS yearmonth, event.participantsCount as count')
            ->innerJoin('event.referentTags', 'tag')
            ->where('tag IN (:tags)')
            ->andWhere('event.committee IS NOT NULL')
            ->andWhere("event.status = '".BaseEvent::STATUS_SCHEDULED."'")
            ->andWhere('event.participantsCount > 0')
            ->andWhere('event.beginAt >= :from')
            ->andWhere('event.beginAt <= :until')
            ->setParameter('from', (new Chronos('first day of -5 months'))->setTime(0, 0, 0, 000))
            ->setParameter('until', (new Chronos('now'))->setTime(23, 59, 59, 999))
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->groupBy('event.id')
            ->getQuery()
            ->useResultCache(true, 3600)
            ->getArrayResult()
        ;

        return RepositoryUtils::aggregateCountByMonth($eventsCount);
    }

    public function countParticipantsInReferentManagedArea(Adherent $referent): int
    {
        $this->checkReferent($referent);

        $referentTagIds = array_map(
            function (ReferentTag $tag) {
                return $tag->getId();
            },
            $referent->getManagedArea()->getTags()->toArray()
        );

        $query = <<<'SQL'
SELECT SUM(events_count.count) as count
FROM (
    SELECT events.participants_count AS count
    FROM events 
        INNER JOIN event_referent_tag ert ON events.id = ert.event_id 
        INNER JOIN referent_tags tags ON tags.id = ert.referent_tag_id 
    WHERE (tags.id IN (?) 
        AND events.committee_id IS NOT NULL 
        AND events.status = ? 
        AND events.participants_count > 0) 
        AND events.type = ? 
    GROUP BY events.id
) AS events_count
SQL;

        $results = $this->_em->getConnection()->executeQuery(
            $query,
            [$referentTagIds, BaseEvent::STATUS_SCHEDULED, EventTypeEnum::TYPE_COMMITTEE],
            [Connection::PARAM_STR_ARRAY, \PDO::PARAM_STR]
        );

        return $results->fetchColumn();
    }

    public function countCommitteeEventsInReferentManagedArea(
        Adherent $referent,
        StatisticsParametersFilter $filter = null
    ): array {
        $this->checkReferent($referent);

        $query = $this->queryCountByMonth($referent);
        if ($filter) {
            $query = RepositoryUtils::addStatstFilter($filter, $query);
        }

        $result = $query
            ->andWhere('event.committee IS NOT NULL')
            ->getQuery()
            ->getArrayResult()
        ;

        return RepositoryUtils::aggregateCountByMonth($result);
    }

    public function countReferentEventsInReferentManagedArea(Adherent $referent): array
    {
        $this->checkReferent($referent);

        $query = $this->queryCountByMonth($referent);

        $result = $query
            ->andWhere('event.committee IS NULL')
            ->getQuery()
            ->getArrayResult()
        ;

        return RepositoryUtils::aggregateCountByMonth($result);
    }

    public function countTotalEventsInReferentManagedAreaForCurrentMonth(Adherent $referent): int
    {
        $this->checkReferent($referent);

        $query = $this->queryCountByMonth($referent, 0);

        try {
            return (int) $query->getQuery()->getSingleResult()['count'];
        } catch (NoResultException $e) {
            return 0;
        }
    }

    /**
     * @return CommitteeEvent[]
     */
    public function findNearbyOf(
        CommitteeEvent $event,
        int $radius = SearchParametersFilter::RADIUS_10,
        int $max = 3
    ): array {
        return $this
            ->createNearbyQueryBuilder(new Coordinates($event->getLatitude(), $event->getLongitude()))
            ->andWhere($this->getNearbyExpression().' < :distance_max')
            ->andWhere('n.beginAt > :date')
            ->andWhere('n.status = :status')
            ->andwhere('n.published = :published')
            ->setParameter('distance_max', $radius)
            ->setParameter('date', $event->getBeginAt())
            ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
            ->setParameter('published', true)
            ->addOrderBy('n.beginAt', 'ASC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
        ;
    }
}
