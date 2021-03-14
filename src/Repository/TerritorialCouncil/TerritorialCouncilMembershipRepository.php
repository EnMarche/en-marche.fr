<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\TerritorialCouncil\Candidacy\SearchAvailableMembershipFilter;
use App\TerritorialCouncil\Filter\MembersListFilter;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TerritorialCouncilMembershipRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TerritorialCouncilMembership::class);
    }

    /**
     * @return TerritorialCouncilMembership[]
     */
    public function findAvailableMemberships(Candidacy $candidacy, SearchAvailableMembershipFilter $filter): array
    {
        $membership = $candidacy->getMembership();

        $qb = $this
            ->createQueryBuilder('membership')
            ->addSelect('adherent', 'quality')
            ->innerJoin('membership.qualities', 'quality')
            ->innerJoin('membership.adherent', 'adherent')
            ->leftJoin('membership.candidacies', 'candidacy', Join::WITH, 'candidacy.membership = membership AND candidacy.election = :election')
            ->where('membership.territorialCouncil = :council')
            ->andWhere('candidacy IS NULL OR candidacy.status = :candidacy_draft_status')
            ->andWhere('membership.id != :membership_id')
            ->andWhere(sprintf('membership.id NOT IN (%s)',
                $this->createQueryBuilder('t1')
                    ->select('t1.id')
                    ->innerJoin('t1.qualities', 't2')
                    ->where('t1.territorialCouncil = :council')
                    ->andWhere('t2.name IN (:forbidden_qualities)')
                    ->getDQL()
            ))
            ->andWhere('adherent.status = :adherent_status')
            ->setParameters([
                'candidacy_draft_status' => CandidacyInterface::STATUS_DRAFT,
                'election' => $candidacy->getElection(),
                'council' => $coTerr = $membership->getTerritorialCouncil(),
                'membership_id' => $membership->getId(),
                'forbidden_qualities' => TerritorialCouncilQualityEnum::FORBIDDEN_TO_CANDIDATE,
                'adherent_status' => Adherent::ENABLED,
            ])
            ->orderBy('adherent.lastName')
            ->addOrderBy('adherent.firstName')
        ;

        if (DesignationTypeEnum::COPOL === $candidacy->getElection()->getDesignationType()) {
            $qb
                ->andWhere('adherent.gender = :gender AND quality.name = :quality')
                ->setParameter('quality', $filter->getQuality())
                ->setParameter('gender', $candidacy->isFemale() ? Genders::MALE : Genders::FEMALE)
            ;
        } else {
            if (($president = $coTerr->getMemberships()->getPresident()) && $president->getAdherent()->getGender() === $candidacy->getGender()) {
                $qb
                    ->andWhere('adherent.gender = :gender')
                    ->setParameter('gender', $candidacy->isFemale() ? Genders::MALE : Genders::FEMALE)
                ;
            }

            $qb
                ->andWhere('quality.name IN (:qualities)')
                ->setParameter('qualities', array_diff(TerritorialCouncilQualityEnum::ABLE_TO_CANDIDATE, [$filter->getQuality()]))
            ;
        }

        if ($filter->getQuery()) {
            $qb
                ->andWhere('(adherent.firstName LIKE :query OR adherent.lastName LIKE :query)')
                ->setParameter('query', sprintf('%s%%', $filter->getQuery()))
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function searchByFilter(MembersListFilter $filter, int $page = 1, ?int $limit = 50): iterable
    {
        if ($limit) {
            return $this->configurePaginator($this->createFilterQueryBuilder($filter), $page, $limit);
        }

        return $this->createFilterQueryBuilder($filter)->getQuery()->getResult();
    }

    public function countForReferentTags(array $referentTags): int
    {
        $qb = $this
            ->createQueryBuilder('tcm')
            ->select('COUNT(1)')
            ->innerJoin('tcm.territorialCouncil', 'territorial_council')
        ;

        return (int) $this
            ->bindReferentTagsCondition($qb, $referentTags)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getForExport(MembersListFilter $filter): array
    {
        return $this->createFilterQueryBuilder($filter)->getQuery()->getResult();
    }

    private function bindReferentTagsCondition(QueryBuilder $qb, array $referentTags): QueryBuilder
    {
        if (!$referentTags) {
            return $qb->andWhere('1 = 0');
        }

        $tagCondition = 'referentTag IN (:tags)';
        foreach ($referentTags as $referentTag) {
            if ('75' === $referentTag->getCode()) {
                $tagCondition = "(referentTag IN (:tags) OR referentTag.name LIKE '%Paris%')";

                break;
            }
        }

        return $qb
            ->innerJoin('territorial_council.referentTags', 'referentTag')
            ->andWhere($tagCondition)
            ->setParameter('tags', $referentTags)
        ;
    }

    private function createFilterQueryBuilder(MembersListFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('tcm')
            ->addSelect('territorial_council', 'mandate', 'subscription_type', 'adherent')
            ->innerJoin('tcm.adherent', 'adherent')
            ->innerJoin('tcm.territorialCouncil', 'territorial_council')
            ->leftJoin('tcm.qualities', 'quality')
            ->leftJoin('adherent.subscriptionTypes', 'subscription_type')
            ->leftJoin('adherent.adherentMandates', 'mandate')
        ;

        if ($filter->getReferentTags() || (!$filter->getTerritorialCouncil() && !$filter->getPoliticalCommittee())) {
            $this->bindReferentTagsCondition($qb, $filter->getReferentTags());
        }

        if (false !== \strpos($filter->getSort(), '.')) {
            $sort = $filter->getSort();
        } else {
            $sort = 'tcm.'.$filter->getSort();
        }

        $qb->orderBy($sort, 'd' === $filter->getOrder() ? 'DESC' : 'ASC');

        if ($filter->getTerritorialCouncil()) {
            $qb
                ->andWhere('territorial_council = :territorial_council')
                ->setParameter('territorial_council', $filter->getTerritorialCouncil())
            ;
        }

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('adherent.lastName LIKE :last_name')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('adherent.firstName LIKE :first_name')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($gender = $filter->getGender()) {
            switch ($gender) {
                case Genders::FEMALE:
                case Genders::MALE:
                    $qb
                        ->andWhere('adherent.gender = :gender')
                        ->setParameter('gender', $gender)
                    ;

                    break;
                case Genders::UNKNOWN:
                    $qb->andWhere('adherent.gender IS NULL');

                    break;
                default:
                    break;
            }
        }

        if ($ageMin = $filter->getAgeMin()) {
            $now = new \DateTimeImmutable();
            $qb
                ->andWhere('adherent.birthdate <= :min_age_birth_date')
                ->setParameter('min_age_birth_date', $now->sub(new \DateInterval(sprintf('P%dY', $ageMin))))
            ;
        }

        if ($ageMax = $filter->getAgeMax()) {
            $now = new \DateTimeImmutable();
            $qb
                ->andWhere('adherent.birthdate >= :max_age_birth_date')
                ->setParameter('max_age_birth_date', $now->sub(new \DateInterval(sprintf('P%dY', $ageMax))))
            ;
        }

        if ($qualities = $filter->getQualities()) {
            $pcQualities = [];
            $tcQualities = [];
            array_walk($qualities, function (string $quality, $key) use (&$pcQualities, &$tcQualities) {
                if (0 === mb_strpos($quality, 'PC_')) {
                    $pcQualities[] = str_replace('PC_', '', $quality);
                } else {
                    $tcQualities[] = $quality;
                }
            });

            if ($pcQualities) {
                $qb
                    ->leftJoin('adherent.politicalCommitteeMembership', 'pcm')
                    ->leftJoin('pcm.qualities', 'pcQuality')
                    ->andWhere('(quality.name in (:qualities) OR pcQuality.name IN (:pcQualities))')
                    ->setParameter('qualities', $tcQualities)
                    ->setParameter('pcQualities', $pcQualities)
                ;
            } else {
                $qb
                    ->andWhere('quality.name in (:qualities)')
                    ->setParameter('qualities', $tcQualities)
                ;
            }
        }

        if ($cities = $filter->getCities()) {
            $cities = \array_map(function (Zone $city) {
                return $city->getName();
            }, $cities);
            $qb
                ->andWhere('quality.zone in (:cities)')
                ->setParameter('cities', $cities)
            ;
        }

        if ($committees = $filter->getCommittees()) {
            $committees = \array_map(function (Committee $committee) {
                return $committee->getName();
            }, $committees);
            $qb
                ->andWhere('quality.zone in (:committees)')
                ->setParameter('committees', $committees)
            ;
        }

        if (null !== $filter->getEmailSubscription() && $filter->getSubscriptionType()) {
            $qb
                ->leftJoin('adherent.subscriptionTypes', 'subscriptionType')
                ->addSelect('GROUP_CONCAT(subscriptionType.code) AS HIDDEN st_codes')
                ->groupBy('adherent.id')
            ;

            $subscriptionCondition = 'st_codes LIKE :subscription_code';
            if (false === $filter->getEmailSubscription()) {
                $subscriptionCondition = 'st_codes IS NULL OR st_codes NOT LIKE :subscription_code';
            }

            $qb
                ->having($subscriptionCondition)
                ->setParameter('subscription_code', '%'.$filter->getSubscriptionType().'%')
            ;
        }

        if (null !== $filter->isPoliticalCommitteeMember()) {
            $qb
                ->leftJoin('adherent.politicalCommitteeMembership', 'pcMembership')
                ->andWhere(\sprintf(
                    'pcMembership.id %s',
                    $filter->isPoliticalCommitteeMember() ? 'IS NOT NULL' : 'IS NULL')
                )
            ;
        }

        return $qb;
    }

    public function countForTerritorialCouncil(TerritorialCouncil $territorialCouncil, array $qualities = []): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(1)')
            ->where('m.territorialCouncil = :territorial_council')
            ->setParameter('territorial_council', $territorialCouncil)
        ;

        if ($qualities) {
            $qb
                ->innerJoin('m.qualities', 'quality')
                ->andWhere('quality.name IN (:qualities)')
                ->setParameter('qualities', $qualities)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
