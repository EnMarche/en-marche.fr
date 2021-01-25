<?php

namespace App\Repository;

use App\Entity\District;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DistrictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, District::class);
    }

    /**
     * Finds district by coordinates of the point.
     *
     * @return District[]
     */
    public function findDistrictsByCoordinates($latitude, $longitude): array
    {
        return $this->createQueryBuilder('district')
            ->join('district.referentTag', 'referentTag')
            ->addSelect('referentTag')
            ->join('district.geoData', 'geoData')
            ->where('ST_Within(ST_Point(:longitude, :latitude), geoData.geoShape) = true')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->getQuery()
            ->getResult()
        ;
    }
}
