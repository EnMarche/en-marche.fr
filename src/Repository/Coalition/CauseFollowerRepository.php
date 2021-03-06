<?php

namespace App\Repository\Coalition;

use App\Entity\Coalition\CauseFollower;
use App\Entity\Coalition\Coalition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CauseFollowerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CauseFollower::class);
    }

    public function countByCoalition(Coalition $coalition): int
    {
        return (int) $this
            ->createQueryBuilder('follower')
            ->select('COUNT(1)')
            ->innerJoin('follower.cause', 'cause')
            ->where('cause.coalition = :coalition')
            ->setParameter('coalition', $coalition)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function createSubscribedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('f')
            ->where('f.emailAddress IS NOT NULL')
            ->andWhere('f.cguAccepted = :true')
            ->setParameter('true', true)
        ;
    }

    public function findLastOne(string $email): ?CauseFollower
    {
        return $this->createQueryBuilder('follower')
            ->where('follower.emailAddress = :email')
            ->setParameter('email', $email)
            ->orderBy('follower.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
