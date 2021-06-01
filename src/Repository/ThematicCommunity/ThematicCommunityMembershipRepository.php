<?php

namespace App\Repository\ThematicCommunity;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\CitizenProjectMembership;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Repository\PaginatorTrait;
use App\Subscription\SubscriptionTypeEnum;
use App\ThematicCommunity\ThematicCommunityMembershipFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ThematicCommunityMembershipRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThematicCommunityMembership::class);
    }

    public function searchByFilter(
        ThematicCommunityMembershipFilter $filter,
        int $page = 1,
        int $limit = 30
    ): PaginatorInterface {
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

    private function createFilterQueryBuilder(ThematicCommunityMembershipFilter $filter): QueryBuilder
    {
        $sort = $filter->getSort();
        $order = 'd' === $filter->getOrder() ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('tcm')
            ->leftJoin('tcm.community', 'tc')
            ->leftJoin('tcm.adherent', 'a')
            ->leftJoin('tcm.contact', 'c')
            ->where('tc IN (:communities)')
            ->setParameter('communities', $filter->getThematicCommunities())
        ;

        if ('lastName' === $sort) {
            $qb
                ->addSelect('CASE WHEN a.lastName IS NULL THEN c.lastName ELSE a.lastName END as HIDDEN name')
                ->orderBy('name', $order)
            ;
        } else {
            $qb->addOrderBy('tcm.joinedAt', $order);
        }

        if ($role = $filter->getRole()) {
            switch ($role) {
                case 'contact':
                    $qb->andWhere('c IS NOT NULL');
                    break;
                case 'referent':
                    $qb->innerJoin('a.managedArea', 'area');
                    break;
                case 'supervisor':
                case 'provisional_supervisor':
                    $qb->innerJoin('a.adherentMandates', 'am')
                        ->andWhere('am.committee IS NOT NULL AND am.quality = :supervisor AND am.finishAt IS NULL AND am.provisional = :provisional')
                        ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
                        ->setParameter('provisional', 'supervisor' === $role ? false : true)
                    ;
                    break;
                case 'citizen_project_admin':
                    $qb->innerJoin('a.citizenProjectMemberships', 'cpm')
                        ->andWhere('cpm.privilege = :administrator')
                        ->setParameter('administrator', CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR)
                    ;
                    break;
                case 'adherent':
                    $qb->andWhere('a IS NOT NULL');
                    break;
            }
        }

        if (($categories = $filter->getCategories()) && $categories->count()) {
            $qb
                ->innerJoin('tcm.userListDefinitions', 'uld')
                ->andWhere('uld IN (:categories)')
                ->setParameter('categories', $categories)
            ;
        }

        if ($gender = $filter->getGender()) {
            $qb
                ->andWhere('a.gender = :gender OR c.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('LOWER(a.lastName) LIKE :last_name OR LOWER(c.lastName) LIKE :last_name')
                ->setParameter('last_name', '%'.mb_strtolower($lastName).'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('LOWER(a.firstName) LIKE :first_name OR LOWER(c.firstName) LIKE :first_name')
                ->setParameter('first_name', '%'.mb_strtolower($firstName).'%')
            ;
        }

        if ($ageMin = $filter->getAgeMin()) {
            $qb
                ->andWhere('a.birthdate <= :age_min OR c.birthDate <= :age_min')
                ->setParameter('age_min', new \DateTime('-'.$ageMin.' years'))
            ;
        }

        if ($ageMax = $filter->getAgeMax()) {
            $qb
                ->andWhere('a.birthdate >= :age_max OR c.birthDate >= :age_max')
                ->setParameter('age_max', new \DateTime('-'.$ageMax.' years'))
            ;
        }

        if ($joinedSince = $filter->getJoinedSince()) {
            $qb
                ->andWhere('tcm.joinedAt >= :joined_since')
                ->setParameter('joined_since', $joinedSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($joinedUntil = $filter->getJoinedUntil()) {
            $qb
                ->andWhere('tcm.joinedAt <= :joined_until')
                ->setParameter('joined_until', $joinedUntil->format('Y-m-d 23:59:59'))
            ;
        }

        if ($queryAreaCode = $filter->getCityAsArray()) {
            $areaCodeExpression = $qb->expr()->orX();

            foreach ($queryAreaCode as $key => $areaCode) {
                if (is_numeric($areaCode)) {
                    $areaCodeExpression->add('(a.postAddress.postalCode LIKE :postalCode_'.$key.') OR (c.postAddress.postalCode LIKE :postalCode_'.$key.')');
                    $qb->setParameter('postalCode_'.$key, $areaCode.'%');
                }

                if (\is_string($areaCode)) {
                    $areaCodeExpression->add('a.postAddress.cityName = :city_'.$key.' OR c.postAddress.cityName = :city_'.$key);
                    $qb->setParameter('city_'.$key, $areaCode);
                }
            }

            $qb->andWhere($areaCodeExpression);
        }

        if ($country = $filter->getCountry()) {
            $qb
                ->andWhere('a.postAddress.country = :country OR c.postAddress.country = :country')
                ->setParameter('country', $country)
            ;
        }

        if (null !== $filter->isSmsSubscription()) {
            if (true === $filter->isSmsSubscription()) {
                $qb
                    ->innerJoin('a.subscriptionTypes', 'subSMS')
                    ->andWhere('subSMS.code = :sms_subscription')
                    ->setParameter('sms_subscription', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
                ;
            } else {
                $subQb = $this->createQueryBuilder('stcm')
                    ->innerJoin('stcm.adherent', 'sa')
                    ->innerJoin('sa.subscriptionTypes', 'subSMS')
                    ->andWhere('subSMS.code = :sms_subscription')
                ;

                $or = $qb->expr()->orX(
                    $qb->expr()->not($qb->expr()->exists($subQb)),
                    // for now Contact does not have subscription, so add them where searching for non subscribed
                    'tcm.contact is not null'
                );

                $qb
                    ->andWhere($or)
                    ->setParameter('sms_subscription', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
                ;
            }
        }

        if (null !== $filter->isEmailSubscription()) {
            if (true === $filter->isEmailSubscription()) {
                $qb->innerJoin('a.subscriptionTypes', 'subEmail')
                    ->andWhere('subEmail.code = :email_subscription')
                    ->setParameter('email_subscription', SubscriptionTypeEnum::THEMATIC_COMMUNITY_EMAIL)
                ;
            } else {
                $subQb = $this->createQueryBuilder('stcm')
                    ->innerJoin('stcm.adherent', 'sa')
                    ->innerJoin('sa.subscriptionTypes', 'subEmail')
                    ->andWhere('subEmail.code = :email_subscription')
                ;

                $or = $qb->expr()->orX(
                    $qb->expr()->not($qb->expr()->exists($subQb)),
                    // for now Contact does not have subscription, so add them where searching for non subscribed
                    'tcm.contact is not null'
                );

                $qb
                    ->andWhere($or)
                    ->setParameter('email_subscription', SubscriptionTypeEnum::THEMATIC_COMMUNITY_EMAIL)
                ;
            }
        }

        if ($motivations = $filter->getMotivations()) {
            $or = $qb->expr()->orX();
            foreach ($motivations as $i => $motivation) {
                $or->add(":motivation_$i = ANY_OF(string_to_array(tcm.motivations, ','))");
                $qb->setParameter("motivation_$i", $motivation);
            }
            $qb->andWhere($or);
        }

        if (null !== $filter->isExpert()) {
            $qb->andWhere('tcm.expert = :expert')
                ->setParameter('expert', $filter->isExpert())
            ;
        }

        if (null !== ($withJob = $filter->isWithJob())) {
            if (true === $withJob) {
                $qb->andWhere('a.job IS NOT NULL OR c.job IS NOT NULL');
            } else {
                $qb->andWhere('a.job IS NULL AND c.job IS NULL');
            }
        }

        if ($job = $filter->getJob()) {
            $qb->andWhere('a.job IN (:job) OR c.job IN (:job)')
                ->setParameter('job', $job)
            ;
        }

        if (null !== ($withAssociation = $filter->isWithAssociation())) {
            $qb->andWhere('tcm.association = :with_association')
                ->setParameter('with_association', $withAssociation)
            ;
        }

        return $qb;
    }

    public function countMembershipsInCommunities(array $thematicCommunities): int
    {
        return (int) $this->createQueryBuilder('tcm')
            ->select('COUNT(tcm)')
            ->leftJoin('tcm.community', 'tc')
            ->leftJoin('tcm.adherent', 'a')
            ->leftJoin('tcm.contact', 'c')
            ->where('tc IN (:communities)')
            ->setParameter('communities', $thematicCommunities)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findAdherentMemberships(Adherent $adherent): array
    {
        return $this->createQueryBuilder('tcm')
            ->where('tcm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function isEmailContactAlreadyRegisteredOnCommunity(
        ThematicCommunity $thematicCommunity,
        string $email
    ): bool {
        return null !== $this->createQueryBuilder('tcm')
            ->innerJoin('tcm.contact', 'c')
            ->where('tcm.community = :community')
            ->andWhere('c.email = :email')
            ->setParameter('community', $thematicCommunity)
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
