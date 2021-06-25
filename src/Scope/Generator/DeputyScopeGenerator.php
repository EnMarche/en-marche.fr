<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\AppEnum;
use App\Scope\Scope;
use App\Scope\ScopeEnum;

class DeputyScopeGenerator extends AbstractScopeGenerator
{
    public function generate(Adherent $adherent): Scope
    {
        return new Scope(
            $this->getScope(),
            [$adherent->getManagedDistrict()->getReferentTag()->getZone()],
            [AppEnum::DATA_CORNER]
        );
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isDeputy();
    }

    public function getScope(): string
    {
        return ScopeEnum::DEPUTY;
    }
}
