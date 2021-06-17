<?php

namespace App\Repository\Poll;

use App\Entity\Poll\NationalPoll;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NationalPollRepository extends ServiceEntityRepository
{
    use UnpublishPollTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NationalPoll::class);
    }

    public function findLastActivePoll(): ?NationalPoll
    {
        return $this->createQueryBuilder('poll')
            ->where('poll.finishAt > :now')
            ->orderBy('poll.finishAt', 'desc')
            ->setParameter('now', new Chronos())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
