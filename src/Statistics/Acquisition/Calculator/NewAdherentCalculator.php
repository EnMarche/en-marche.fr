<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Repository\AdherentRepository;
use App\Statistics\Acquisition\Calculator\Category\AdhesionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

class NewAdherentCalculator extends AbstractCalculator
{
    use AdhesionCategoryTrait;

    private $repository;

    public function __construct(AdherentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getLabel(): string
    {
        return 'Adhérents (nouveaux)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->repository
            ->createQueryBuilder('adherent')
            ->select('COUNT(1) AS total')
            ->addSelect("DATE_FORMAT(adherent.registeredAt, 'YYYYMM') AS date")
            ->innerJoin('adherent.referentTags', 'tags')
            ->where('adherent.registeredAt >= :start_date AND adherent.registeredAt <= :end_date')
            ->andWhere('adherent.adherent = true')
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
