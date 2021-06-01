<?php

namespace App\Repository;

use App\Coordinator\Filter\CitizenProjectFilter;
use App\Entity\Adherent;
use App\Entity\BaseGroup;
use App\Entity\CitizenProject;
use App\Geocoder\Coordinates;
use App\Search\SearchParametersFilter;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class CitizenProjectRepository extends AbstractGroupRepository
{
    use NearbyTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CitizenProject::class);
    }

    /**
     * Returns the total number of approved citizen projects.
     */
    public function countApprovedCitizenProjects(): int
    {
        return $this
            ->createQueryBuilder('g')
            ->select('COUNT(g.uuid)')
            ->where('g.status = :status')
            ->setParameter('status', BaseGroup::APPROVED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CitizenProject[]
     */
    public function findCitizenProjects(array $uuids, int $statusFilter = self::ONLY_APPROVED, int $limit = 0): array
    {
        if (!$uuids) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->where('c.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->orderBy('c.membersCount', 'DESC')
        ;

        if (self::ONLY_APPROVED === $statusFilter) {
            $qb
                ->andWhere('c.status = :status')
                ->setParameter('status', BaseGroup::APPROVED)
            ;
        }

        if ($limit >= 1) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return CitizenProject[]
     */
    public function findAllRegisteredCitizenProjectsForAdherent(
        Adherent $adherent,
        bool $onlyAdministrated = false
    ): array {
        $memberships = $adherent->getCitizenProjectMemberships(true);

        if ($onlyAdministrated) {
            $memberships = $memberships->getCitizenProjectAdministratorMemberships();
        }

        return $this->sortCitizenProjects(
            $this->findCitizenProjects($memberships->getCitizenProjectUuids(), self::INCLUDE_UNAPPROVED),
            $adherent
        );
    }

    /**
     * Sorts Citizen Projects: administrated - with status approved - with status pending/pre-approved/pre-refused
     */
    public function sortCitizenProjects(array $citizenProjects, Adherent $adherent): array
    {
        uasort($citizenProjects, function (CitizenProject $a, CitizenProject $b) use ($adherent) {
            if ($adherent->isAdministratorOf($a)) {
                return -1;
            } elseif ($adherent->isAdministratorOf($b)) {
                return 1;
            } else {
                return $b->isApproved() <=> $a->isApproved();
            }
        });

        return $citizenProjects;
    }

    public function findOneApprovedBySlug(string $slug): ?CitizenProject
    {
        return $this
            ->createQueryBuilder('g')
            ->where('g.slug = :slug')
            ->andWhere('g.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', BaseGroup::APPROVED)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findManagedByCoordinator(Adherent $coordinator, CitizenProjectFilter $filter): array
    {
        if (!$coordinator->isCoordinatorCitizenProjectSector()) {
            return [];
        }

        $qb = $this->createQueryBuilder('cp')
            ->orderBy('cp.name', 'ASC')
            ->orderBy('cp.createdAt', 'DESC')
        ;

        $filter->setCoordinator($coordinator);
        $filter->apply($qb, 'cp');

        return $qb->getQuery()->getResult();
    }

    public function findManagedByReferent(Adherent $referent): array
    {
        if (!$referent->isReferent()) {
            return [];
        }

        $qb = $this->createQueryBuilder('cp')
            ->select('cp')
            ->join('cp.referentTags', 'tag')
            ->where('cp.status = :status')
            ->andWhere('tag IN (:tags)')
            ->setParameter('status', BaseGroup::APPROVED)
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->orderBy('cp.name', 'ASC')
            ->orderBy('cp.createdAt', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return CitizenProject[]
     */
    public function searchAll(SearchParametersFilter $search): iterable
    {
        if (SearchParametersFilter::TYPE_CITIZEN_PROJECTS !== $search->getType()) {
            throw new \LogicException(sprintf('Only %s is supported', SearchParametersFilter::TYPE_CITIZEN_PROJECTS));
        }

        if ($search->getRadius() > 0 && $coordinates = $search->getCityCoordinates()) {
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
            $qb->setParameter('query', "%$query%");
        }

        return $qb
            ->andWhere('n.status = :status')
            ->setParameter('status', CitizenProject::APPROVED)
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasCitizenProjectInStatus(Adherent $adherent, array $status): bool
    {
        $nb = $this->createQueryBuilder('cp')
            ->select('COUNT(cp) AS nb')
            ->where('cp.createdBy = :creator')
            ->andWhere('cp.status IN (:status)')
            ->setParameter('creator', $adherent->getUuid()->toString())
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $nb > 0;
    }

    public function findCitizenProjectUuidByCreatorUuids(array $creatorsUuid): array
    {
        $qb = $this->createQueryBuilder('cp');

        $query = $qb
            ->select('cp.uuid')
            ->where('cp.createdBy IN (:creatorsUuid)')
            ->setParameter('creatorsUuid', $creatorsUuid)
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    public function findNearCitizenProjectByCoordinates(Coordinates $coordinates, int $limit = 3): array
    {
        return $this
            ->createNearbyQueryBuilder($coordinates)
            ->where('n.status = :status')
            ->setParameter('status', BaseGroup::APPROVED)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
