<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\City;
use App\Entity\MunicipalManagerRoleAssociation;
use App\MunicipalManager\Filter\AssociationCityFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CityRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;
    use PaginatorTrait;

    private const ALIAS = 'city';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * @return City[]|PaginatorInterface
     */
    public function findAllForFilter(AssociationCityFilter $filter, int $page, int $limit = 30): PaginatorInterface
    {
        $qb = $this
            ->createQueryBuilder(self::ALIAS)
            ->innerJoin(self::ALIAS.'.department', 'department')
            ->innerJoin('department.region', 'region')
        ;

        if ($managedTags = $filter->getManagedTags()) {
            $this->applyGeoFilter($qb, $managedTags, self::ALIAS, 'region.country', self::ALIAS.'.inseeCode');
        }

        if ($managedInseeCode = $filter->getManagedInseeCode()) {
            $qb
                ->andWhere(self::ALIAS.'.inseeCode = :managed_insee_code')
                ->setParameter('managed_insee_code', $managedInseeCode)
            ;
        }

        if ($name = $filter->getName()) {
            $qb
                ->andWhere('ILIKE('.self::ALIAS.'.name, :name) = true')
                ->setParameter('name', sprintf('%%%s%%', $name))
            ;
        }

        if ($inseeCode = $filter->getInseeCode()) {
            $qb
                ->andWhere(self::ALIAS.'.inseeCode LIKE :insee_code')
                ->setParameter('insee_code', sprintf('%s%%', $inseeCode))
            ;
        }

        $municipalManagerFirstName = $filter->getMunicipalManagerFirstName();
        $municipalManagerLastName = $filter->getMunicipalManagerLastName();
        $municipalManagerEmail = $filter->getMunicipalManagerEmail();

        if ($municipalManagerFirstName || $municipalManagerLastName || $municipalManagerEmail) {
            $qb
                ->leftJoin(
                    MunicipalManagerRoleAssociation::class,
                    'municipal_manager_role',
                    Join::WITH,
                    self::ALIAS.' MEMBER OF municipal_manager_role.cities'
                )
                ->leftJoin(
                    Adherent::class,
                    'municipal_manager',
                    Join::WITH,
                    'municipal_manager.municipalManagerRole = municipal_manager_role'
                )
            ;

            if ($municipalManagerFirstName) {
                $qb
                    ->andWhere('ILIKE(municipal_manager.firstName, :municipal_manager_first_name) = true')
                    ->setParameter('municipal_manager_first_name', sprintf('%%%s%%', $municipalManagerFirstName))
                ;
            }

            if ($municipalManagerLastName) {
                $qb
                    ->andWhere('ILIKE(municipal_manager.lastName, :municipal_manager_last_name) = true')
                    ->setParameter('municipal_manager_last_name', sprintf('%%%s%%', $municipalManagerLastName))
                ;
            }

            if ($municipalManagerEmail) {
                $qb
                    ->andWhere(' ILIKE(municipal_manager.emailAddress, :municipal_manager_email) = true')
                    ->setParameter('municipal_manager_email', sprintf('%%%s%%', $municipalManagerEmail))
                ;
            }
        }

        $qb->orderBy(self::ALIAS.'.inseeCode', 'ASC');

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function findByInseeCode(string $inseeCode): ?City
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.inseeCode = :insee_code')
            ->setParameter('insee_code', $inseeCode)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
