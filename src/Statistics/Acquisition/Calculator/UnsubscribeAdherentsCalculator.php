<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Repository\UnregistrationRepository;
use App\Statistics\Acquisition\Calculator\Category\AdhesionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

class UnsubscribeAdherentsCalculator extends AbstractCalculator
{
    use AdhesionCategoryTrait;

    private $repository;

    public function __construct(UnregistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getLabel(): string
    {
        return 'Desadhésions (nouveaux)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->repository
            ->createQueryBuilder('adherent')
            ->select('COUNT(1) AS total')
            ->addSelect("DATE_FORMAT(adherent.unregisteredAt, 'YYYYMM') AS date")
            ->innerJoin('adherent.referentTags', 'tags')
            ->where('adherent.unregisteredAt >= :start_date AND adherent.unregisteredAt <= :end_date')
            ->andWhere('tags.code IN (:tags)')
            ->setParameters([
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
