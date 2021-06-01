<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEvent;
use App\Repository\Event\BaseEventRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;

final class EventsZipCodeFilter extends AbstractContextAwareFilter
{
    /**
     * @var BaseEventRepository
     */
    private $baseEventRepository;

    /**
     * @var ZoneRepository
     */
    private $zoneRepository;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if (
            !is_a($resourceClass, BaseEvent::class, true)
            || 'zipCode' !== $property
            || empty($value)
        ) {
            return;
        }

        $zone = $this->zoneRepository->findRegionByPostalCode($value);
        if (null === $zone) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $this->baseEventRepository->withGeoZones(
            [$zone],
            $queryBuilder,
            $rootAlias,
            $resourceClass,
            'e2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.published = true', $entityClassAlias));
            }
        );
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description['zipCode'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by zipCode.',
                    'name' => 'zipCode',
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }

    /**
     * @required
     */
    public function setBaseEventRepository(BaseEventRepository $baseEventRepository): void
    {
        $this->baseEventRepository = $baseEventRepository;
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
