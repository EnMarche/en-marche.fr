<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Instance\InstanceQualityEnum;

class DeputyQualityUpdater extends AbstractMandateTypeBasedQualityUpdater
{
    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::DEPUTY];
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::DEPUTY;
    }
}
