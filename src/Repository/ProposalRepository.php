<?php

namespace App\Repository;

use App\Entity\Proposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProposalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proposal::class);
    }

    /**
     * @return Proposal[]
     */
    public function findAllOrderedByPosition(): array
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p', 't')
            ->leftJoin('p.themes', 't')
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlug(string $slug): ?Proposal
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p', 'm', 't')
            ->leftJoin('p.media', 'm')
            ->leftJoin('p.themes', 't')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findPublishedProposal(string $slug): ?Proposal
    {
        return $this
            ->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->andWhere('p.published = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
