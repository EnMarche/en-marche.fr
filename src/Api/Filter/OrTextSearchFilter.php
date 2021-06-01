<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class OrTextSearchFilter extends AbstractContextAwareFilter
{
    public const PROPERTY_SUFFIX = '_contains';
    public const IGNORED_WORDS = ['avec', 'après', 'avant', 'depuis', 'jusque', 'jusqu’à', 'jusqu\'à', 'pendant', 'à côté',
        'à droite', 'à gauche', 'au-delà', 'au-dessous', 'au-dessus', 'à travers', 'derrière', 'en dehors', 'contre',
        'd\'après', 'd’après', 'en face', 'chez', 'hors', 'loin', 'par', 'près', 'sous', 'sur', 'vers', 'grâce à',
        'malgré', 'pour', 'contre', 'entre', ' sauf', 'excepté', 'par', 'parmi', 'sans', 'selon', 'dans', 'en',
        'le', 'la', 'les', 'au', 'aux', 'un', 'une', 'dès', 'des', 'du', 'de', 'a', 'à', ];

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        $property = str_replace(self::PROPERTY_SUFFIX, '', $property);

        // to be sure that filter is applied only to an existing string field
        if (!\array_key_exists($property, $this->properties)
            || !$this->getClassMetadata($resourceClass)->hasField($property)
            || 'string' !== $this->getClassMetadata($resourceClass)->getTypeOfField($property)) {
            return;
        }

        $value = str_replace(array_map(function (string $word) {
            return ' '.$word.' ';
        }, self::IGNORED_WORDS), ' ', ' '.$value.' ');
        $values = array_filter(
            explode(' ', preg_replace('/[^0-9a-zÀ-ÖÙ-öù-ÿœ\s]/iu', ' ', $value)),
            function ($word) {
                return $word && \strlen($word) > 2;
            }
        );
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $searchTextExpression = $queryBuilder->expr()->orX();

        foreach ($values as $key => $text) {
            $searchTextExpression->add(sprintf('ILIKE(%s.%s, :value_%s) = true', $rootAlias, $property, $key));
            $queryBuilder->setParameter("value_$key", "%$text%");
        }

        $queryBuilder->andWhere($searchTextExpression);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description[$property.self::PROPERTY_SUFFIX] = [
                'property' => $property.self::PROPERTY_SUFFIX,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by containing one of the words of searching string.',
                    'name' => $property.self::PROPERTY_SUFFIX,
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }
}
