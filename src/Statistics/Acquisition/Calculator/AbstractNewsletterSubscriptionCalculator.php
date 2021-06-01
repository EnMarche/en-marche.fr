<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Entity\Reporting\EmailSubscriptionHistoryAction;
use App\Repository\EmailSubscriptionHistoryRepository;
use App\Statistics\Acquisition\Calculator\Category\NewsletterSubscriptionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

abstract class AbstractNewsletterSubscriptionCalculator extends AbstractCalculator
{
    use NewsletterSubscriptionCategoryTrait;

    private $subscriptionHistoryRepository;

    public function __construct(EmailSubscriptionHistoryRepository $subscriptionHistoryRepository)
    {
        $this->subscriptionHistoryRepository = $subscriptionHistoryRepository;
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        $total = $this->getTotalInitial($request);

        return array_map(
            function (int $totalByMonth) use (&$total) {
                return $total += $totalByMonth;
            },
            $this->fillEmptyCase(
                $this->getNewCounters($request),
                $keys
            )
        );
    }

    private function getTotalInitial(StatisticsRequest $request): int
    {
        return (int) $this->subscriptionHistoryRepository
            ->createQueryBuilder('history')
            ->select('SUM(CASE WHEN history.action = :action THEN 1 ELSE -1 END) AS total')
            ->innerJoin('history.subscriptionType', 'st')
            ->innerJoin('history.referentTags', 'tags')
            ->where('history.date < :date')
            ->andWhere('st.code = :code')
            ->andWhere('tags.code IN (:tags)')
            ->setParameters([
                'action' => EmailSubscriptionHistoryAction::SUBSCRIBE,
                'code' => $this->getSubscriptionCode(),
                'date' => $request->getStartDateAsString(),
                'tags' => $request->getTags(),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getNewCounters(StatisticsRequest $request): array
    {
        return $this->subscriptionHistoryRepository
            ->createQueryBuilder('history')
            ->select('SUM(CASE WHEN history.action = :action THEN 1 ELSE -1 END) AS total')
            ->addSelect("TO_CHAR(history.date, 'YYYYMM') AS date")
            ->innerJoin('history.subscriptionType', 'st')
            ->innerJoin('history.referentTags', 'tags')
            ->where('history.date >= :start_date AND history.date <= :end_date')
            ->andWhere('st.code = :code')
            ->andWhere('tags.code IN (:tags)')
            ->setParameters([
                'action' => EmailSubscriptionHistoryAction::SUBSCRIBE,
                'code' => $this->getSubscriptionCode(),
                'start_date' => $request->getStartDateAsString(),
                'end_date' => $request->getEndDateAsString(),
                'tags' => $request->getTags(),
            ])
            ->groupBy('date')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    abstract protected function getSubscriptionCode(): string;
}
