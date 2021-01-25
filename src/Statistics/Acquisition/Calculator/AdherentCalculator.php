<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Repository\AdherentRepository;
use App\Statistics\Acquisition\Calculator\Category\AdhesionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

class AdherentCalculator extends AbstractCalculator
{
    use AdhesionCategoryTrait;

    private $repository;
    private $newAdherentCalculator;

    public function __construct(NewAdherentCalculator $newAdherentCalculator, AdherentRepository $repository)
    {
        $this->newAdherentCalculator = $newAdherentCalculator;
        $this->repository = $repository;
    }

    public function getLabel(): string
    {
        return 'Adhérents (total)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        $total = (int) $this->repository
            ->createQueryBuilder('adherent')
            ->select('COUNT(1)')
            ->innerJoin('adherent.referentTags', 'tags')
            ->where('adherent.registeredAt < :date')
            ->andWhere('adherent.adherent = true')
            ->andWhere('tags.code IN (:tags)')
            ->setParameters([
                'date' => $request->getStartDateAsString(),
                'tags' => $request->getTags(),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return array_map(
            function (int $totalByMonth) use (&$total) {
                return $total += $totalByMonth;
            },
            $this->newAdherentCalculator->calculate($request, $keys)
        );
    }
}
