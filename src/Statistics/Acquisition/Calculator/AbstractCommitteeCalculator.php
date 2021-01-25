<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Repository\CommitteeRepository;
use App\Statistics\Acquisition\Calculator\Category\AdhesionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

abstract class AbstractCommitteeCalculator extends AbstractCalculator
{
    use AdhesionCategoryTrait;

    private $repository;

    public function __construct(CommitteeRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function calculateCommitteeByStatus(string $status, StatisticsRequest $request): array
    {
        return $this->repository
            ->createQueryBuilder('committee')
            ->select('COUNT(1) AS total')
            ->addSelect("DATE_FORMAT(committee.createdAt, 'YYYYMM') AS date")
            ->innerJoin('committee.referentTags', 'tags')
            ->where('committee.createdAt >= :start_date AND committee.createdAt <= :end_date')
            ->andWhere('committee.status = :status')
            ->andWhere('tags.code IN (:tags)')
            ->setParameters([
                'start_date' => $request->getStartDateAsString(),
                'end_date' => $request->getEndDateAsString(),
                'status' => $status,
                'tags' => $request->getTags(),
            ])
            ->groupBy('date')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
