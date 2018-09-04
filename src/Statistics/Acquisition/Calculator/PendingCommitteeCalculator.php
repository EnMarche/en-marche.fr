<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Entity\Committee;
use AppBundle\Statistics\Acquisition\StatisticsRequest;

class PendingCommitteeCalculator extends AbstractCommitteeCalculator
{
    public function getLabel(): string
    {
        return 'Comités en attente (new)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->calculateCommitteeByStatus(Committee::PENDING, $request);
    }
}
