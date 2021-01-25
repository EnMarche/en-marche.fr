<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Repository\NewsletterSubscriptionRepository;
use App\Statistics\Acquisition\Calculator\Category\NewsletterSubscriptionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

class NewsletterSubscriptionFridayListCalculator extends AbstractCalculator
{
    use NewsletterSubscriptionCategoryTrait;

    private $adherentCalculator;
    private $newsletterSubscriptionRepository;

    public function __construct(
        NewsletterAdherentSubscriptionFridayListCalculator $adherentCalculator,
        NewsletterSubscriptionRepository $newsletterSubscriptionRepository
    ) {
        $this->adherentCalculator = $adherentCalculator;
        $this->newsletterSubscriptionRepository = $newsletterSubscriptionRepository;
    }

    public function getLabel(): string
    {
        return 'Inscrits à la lettre du vendredi (total)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->mergeResults(
            function (int $a, int $b) { return $a + $b; },
            $keys,
            $this->adherentCalculator->calculate($request, $keys),
            $this->getVisitorStats($request, $keys)
        );
    }

    private function getVisitorStats(StatisticsRequest $request, array $keys): array
    {
        $total = (int) $this->newsletterSubscriptionRepository
            ->createQueryBuilder('newsletter')
            ->select('COUNT(1) AS total')
            ->where('newsletter.createdAt < :date')
            ->andWhere('(newsletter.country IN (:tags) OR newsletter.postalCode IN (:tags))')
            ->setParameters([
                'date' => $request->getStartDateAsString(),
                'tags' => $request->getTags(),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $newByMonth = $this->newsletterSubscriptionRepository
            ->createQueryBuilder('newsletter')
            ->select('COUNT(1) AS total')
            ->addSelect("DATE_FORMAT(newsletter.createdAt, 'YYYYMM') AS date")
            ->where('newsletter.createdAt >= :start_date AND newsletter.createdAt <= :end_date')
            ->andWhere('(newsletter.country IN (:tags) OR newsletter.postalCode IN (:tags))')
            ->setParameters([
                'start_date' => $request->getStartDateAsString(),
                'end_date' => $request->getEndDateAsString(),
                'tags' => $request->getTags(),
            ])
            ->groupBy('date')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(
            function (int $totalByMonth) use (&$total) {
                return $total += $totalByMonth;
            },
            $this->fillEmptyCase($newByMonth, $keys)
        );
    }
}
