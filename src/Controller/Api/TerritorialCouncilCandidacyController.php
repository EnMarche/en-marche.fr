<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Election;
use App\Form\TerritorialCouncil\SearchAvailableMembershipType;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\TerritorialCouncil\Candidacy\SearchAvailableMembershipFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class TerritorialCouncilCandidacyController extends AbstractController
{
    /**
     * @Route("/territorial-council/candidacy/available-memberships", name="api_territorial_council_candidacy_available_memberships_get", methods={"GET"})
     *
     * @Security("is_granted('ABLE_TO_BECOME_TERRITORIAL_COUNCIL_CANDIDATE', user)")
     *
     * @param Adherent $adherent
     */
    public function getAvailableMembershipsAction(
        Request $request,
        UserInterface $adherent,
        TerritorialCouncilMembershipRepository $repository
    ): Response {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            throw $this->createAccessDeniedException('No election is started');
        }

        if (!$candidacy = $membership->getCandidacyForElection($election)) {
            throw $this->createAccessDeniedException('You do not have a candidacy');
        }

        $form = $this
            ->createForm(
                SearchAvailableMembershipType::class,
                $filter = new SearchAvailableMembershipFilter(),
                ['qualities' => $membership->getAvailableForCandidacyQualityNames()]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->json(
                ['message' => $form->getErrors(true)[0]->getMessage()],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            $repository->findAvailableMemberships($candidacy, $filter),
            JsonResponse::HTTP_OK,
            [],
            ['groups' => ['api_candidacy_read']]
        );
    }
}
