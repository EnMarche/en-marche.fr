<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Entity\Reporting\CommitteeMembershipAction;
use App\Repository\CommitteeMembershipHistoryRepository;
use App\Statistics\Acquisition\Calculator\Category\AdhesionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

class CommitteeMemberCalculator extends AbstractCalculator
{
    use AdhesionCategoryTrait;

    private $repository;

    public function __construct(CommitteeMembershipHistoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getLabel(): string
    {
        return 'Adhérents membres de comités (total)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        $total = $this->getTotalInitial($request);

        return array_map(
            function (int $totalByMonth) use (&$total) {
                return $total += $totalByMonth;
            },
            $this->fillEmptyCase($this->getNewMemberCounters($request), $keys)
        );
    }

    private function getTotalInitial(StatisticsRequest $request): int
    {
        return (int) $this->repository
            ->createQueryBuilder('c')
            ->select('SUM(CASE WHEN (c.action = :join_action) THEN 1 ELSE -1 END) AS total')
            ->innerJoin('c.referentTags', 'tags')
            ->where('c.date < :date AND tags.code IN (:tags)')
            ->setParameters([
                'join_action' => CommitteeMembershipAction::JOIN,
                'date' => $request->getStartDateAsString(),
                'tags' => $request->getTags(),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getNewMemberCounters(StatisticsRequest $request): array
    {
        return $this->repository
            ->createQueryBuilder('c')
            ->select('SUM(CASE WHEN (c.action = :join_action) THEN 1 ELSE -1 END) AS total')
            ->addSelect("DATE_FORMAT(c.date, 'YYYYMM') AS date")
            ->innerJoin('c.referentTags', 'tags')
            ->where('c.date >= :start_date AND c.date <= :end_date')
            ->andWhere('tags.code IN (:tags)')
            ->setParameters([
                'join_action' => CommitteeMembershipAction::JOIN,
                'start_date' => $request->getStartDateAsString(),
                'end_date' => $request->getEndDateAsString(),
                'tags' => $request->getTags(),
            ])
            ->groupBy('date')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
