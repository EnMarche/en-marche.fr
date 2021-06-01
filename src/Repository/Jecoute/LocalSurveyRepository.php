<?php

namespace App\Repository\Jecoute;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Repository\ReferentTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class LocalSurveyRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalSurvey::class);
    }

    /**
     * @return LocalSurvey[]
     */
    public function findAllByZones(array $zones): array
    {
        return $this
            ->createSurveysByZonesQueryBuilder($zones)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return LocalSurvey[]
     */
    public function findAllByZonesWithStats(array $zones): array
    {
        return $this
            ->createQueryBuilder('survey')
            ->leftJoin('survey.zone', 'zone')
            ->leftJoin('zone.parents', 'parent')
            ->leftJoin('zone.children', 'child')
            ->addSelect('zone')
            ->addSelect(sprintf('(SELECT COUNT(q.id) FROM %s AS q WHERE q.survey = survey) AS questions_count', SurveyQuestion::class))
            ->addSelect(sprintf('(SELECT COUNT(r.id) FROM %s AS r WHERE r.survey = survey) AS responses_count', DataSurvey::class))
            ->where('(zone IN (:zones) OR parent IN (:zones) OR child IN (:zones))')
            ->setParameter('zones', $zones)
            ->orderBy('survey.createdAt', 'DESC')
            ->addOrderBy('survey.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function createSurveysByZonesQueryBuilder(array $zones): QueryBuilder
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions', 'zone')
            ->innerJoin('survey.questions', 'questions')
            ->innerJoin('survey.zone', 'zone')
            ->leftJoin('zone.children', 'child')
            ->where('(zone IN (:zones) OR child IN (:zones))')
            ->setParameter('zones', $zones)
            ->andWhere('survey.published = true')
            ->orderBy('survey.id')
        ;
    }

    public function findAllByAuthor(Adherent $adherent): array
    {
        return $this
            ->createQueryBuilder('survey')
            ->leftJoin('survey.zone', 'zone')
            ->addSelect('zone')
            ->addSelect(sprintf('(SELECT COUNT(q.id) FROM %s AS q WHERE q.survey = survey) AS questions_count', SurveyQuestion::class))
            ->addSelect(sprintf('(SELECT COUNT(r.id) FROM %s AS r WHERE r.survey = survey) AS responses_count', DataSurvey::class))
            ->where('survey.author = :author')
            ->setParameter('author', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }
}
