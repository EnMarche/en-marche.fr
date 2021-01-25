<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TerritorialCouncilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TerritorialCouncil::class);
    }

    public function findOneByReferentTag(ReferentTag $referentTag): ?TerritorialCouncil
    {
        return $this->createQueryBuilder('tc')
            ->innerJoin('tc.referentTags', 'tag')
            ->where('tag.id = :tag')
            ->setParameter('tag', $referentTag)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return TerritorialCouncil[]
     */
    public function findAllWithoutStartedElection(Designation $designation, int $offset = 0, int $limit = 200): array
    {
        return $this->createQueryBuilder('tc')
            ->innerJoin('tc.referentTags', 'tag')
            ->leftJoin('tc.currentDesignation', 'd')
            ->where('(tc.currentDesignation IS NULL OR (d.voteEndDate IS NOT NULL AND d.voteEndDate < :date))')
            ->andWhere('tag IN (:tags)')
            ->setParameters([
                'date' => $designation->getCandidacyStartDate(),
                'tags' => $designation->getReferentTags(),
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByMandates(array $mandates): array
    {
        $zones = [];
        array_walk($mandates, function (Mandate $mandate) use (&$zones) {
            $zones[] = $mandate->getGeoZone();
        });

        return $this->createQueryBuilder('tc')
            ->leftJoin('tc.zones', 'zone')
            ->leftJoin('zone.children', 'child')
            ->where('(zone IN (:zones) OR (zone.code NOT LIKE :paris AND zone.code NOT LIKE :paris_circo AND child IN (:zones)))')
            ->setParameter('zones', $zones)
            ->setParameter('paris', '751%')
            ->setParameter('paris_circo', '75-%')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForSupervisor(Adherent $adherent): array
    {
        return $this->createQueryBuilder('tc')
            ->innerJoin('tc.referentTags', 'tag')
            ->innerJoin(Committee::class, 'committee', Join::WITH, 'tag MEMBER OF committee.referentTags')
            ->innerJoin(CommitteeAdherentMandate::class, 'am', Join::WITH, 'committee = am.committee')
            ->where('am.adherent = :adherent')
            ->andWhere('am.committee IS NOT NULL AND am.quality = :supervisor AND am.finishAt IS NULL AND am.provisional = false')
            ->setParameter('adherent', $adherent)
            ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCommittees(array $committees): array
    {
        return $this->createQueryBuilder('tc')
            ->leftJoin('tc.referentTags', 'tag')
            ->leftJoin(Committee::class, 'committee', Join::WITH, 'tag MEMBER OF committee.referentTags')
            ->where('committee IN (:committees)')
            ->setParameter('committees', $committees)
            ->getQuery()
            ->getResult()
        ;
    }

    public function createQueryBuilderWithReferentTagsCondition(array $referentTags): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('tc')
            ->innerJoin('tc.referentTags', 'tag')
        ;

        if (!$referentTags) {
            return $qb->andWhere('1 = 0');
        }

        $tagCondition = 'tag IN (:tags)';

        foreach ($referentTags as $referentTag) {
            if ('75' === $referentTag->getCode()) {
                $tagCondition = "(tag IN (:tags) OR tag.name LIKE '%Paris%')";

                break;
            }
        }

        return $qb
            ->where($tagCondition)
            ->andWhere('tc.isActive = :true')
            ->setParameters([
                'tags' => $referentTags,
                'true' => true,
            ])
        ;
    }
}
