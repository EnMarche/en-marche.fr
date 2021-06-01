<?php

namespace App\Repository\ElectedRepresentative;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\ElectedRepresentativeTypeEnum;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\Geo\Zone;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ElectedRepresentativeRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedRepresentative::class);
    }

    public function findByEmail(string $email): array
    {
        return $this
            ->createQueryBuilder('elected_representative')
            ->leftJoin('elected_representative.adherent', 'adherent')
            ->andWhere((new Orx())
                ->add('adherent.emailAddress = :email')
                ->add('elected_representative.contactEmail = :email')
            )
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ElectedRepresentative[]|PaginatorInterface
     */
    public function searchByFilter(ListFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return $this->configurePaginator($this->createFilterQueryBuilder($filter), $page, $limit);
    }

    /**
     * @param Zone[] $zones
     */
    public function countForZones(array $zones): int
    {
        $qb = $this
            ->createQueryBuilder('er')
            ->select('COUNT(DISTINCT er.id)')
        ;
        $this->withActiveMandatesCondition($qb);

        if ($zones) {
            $this->withZoneCondition($qb, $zones);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function isInReferentManagedArea(ElectedRepresentative $electedRepresentative, array $zones): bool
    {
        $qb = $this->createQueryBuilder('er');

        $this->withActiveMandatesCondition($qb);

        $res = $this
            ->withZoneCondition($qb, $zones)
            ->andWhere('er = :electedRepresentative')
            ->setParameter('electedRepresentative', $electedRepresentative)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return null !== $res;
    }

    public function createWithEmailQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('elected_representative')
            ->andWhere((new Orx())
                ->add('elected_representative.adherent IS NOT NULL')
                ->add('elected_representative.contactEmail IS NOT NULL')
            )
        ;
    }

    private function createFilterQueryBuilder(ListFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('er')
        ;

        $this->withActiveMandatesCondition($qb);

        $zones = $filter->getZones() ?: $filter->getManagedZones();
        if ($zones) {
            $this->withZoneCondition($qb, $zones);
        }

        if ($filter->getManagedZones()) {
            $qb
                ->orderBy('er.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
                ->addOrderBy('mandate.number', 'ASC')
            ;
        }

        $qb
            ->addSelect('mandate', 'zone', 'politicalFunction', 'userListDefinition', 'label')
            ->addSelect('sponsorship', 'socialNetworkLink', 'userListDefinition')
            ->leftJoin('er.labels', 'label')
            ->leftJoin('er.sponsorships', 'sponsorship')
            ->leftJoin('er.socialNetworkLinks', 'socialNetworkLink')
            ->leftJoin('er.politicalFunctions', 'politicalFunction')
            ->leftJoin('er.userListDefinitions', 'userListDefinition')
        ;

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('ILIKE(er.lastName, :last_name) = true')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('ILIKE(er.firstName, :first_name) = true')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($gender = $filter->getGender()) {
            switch ($gender) {
                case Genders::FEMALE:
                case Genders::MALE:
                    $qb
                        ->andWhere('er.gender = :gender')
                        ->setParameter('gender', $gender)
                    ;

                    break;
                case Genders::UNKNOWN:
                    $qb->andWhere('er.gender IS NULL');

                    break;
                default:
                    break;
            }
        }

        if ($labels = $filter->getLabels()) {
            $qb
                ->andWhere('label.name in (:labels)')
                ->andWhere('label.onGoing = true')
                ->andWhere('label.finishYear IS NULL')
                ->setParameter('labels', $labels)
            ;
        }

        if ($mandates = $filter->getMandates()) {
            $qb
                ->andWhere('mandate.type in (:mandates)')
                ->setParameter('mandates', $mandates)
            ;
        }

        if ($politicalFunctions = $filter->getPoliticalFunctions()) {
            $qb
                ->andWhere('politicalFunction.name in (:politicalFunctions)')
                ->andWhere('politicalFunction.onGoing = true')
                ->andWhere('politicalFunction.finishAt IS NULL')
                ->setParameter('politicalFunctions', $politicalFunctions)
            ;
        }

        if ($userListDefinitions = $filter->getUserListDefinitions()) {
            $qb
                ->andWhere('userListDefinition.id in (:userListDefinitions)')
                ->setParameter('userListDefinitions', $userListDefinitions)
            ;
        }

        if ($contactType = $filter->getContactType()) {
            switch ($contactType) {
                case ElectedRepresentativeTypeEnum::ADHERENT:
                    $qb->andWhere('er.adherent IS NOT NULL');

                    break;
                case ElectedRepresentativeTypeEnum::CONTACT:
                    $qb
                        ->andWhere('er.adherent IS NULL')
                        ->andWhere('er.contactEmail IS NOT NULL')
                    ;

                    break;
                case ElectedRepresentativeTypeEnum::OTHER:
                    $qb
                        ->andWhere('er.adherent IS NULL')
                        ->andWhere('er.contactEmail IS NULL')
                    ;

                    break;
                default:
                    throw new \InvalidArgumentException("ElectedRepresentative contactType \"$contactType\" is undefined.");
            }
        }

        $emailSubscribed = $filter->isEmailSubscribed();
        if (null !== $emailSubscribed) {
            $qb
                ->andWhere('er.emailUnsubscribed = :email_unsubscribed')
                ->setParameter('email_unsubscribed', !$emailSubscribed)
            ;
        }

        return $qb;
    }

    private function withActiveMandatesCondition(QueryBuilder $qb, string $alias = 'er'): QueryBuilder
    {
        return $qb
            ->leftJoin($alias.'.mandates', 'mandate')
            ->leftJoin('mandate.zone', 'zone')
            ->andWhere('mandate.finishAt IS NULL')
            ->andWhere('mandate.onGoing = true')
            ->andWhere('mandate.isElected = true')
        ;
    }

    private function withZoneCondition(QueryBuilder $qb, array $zones, string $alias = 'er'): QueryBuilder
    {
        if (!$zones) {
            return $qb;
        }

        if (!\in_array('mandate', $qb->getAllAliases(), true)) {
            $qb->leftJoin($alias.'.mandates', 'mandate');
        }

        if (!\in_array('geo_zone', $qb->getAllAliases(), true)) {
            $qb->innerJoin('mandate.geoZone', 'geo_zone');
        }

        if (!\in_array('geo_zone_parent', $qb->getAllAliases(), true)) {
            $qb->innerJoin('geo_zone.parents', 'geo_zone_parent');
        }

        $ids = array_map(static function ($zone) {
            return $zone->getId();
        }, $zones);

        return $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->in('geo_zone.id', $ids),
                $qb->expr()->in('geo_zone_parent.id', $ids),
            )
        );
    }

    public function hasActiveParliamentaryMandate(Adherent $adherent): bool
    {
        return 0 < (int) $this->createQueryBuilder('e')
            ->select('COUNT(1)')
            ->innerJoin('e.mandates', 'm')
            ->where('m.onGoing = :true AND m.isElected = :true AND m.finishAt IS NULL')
            ->andWhere('m.type IN (:types)')
            ->andWhere('e.adherent = :adherent')
            ->setParameters([
                'true' => true,
                'adherent' => $adherent,
                'types' => [
                    MandateTypeEnum::SENATOR,
                    MandateTypeEnum::DEPUTY,
                    MandateTypeEnum::EURO_DEPUTY,
                ],
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
