<?php

namespace App\Repository;

use App\Entity\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function createAllComingNextByRoundDateQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.rounds', 'r')
            ->where('r.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('r.date', 'ASC')
        ;
    }

    public function findComingNextElection(): ?Election
    {
        $elections = $this->createAllComingNextByRoundDateQueryBuilder()
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return $elections[0] ?? null;
    }

    public function findClosestElection(): ?Election
    {
        return $this->createQueryBuilder('e')
            ->addSelect('round')
            ->innerJoin('e.rounds', 'round')
            ->setParameter('now', new \DateTime())
            ->orderBy('ABS(TIMESTAMPDIFF(SECOND, round.date, :now))', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    public function findElectionOfRound(int $id): ?Election
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.rounds', 'r')
            ->where('r.id = :round_id')
            ->setParameter('round_id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getMunicipalElection2014(): ?Election
    {
        return $this->createQueryBuilder('e')
            ->addSelect('r')
            ->leftJoin('e.rounds', 'r')
            ->where("DATE_FORMAT(r.date, 'YYYYMM') = '201403'")
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
