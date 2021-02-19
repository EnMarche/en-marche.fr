<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface FollowedInterface extends ImageOwnerInterface
{
    public function getFollowers(): Collection;

    public function addFollower(Adherent $follower): void;

    public function removeFollower(Adherent $follower): void;
}
