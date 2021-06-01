<?php

namespace App\Repository;

use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @deprecated
 */
class ReferentTagRepository extends ServiceEntityRepository
{
    public const FRENCH_OUTSIDE_FRANCE_TAG = 'FOF';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferentTag::class);
    }

    public function findOneByCode(string $code): ?ReferentTag
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function findByCodes(array $codes): array
    {
        return $this->findBy(['code' => $codes]);
    }

    /**
     * @param Zone[] $zones
     */
    public function findByZones(array $zones): array
    {
        $qb = $this->createQueryBuilder('tag');

        return $qb
            ->innerJoin('tag.zone', 'zone')
            ->leftJoin('zone.parents', 'parent')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('zone.id', ':zones'),
                    $qb->expr()->in('parent.id', ':zones'),
                )
            )
            ->setParameter(':zones', $zones)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPartialName(string $name, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('tag')
            ->where('ILIKE(tag.name, :name) = true')
            ->setParameter('name', $name.'%')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }

    public function createSelectSenatorAreaQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('tag')
            ->where('tag.type = :type_dpt OR tag.code = :code')
            ->setParameters([
                'type_dpt' => ReferentTag::TYPE_DEPARTMENT,
                'code' => self::FRENCH_OUTSIDE_FRANCE_TAG,
            ])
        ;
    }
}
