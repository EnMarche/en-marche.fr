<?php

namespace App\Repository;

use App\Entity\AssessorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractAssessorRepository extends ServiceEntityRepository
{
    protected static function addAndWhereAssessorRequestLocation(
        QueryBuilder $qb,
        AssessorRequest $assessorRequest,
        string $alias
    ): QueryBuilder {
        if ($assessorRequest->isFrenchAssessorRequest()) {
            return $qb
                ->andWhere(":postalCode = ANY_OF(string_to_array($alias.postalCode, ','))")
                ->setParameter('postalCode', $assessorRequest->getAssessorPostalCode())
                ->andWhere('vp.city = :city')
                ->setParameter('city', $assessorRequest->getAssessorCity())
            ;
        }

        return $qb
            ->andWhere("$alias.country = :countryCode")
            ->setParameter('countryCode', $assessorRequest->getAssessorCountry())
        ;
    }
}
