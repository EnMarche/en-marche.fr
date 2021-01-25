<?php

namespace App\Repository;

use App\Entity\TurnkeyProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TurnkeyProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TurnkeyProject::class);
    }

    public function findOneApprovedBySlug(?string $slug): ?TurnkeyProject
    {
        if (!$slug) {
            return null;
        }

        return $this
            ->createQueryBuilder('project')
            ->where('project.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('project.isApproved = :approved')
            ->setParameter('approved', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function countProjects(): int
    {
        return $this
            ->createQueryBuilder('projects')
            ->select('COUNT(projects)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countApproved(): int
    {
        return $this
            ->createQueryBuilder('projects')
            ->select('COUNT(projects)')
            ->where('projects.isApproved = :approved')
            ->setParameter('approved', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return TurnkeyProject[]
     */
    public function findApprovedOrdered(): array
    {
        return $this
            ->createQueryBuilder('projects')
            ->where('projects.isApproved = :approved')
            ->setParameter('approved', true)
            ->orderBy('projects.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPinned(int $excludedProjectId = null): ?TurnkeyProject
    {
        $qb = $this
            ->createQueryBuilder('project')
            ->where('project.isPinned = true')
            ->andWhere('project.isApproved = :approved')
            ->setParameter('approved', true)
        ;

        if ($excludedProjectId) {
            $qb->andWhere('project.id != :id')
                ->setParameter('id', $excludedProjectId)
            ;
        }

        return $qb->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
