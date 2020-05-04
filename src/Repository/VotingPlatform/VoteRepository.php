<?php

namespace AppBundle\Repository\VotingPlatform;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\VotingPlatform\Election;
use AppBundle\Entity\VotingPlatform\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function alreadyVoted(Adherent $adherent, Election $election): bool
    {
        return 0 < (int) $this->createQueryBuilder('vote')
            ->select('COUNT(1)')
            ->innerJoin('vote.voter', 'voter')
            ->where('voter.adherent = :adherent AND vote.election = :election')
            ->setParameters([
                'adherent' => $adherent,
                'election' => $election,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
