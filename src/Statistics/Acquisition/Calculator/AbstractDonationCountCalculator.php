<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Entity\Adherent;
use App\Statistics\Acquisition\StatisticsRequest;
use Doctrine\ORM\Query\Expr\Join;

abstract class AbstractDonationCountCalculator extends AbstractDonationCalculator
{
    protected function processing(StatisticsRequest $request, array $keys): array
    {
        $total = $this->getTotalInitial($request);

        return array_map(
            function (int $totalByMonth) use (&$total) {
                return $total += $totalByMonth;
            },
            $this->fillEmptyCase($this->getNewCounters($request), $keys)
        );
    }

    private function getTotalInitial(StatisticsRequest $request): int
    {
        $qb = $this->repository
            ->createQueryBuilder('donation')
            ->select('COUNT(1) AS total')
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

        if ($this->isAdherentOnly()) {
            $qb
                ->innerJoin('donation.donator', 'donator')
                ->innerJoin(Adherent::class, 'adherent', Join::WITH, 'adherent.emailAddress = donator.emailAddress')
                ->andWhere('adherent.adherent = true')
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getNewCounters(StatisticsRequest $request): array
    {
        $qb = $this->repository
            ->createQueryBuilder('donation')
            ->select('COUNT(1) AS total')
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

        if ($this->isAdherentOnly()) {
            $qb
                ->innerJoin('donation.donator', 'donator')
                ->innerJoin(Adherent::class, 'adherent', Join::WITH, 'adherent.emailAddress = donator.emailAddress')
                ->andWhere('adherent.adherent = true')
            ;
        }

        return $qb->getQuery()->getArrayResult();
    }

    protected function isAdherentOnly(): bool
    {
        return false;
    }
}
