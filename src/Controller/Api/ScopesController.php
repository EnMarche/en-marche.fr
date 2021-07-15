<?php

namespace App\Controller\Api;

use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScopesController extends AbstractController
{
    /**
     * @Route("/v3/profile/me/scopes", name="app_api_user_profile_scopes", methods={"GET"})
     */
    public function __invoke(GeneralScopeGenerator $scopeGenerator): JsonResponse
    {
        return $this->json(
            $scopeGenerator->generateScopes($this->getUser()),
            Response::HTTP_OK,
            [],
            ['groups' => ['scopes']]
        );
    }
}
