<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    /**
     * Finds all available skills for autocomplete.
     */
    public function findAvailableSkillsFor(string $term, Adherent $user): array
    {
        $qbUserSkills = $this
            ->createQueryBuilder('us')
            ->select('us.slug')
            ->innerJoin('us.summaries', 'cv')
            ->andWhere('cv.member = :user')
        ;

        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('ILIKE(s.slug, :slug) = true')
            ->andWhere($qb->expr()->notIn('s.slug', $qbUserSkills->getDQL()))
            ->setParameters([
                'slug' => $term.'%',
                'user' => $user,
            ])
        ;

        $skills = $qb->getQuery()->getArrayResult();

        foreach ($skills as $skill) {
            $names[] = $skill['name'];
        }

        return $names ?? [];
    }

    /**
     * Finds all skills for autocomplete.
     */
    public function findAvailableSkillsForAdmin(string $term): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('ILIKE(s.slug, :slug) = true')
            ->setParameters([
                'slug' => $term.'%',
            ])
        ;

        $skills = $qb->getQuery()->getArrayResult();

        foreach ($skills as $skill) {
            $names[] = $skill['name'];
        }

        return $names ?? [];
    }
}
