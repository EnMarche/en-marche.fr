<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

trait EntityFollowersTrait
{
    /**
     * @var Collection|Adherent[]
     */
    private $followers;

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Adherent $follower): void
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
        }
    }

    public function removeFollower(Adherent $follower): void
    {
        $this->followers->removeElement($follower);
    }
}
