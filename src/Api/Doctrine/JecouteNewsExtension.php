<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Jecoute\News;
use Doctrine\ORM\QueryBuilder;

class JecouteNewsExtension implements ContextAwareQueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (News::class === $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%1$s.published = true AND %1$s.createdAt >= :date', $queryBuilder->getRootAliases()[0]))
                ->setParameter('date', (new \DateTime('-60 days'))->setTime(0, 0))
            ;
        }
    }
}
