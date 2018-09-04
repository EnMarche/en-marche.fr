<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

class EventAdherentSubscriptionCalculator extends AbstractEventSubscriptionCalculator
{
    public function getLabel(): string
    {
        return 'Adherents inscrits à des événements (total)';
    }

    protected function isAdherentOnly(): bool
    {
        return true;
    }
}
