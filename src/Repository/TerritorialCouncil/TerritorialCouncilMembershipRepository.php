<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
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
            ->andWhere(sprintf('membership.id NOT IN (%s)',
                $this->createQueryBuilder('t3')
                    ->select('t3.id')
                    ->innerJoin('t3.qualities', 't4')
                    ->innerJoin('t3.adherent', 't5')
                    ->leftJoin('t5.adherentMandates', 't6', Join::WITH, 't6.committee IS NOT NULL AND t6.quality IS NULL AND t6.finishAt IS NULL')
                    ->where('t3.territorialCouncil = :council')
                    ->andWhere('t6.id IS NULL')
                    ->having('STRING_AGG(t4.name, \',\') = :ad_quality')
                    ->groupBy('t3.id')
                    ->getDQL()
            ))
            ->andWhere(sprintf('membership.id NOT IN (%s)',
                $this->createQueryBuilder('t7')
                    ->select('t7.id')
                    ->innerJoin('t7.qualities', 't8')
                    ->innerJoin('t7.adherent', 't9')
                    ->leftJoin('t9.adherentMandates', 't10', Join::WITH, 't10.committee IS NOT NULL AND t10.quality = :al_mandate_quality AND t10.finishAt IS NULL')
                    ->where('t7.territorialCouncil = :council')
                    ->andWhere('t10.id IS NULL')
                    ->having('STRING_AGG(t8.name, \',\') = :al_quality')
                    ->groupBy('t7.id')
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
                'al_quality' => TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                'ad_quality' => TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                'al_mandate_quality' => CommitteeMandateQualityEnum::SUPERVISOR,
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
                ->andWhere('(ILIKE(adherent.firstName, :query) = true OR ILIKE(adherent.lastName, :query) = true)')
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

        if (false !== strpos($filter->getSort(), '.')) {
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
                ->andWhere('ILIKE(adherent.lastName, :last_name) = true')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('ILIKE(adherent.firstName, :first_name) = true')
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
            $cities = array_map(function (Zone $city) {
                return $city->getName();
            }, $cities);
            $qb
                ->andWhere('quality.zone in (:cities)')
                ->setParameter('cities', $cities)
            ;
        }

        if ($committees = $filter->getCommittees()) {
            $committees = array_map(function (Committee $committee) {
                return $committee->getName();
            }, $committees);
            $qb
                ->andWhere('quality.zone in (:committees)')
                ->setParameter('committees', $committees)
            ;
        }

        if (null !== $filter->getEmailSubscription() && $filter->getSubscriptionType()) {
            $subQuery = $this
                ->createQueryBuilder('tcm2')
                ->innerJoin('tcm2.adherent', 'a2')
                ->select('a2.id')
                ->innerJoin('a2.subscriptionTypes', 's2')
                ->andWhere('s2.code = :subscription_code')
                ->andWhere('tcm2.territorialCouncil = tcm.territorialCouncil')
                ->getDQL()
            ;

            if (false === $filter->getEmailSubscription()) {
                $qb->andWhere($qb->expr()->notIn('adherent.id', $subQuery));
            } else {
                $qb->andWhere($qb->expr()->in('adherent.id', $subQuery));
            }

            $qb
                ->setParameter('subscription_code', $filter->getSubscriptionType())
            ;
        }

        if (null !== $filter->isPoliticalCommitteeMember()) {
            $qb
                ->leftJoin('adherent.politicalCommitteeMembership', 'pcMembership')
                ->andWhere(sprintf(
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
