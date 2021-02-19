<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Adherent;
use App\Entity\FollowedInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FollowController
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function follow(FollowedInterface $data): FollowedInterface
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof Adherent) {
            throw new BadRequestHttpException('No adherent to add as follower');
        }

        $data->addFollower($user);

        return $data;
    }

    public function unfollow(FollowedInterface $data): FollowedInterface
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof Adherent) {
            throw new BadRequestHttpException('No adherent to remove from followers');
        }

        $data->removeFollower($user);

        return $data;
    }
}
