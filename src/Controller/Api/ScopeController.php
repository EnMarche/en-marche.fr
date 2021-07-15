<?php

namespace App\Controller\Api;

use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ScopeController extends AbstractController
{
    use ScopeTrait;

    public function __construct(GeneralScopeGenerator $generalScopeGenerator)
    {
        $this->generalScopeGenerator = $generalScopeGenerator;
    }

    /**
     * @Route("/v3/profile/me/scope/{scopeCode}", name="app_api_user_profile_scope", methods={"GET"})
     */
    public function __invoke(UserInterface $user, string $scopeCode): JsonResponse
    {
        return $this->json(
            $this->getScope($scopeCode, $user),
            Response::HTTP_OK,
            [],
            ['groups' => ['scope']]
        );
    }
}
