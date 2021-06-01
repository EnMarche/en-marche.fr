<?php

namespace App\History;

use App\Entity\Adherent;
use App\Repository\CommitteeMembershipHistoryRepository;
use App\Statistics\StatisticsParametersFilter;
use Cake\Chronos\Chronos;

class CommitteeMembershipHistoryHandler
{
    private $historyRepository;

    public function __construct(CommitteeMembershipHistoryRepository $historyRepository)
    {
        $this->historyRepository = $historyRepository;
    }

    public function queryCountByMonth(
        Adherent $referent,
        int $months = 6,
        StatisticsParametersFilter $filter = null
    ): array {
        $countByMonth = [];
        foreach (range(0, $months - 1) as $monthInterval) {
            $until = $monthInterval
                ? (new Chronos("last day of -$monthInterval month"))->setTime(23, 59, 59, 999)
                : new Chronos()
            ;

            $count = $this->historyRepository->countAdherentMemberOfAtLeastOneCommitteeManagedBy($referent, $until, $filter);

            $countByMonth[] = ['date' => $until->format('Y-m'), 'count' => $count];
        }

        return $countByMonth;
    }
}
