<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface FollowedInterface
{
    public function getFollowers(): Collection;

    public function addFollower(Adherent $follower): void;

    public function removeFollower(Adherent $follower): void;
}
