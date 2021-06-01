<?php

namespace App\Repository\Projection;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Projection\ManagedUser;
use App\Intl\FranceCitiesBundle;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use App\Repository\ReferentTrait;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ManagedUserRepository extends ServiceEntityRepository
{
    use ReferentTrait;
    use PaginatorTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ManagedUser::class);
    }

    /**
     * @return ManagedUser[]|PaginatorInterface
     */
    public function searchByFilter(ManagedUsersFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return $this->configurePaginator(
            $this->createFilterQueryBuilder($filter),
            $page,
            $limit,
            static function (Query $query) {
                $query
                    ->useResultCache(true)
                    ->setResultCacheLifetime(1800)
                ;
            }
        );
    }

    public function getExportQueryBuilder(ManagedUsersFilter $filter): Query
    {
        return $this->createFilterQueryBuilder($filter)->getQuery();
    }

    private function createFilterQueryBuilder(ManagedUsersFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->where('u.status = :status')
            ->setParameter('status', ManagedUser::STATUS_READY)
            ->orderBy('u.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
        ;

        $this->withGeoZones(
            $filter->getZones() ?: $filter->getManagedZones(),
            $qb,
            'u',
            ManagedUser::class,
            'm2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        if ($queryAreaCode = $filter->getCityAsArray()) {
            $areaCodeExpression = $qb->expr()->orX();

            foreach ($queryAreaCode as $key => $areaCode) {
                if (is_numeric($areaCode)) {
                    $areaCodeExpression->add('u.postalCode LIKE :postalCode_'.$key.' OR u.committeePostalCode LIKE :postalCode_'.$key);
                    $qb->setParameter('postalCode_'.$key, $areaCode.'%');
                }

                if (\is_string($areaCode)) {
                    $areaCodeExpression->add('u.country = :countryOrCity_'.$key.' OR u.city = :countryOrCity_'.$key);
                    $qb->setParameter('countryOrCity_'.$key, $areaCode);
                }
            }

            $qb->andWhere($areaCodeExpression);
        }

        if ($gender = $filter->getGender()) {
            $qb
                ->andWhere('u.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('ILIKE(u.lastName, :last_name) = true')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('ILIKE(u.firstName, :first_name) = true')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($ageMin = $filter->getAgeMin()) {
            $qb
                ->andWhere('u.age >= :age_min')
                ->setParameter('age_min', $ageMin)
            ;
        }

        if ($ageMax = $filter->getAgeMax()) {
            $qb
                ->andWhere('u.age <= :age_max')
                ->setParameter('age_max', $ageMax)
            ;
        }

        if ($registeredSince = $filter->getRegisteredSince()) {
            $qb
                ->andWhere('u.createdAt >= :registered_since')
                ->setParameter('registered_since', $registeredSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($registeredUntil = $filter->getRegisteredUntil()) {
            $qb
                ->andWhere('u.createdAt <= :registered_until')
                ->setParameter('registered_until', $registeredUntil->format('Y-m-d 23:59:59'))
            ;
        }

        foreach (array_values($filter->getInterests()) as $key => $interest) {
            $qb
                ->andWhere(":interest_$key = ANY_OF(string_to_array(u.interests, ','))")
                ->setParameter('interest_'.$key, $interest)
            ;
        }

        if ($committee = $filter->getCommittee()) {
            $qb
                ->andWhere(":committee_uuid = ANY_OF(string_to_array(u.committeeUuids, ','))")
                ->setParameter('committee_uuid', $committee->getUuidAsString())
            ;
        }

        $restrictionsExpression = $qb->expr()->orX();

        if ($committees = $filter->getCommitteeUuids()) {
            $committeesExpression = $qb->expr()->orX();

            foreach ($committees as $key => $uuid) {
                $committeesExpression->add(":committee_uuid_$key = ANY_OF(string_to_array(u.committeeUuids, ','))");
                $qb->setParameter("committee_uuid_$key", $uuid);
            }

            $restrictionsExpression->add($committeesExpression);
        }

        if ($cities = $filter->getCities()) {
            $citiesExpression = $qb->expr()->orX();

            foreach ($cities as $key => $inseeCode) {
                $city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCode);
                $postalCode = $city ? $city['postal_code'] : null;

                if (!$postalCode) {
                    continue;
                }

                $cityExpression = $qb->expr()->andX(
                    'u.postalCode = :city_postalCode_'.$key,
                    'u.country = :country_france'
                );
                $qb->setParameter('city_postalCode_'.$key, $postalCode);
                $qb->setParameter('country_france', 'FR');

                $citiesExpression->add($cityExpression);
            }

            $restrictionsExpression->add($citiesExpression);
        }

        if ($restrictionsExpression->count()) {
            $qb->andWhere($restrictionsExpression);
        }

        if (null !== $filter->isCommitteeMember()) {
            $qb
                ->andWhere('u.isCommitteeMember = :is_committee_membre')
                ->setParameter('is_committee_membre', $filter->isCommitteeMember())
            ;
        }

        $typeExpression = $qb->expr()->orX();

        // includes
        if (true === $filter->includeCommitteeHosts()) {
            $typeExpression->add('u.isCommitteeHost = true');
        }

        if (true === $filter->includeCommitteeSupervisors()) {
            $typeExpression->add('u.isCommitteeSupervisor = true');
        }

        if (true === $filter->includeCommitteeProvisionalSupervisors()) {
            $typeExpression->add('u.isCommitteeProvisionalSupervisor = true');
        }

        if (true === $filter->includeCitizenProjectHosts()) {
            $typeExpression->add('u.citizenProjectsOrganizer IS NOT NULL');
        }

        $qb->andWhere($typeExpression);

        // excludes
        if (false === $filter->includeCommitteeHosts()) {
            $qb->andWhere('u.isCommitteeHost = false');
        }

        if (false === $filter->includeCommitteeSupervisors()) {
            $qb->andWhere('u.isCommitteeSupervisor = false');
        }

        if (false === $filter->includeCommitteeProvisionalSupervisors()) {
            $qb->andWhere('u.isCommitteeProvisionalSupervisor = false');
        }

        if (false === $filter->includeCitizenProjectHosts()) {
            $qb->andWhere('u.citizenProjectsOrganizer IS NULL');
        }

        if (null !== $filter->getEmailSubscription() && $filter->getSubscriptionType()) {
            $subscriptionTypesCondition = ":subscription_type = ANY_OF(string_to_array(u.subscriptionTypes, ','))";
            if (false === $filter->getEmailSubscription()) {
                $subscriptionTypesCondition = "u.subscriptionTypes IS NULL OR NOT(:subscription_type = ANY_OF(string_to_array(u.subscriptionTypes, ',')))";
            }

            $qb
                ->andWhere($subscriptionTypesCondition)
                ->setParameter('subscription_type', $filter->getSubscriptionType())
            ;
        }

        if (null !== $filter->getSmsSubscription()) {
            $subscriptionTypesCondition = ":sms_subscription_type = ANY_OF(string_to_array(u.subscriptionTypes, ','))";
            if (false === $filter->getSmsSubscription()) {
                $subscriptionTypesCondition = "u.subscriptionTypes IS NULL OR NOT(:sms_subscription_type = ANY_OF(string_to_array(u.subscriptionTypes, ',')))";
            }

            $qb
                ->andWhere($subscriptionTypesCondition)
                ->setParameter('sms_subscription_type', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
            ;
        }

        if (null !== $filter->getVoteInCommittee()) {
            $qb->andWhere(sprintf('u.voteCommitteeId %s NULL', $filter->getVoteInCommittee() ? 'IS NOT' : 'IS'));
        }

        if (null !== $filter->getIsCertified()) {
            $qb->andWhere(sprintf('u.certifiedAt %s NULL', $filter->getIsCertified() ? 'IS NOT' : 'IS'));
        }

        return $qb;
    }

    public function countManagedUsers(array $zones = []): int
    {
        if (empty($zones)) {
            throw new \InvalidArgumentException('Zones could not be empty');
        }

        $qb = $this
            ->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->where('u.status = :status')
            ->setParameter('status', ManagedUser::STATUS_READY)
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'u',
            ManagedUser::class,
            'm2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
