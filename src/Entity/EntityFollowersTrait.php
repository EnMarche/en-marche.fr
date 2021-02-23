<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

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

    public function removeFollower(Adherent $adherent): void
    {
        if ($follower = $this->getFollower($adherent)) {
            $this->followers->removeElement($follower);
        }
    }

    public function hasFollower(Adherent $adherent): bool
    {
        return (bool) $this->getFollower($adherent);
    }

    public function getFollower(Adherent $adherent): ?FollowerInterface
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('adherent', $adherent))
        ;

        return $this->followers->matching($criteria)->count() > 0
            ? $this->followers->matching($criteria)->first()
            : null;
    }
}
