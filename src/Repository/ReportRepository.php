<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Report\Report;
use App\Entity\Report\ReportStatusEnum;
use App\Report\ReportType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * @return int[]
     */
    public function findIdsByNameForClass(string $class, string $name): array
    {
        if (!is_subclass_of($class, Report::class)) {
            throw new \InvalidArgumentException(sprintf('The class %s should extend %s.', $class, Report::class));
        }

        $ids = $this->_em->createQueryBuilder()
            ->from($class, 'report')
            ->select('report.id')
            ->join('report.subject', 'subject')
            ->andWhere('ILIKE(subject.name, :name) = true')
            ->setParameter('name', sprintf('%%%s%%', $name))
            ->getQuery()
            ->getScalarResult()
        ;

        return array_column($ids, 'id');
    }

    /**
     * @return int[]
     */
    public function findIdsByNameForAll(string $name): array
    {
        $ids = [];

        foreach (ReportType::SEARCHABLE_BY_NAME as $class) {
            $ids = array_merge($ids, $this->findIdsByNameForClass($class, $name));
        }

        sort($ids);

        return $ids;
    }

    public function anonymizeAuthorReports(Adherent $adherent)
    {
        return $this->createQueryBuilder('r')
            ->update()
            ->set('r.author', ':new_value')
            ->setParameter('new_value', null)
            ->where('r.author = :author')
            ->setParameter('author', $adherent)
            ->getQuery()
            ->execute()
        ;
    }

    public function findNotResolvedByClassAndSubject(string $class, $subject): array
    {
        if (!is_subclass_of($class, Report::class)) {
            throw new \InvalidArgumentException(sprintf('The class %s should extend %s.', $class, Report::class));
        }

        return $this->_em->createQueryBuilder('report')
            ->select('report')
            ->from($class, 'report')
            ->where('report.subject = :subject')
            ->andWhere('report.status != :resolved')
            ->setParameter('subject', $subject)
            ->setParameter('resolved', ReportStatusEnum::STATUS_RESOLVED)
            ->getQuery()
            ->getResult()
        ;
    }
}
