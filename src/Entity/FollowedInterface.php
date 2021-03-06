<?php

namespace App\Entity;

interface FollowedInterface
{
    public function addFollower(FollowerInterface $follower): void;

    public function getFollower(Adherent $adherent): ?FollowerInterface;

    /**
     * @return FollowerInterface[]
     */
    public function getFollowers(): array;

    public function createFollower(Adherent $adherent): FollowerInterface;
}
