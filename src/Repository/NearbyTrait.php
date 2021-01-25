<?php

namespace App\Repository;

use App\Geocoder\Coordinates;
use Doctrine\ORM\QueryBuilder;

trait NearbyTrait
{
    public function getNearbyExpression(): string
    {
        return <<<'SQL'
          ST_DistanceSphere(
            ST_Point(n.postAddress.longitude, n.postAddress.latitude),
            ST_Point(:longitude, :latitude)
          )
SQL;
    }

    /**
     * Calculates the distance (in Km) between the subject entity and the provided geographical
     * points in a select statement. You can use this template to apply your constraints
     * by using the 'distance_between' attribute.
     *
     * Setting the hidden flag to false allow you to get an array as result containing
     * the entity and the calculated distance.
     *
     * @return QueryBuilder
     */
    public function createNearbyQueryBuilder(Coordinates $coordinates, bool $hidden = true)
    {
        $hidden = $hidden ? 'hidden' : '';

        return $this
            ->createQueryBuilder('n')
            ->addSelect($this->getNearbyExpression().' as '.$hidden.' distance_between')
            ->setParameter('latitude', $coordinates->getLatitude())
            ->setParameter('longitude', $coordinates->getLongitude())
            ->where('n.postAddress.latitude IS NOT NULL')
            ->andWhere('n.postAddress.longitude IS NOT NULL')
            ->orderBy('distance_between', 'asc')
        ;
    }
}
