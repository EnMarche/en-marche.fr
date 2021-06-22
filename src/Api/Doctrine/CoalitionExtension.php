<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\Coalition;
use Doctrine\ORM\QueryBuilder;

class CoalitionExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (Coalition::class === $resourceClass) {
            $queryBuilder
                ->andWhere("${alias}.enabled = :true")
                ->setParameter('true', true)
            ;
        } elseif (Cause::class === $resourceClass) {
            $queryBuilder
                ->innerJoin("${alias}.coalition", 'coalition')
                ->leftJoin("${alias}.secondCoalition", 'coalition_2')
                ->andWhere('coalition.enabled = :true AND (coalition_2 IS NULL OR coalition_2.enabled = :true)')
                ->setParameter('true', true)
            ;
        }
    }
}
