<?php

namespace App\Entity;

interface FollowerInterface
{
    public function getFollowed(): FollowedInterface;
    public function getAdherent(): ?Adherent;
}
