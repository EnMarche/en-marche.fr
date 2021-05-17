<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\MunicipalEvent;
use Doctrine\Persistence\ManagerRegistry;

class MunicipalEventRepository extends EventRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MunicipalEvent::class);
    }

    public function countEventForOrganizer(Adherent $organizer): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(1)')
            ->where('e.status = :status')
            ->andWhere('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getAllCategories(): array
    {
        $results = $this->createQueryBuilder('event')
            ->select('DISTINCT category.name')
            ->innerJoin('event.category', 'category')
            ->where('event.status = :scheduled')
            ->andWhere('event.finishAt > :now')
            ->setParameter('scheduled', BaseEvent::STATUS_SCHEDULED)
            ->setParameter('now', new \DateTime())
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_column($results, 'name');
    }

    public function findCategoriesForPostalCode(array $postalCodes): array
    {
        $results = $this->createQueryBuilder('event')
            ->select('DISTINCT category.name')
            ->innerJoin('event.category', 'category')
            ->where('event.status = :scheduled')
            ->andWhere('event.finishAt > :now')
            ->andWhere('event.postAddress.postalCode IN (:codes)')
            ->setParameters([
                'scheduled' => BaseEvent::STATUS_SCHEDULED,
                'now' => new \DateTime(),
                'codes' => $postalCodes,
            ])
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_column($results, 'name');
    }
}
