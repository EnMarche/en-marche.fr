<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;

interface ZoneableEntity extends EntityPostAddressInterface
{
    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection;

    public function addZone(Zone $Zone): void;

    public function removeZone(Zone $Zone): void;

    public function clearZones(): void;
}
