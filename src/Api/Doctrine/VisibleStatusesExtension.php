<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\VisibleStatusesInterface;
use Doctrine\ORM\QueryBuilder;

class VisibleStatusesExtension implements ContextAwareQueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (is_a($resourceClass, VisibleStatusesInterface::class, true)) {
            $queryBuilder
                ->andWhere(sprintf('%s.status IN (:statuses)', $queryBuilder->getRootAliases()[0]))
                ->setParameter('statuses', $resourceClass::getVisibleStatuses())
            ;
        }
    }
}
