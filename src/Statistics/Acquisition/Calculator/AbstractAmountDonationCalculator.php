<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Statistics\Acquisition\StatisticsRequest;

abstract class AbstractAmountDonationCalculator extends AbstractDonationCalculator
{
    protected function processing(StatisticsRequest $request, array $keys): array
    {
        $total = $this->getTotalInitial($request);

        return array_map(
            function (int $totalByMonth) use (&$total) {
                $total += $totalByMonth;

                return round($total / 100, 2);
            },
            $this->fillEmptyCase($this->getNewCounters($request), $keys)
        );
    }

    private function getTotalInitial(StatisticsRequest $request): int
    {
        $qb = $this->repository
            ->createQueryBuilder('donation')
            ->select('SUM(donation.amount) AS total')
            ->where('donation.createdAt < :date')
            ->andWhere('donation.status = :status')
            ->andWhere('donation.duration = :duration')
            ->setParameters([
                'date' => $request->getStartDateAsString(),
                'status' => $this->getDonationStatus(),
                'duration' => $this->getDonationDuration(),
            ])
        ;

        $this->addTagFilter($qb, $request->getTags());

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getNewCounters(StatisticsRequest $request): array
    {
        $qb = $this->repository
            ->createQueryBuilder('donation')
            ->select('SUM(donation.amount) AS total')
            ->addSelect("TO_CHAR(donation.createdAt, 'YYYYMM') AS date")
            ->where('donation.createdAt >= :start_date AND donation.createdAt <= :end_date')
            ->andWhere('donation.status = :status')
            ->andWhere('donation.duration = :duration')
            ->setParameters([
                'start_date' => $request->getStartDateAsString(),
                'end_date' => $request->getEndDateAsString(),
                'status' => $this->getDonationStatus(),
                'duration' => $this->getDonationDuration(),
            ])
            ->groupBy('date')
        ;

        $this->addTagFilter($qb, $request->getTags());

        return $qb->getQuery()->getArrayResult();
    }
}
