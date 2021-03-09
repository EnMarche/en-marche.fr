<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Form\TerritorialCouncil\CandidacyQualityType;
use App\Form\VotingPlatform\Candidacy\TerritorialCouncilCandidacyType;
use App\Repository\TerritorialCouncil\CandidacyInvitationRepository;
use App\TerritorialCouncil\CandidacyManager;
use App\ValueObject\Genders;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/conseil-territorial/candidature", name="app_territorial_council_candidature")
 *
 * @Security("is_granted('ABLE_TO_BECOME_TERRITORIAL_COUNCIL_CANDIDATE', user)")
 */
class CandidatureController extends AbstractController
{
    private $manager;

    public function __construct(CandidacyManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("", name="_edit", methods={"POST", "GET"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function editCandidatureAction(Request $request, UserInterface $adherent): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas candidater ou modifier votre candidature pour cette désignation.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        $candidacy = $membership->getCandidacyForElection($election);

        if (!$candidacy) {
            /** @var Adherent $adherent */
            $candidacyGender = $adherent->getGender();

            if ($adherent->isOtherGender()) {
                $candidacyGender = $request->query->get('gender');

                if (!$candidacyGender || !\in_array($candidacyGender, Genders::CIVILITY_CHOICES, true)) {
                    $this->addFlash('error', 'Le genre de la candidature n\'a pas été sélectionné');

                    return $this->redirectToRoute('app_territorial_council_index');
                }
            }

            $candidacy = new Candidacy($membership, $election, $candidacyGender);
        }

        $form = $this
            ->createForm(
                TerritorialCouncilCandidacyType::class,
                $candidacy,
                [
                    'with_accept' => !$membership->containsQualities(
                        [TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, TerritorialCouncilQualityEnum::CITY_COUNCILOR]
                    ),
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $isCreation = null === $candidacy->getId();
            $this->manager->updateCandidature($candidacy);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            if ($candidacy->hasPendingInvitation() || !$isCreation) {
                return $this->redirectToRoute('app_territorial_council_index');
            }

            return $this->redirectToRoute('app_territorial_council_candidature_select_pair_candidate');
        }

        return $this->render('territorial_council/candidacy_step1_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/retirer", name="_remove", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function removeCandidacyAction(UserInterface $adherent): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas retirer votre candidature.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($candidacy = $membership->getCandidacyForElection($election)) || $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        $this->manager->removeCandidacy($candidacy);

        $this->addFlash('info', 'Votre candidature a bien été supprimée');

        return $this->redirectToRoute('app_territorial_council_index');
    }

    /**
     * @Route("/choix-de-binome", name="_select_pair_candidate", methods={"GET", "POST"})
     *
     * @param Adherent $adherent
     */
    public function selectPairCandidateAction(Request $request, UserInterface $adherent): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($candidacy = $membership->getCandidacyForElection($election)) || $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_territorial_council_candidature_edit');
        }

        if ($candidacy->hasInvitation()) {
            $invitation = $candidacy->getFirstInvitation();

            if ($invitation->isAccepted()) {
                return $this->redirectToRoute('app_territorial_council_index');
            }

            $previouslyInvitedMembership = $invitation->getMembership();
        }

        $form = $this
            ->createForm(
                CandidacyQualityType::class,
                $candidacy,
                ['qualities' => $membership->getAvailableForCandidacyQualityNames()]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($invitation = $candidacy->getFirstInvitation()) {
                $this->manager->updateInvitation($invitation, $candidacy, $previouslyInvitedMembership ?? null);

                $this->addFlash('info', 'Votre invitation a bien été envoyée');

                return $this->redirectToRoute('app_territorial_council_candidature_select_pair_candidate_finish');
            }

            $this->manager->saveSingleCandidature($candidacy, $previouslyInvitedMembership ?? null);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/candidacy_step2_invitation.html.twig', [
            'form' => $form->createView(),
            'invitation' => $candidacy->getFirstInvitation(),
        ]);
    }

    /**
     * @Route("/choix-de-binome/fini", name="_select_pair_candidate_finish", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function finishInvitationStepAction(UserInterface $adherent): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($candidacy = $membership->getCandidacyForElection($election)) || !($invitation = $candidacy->getFirstInvitation())) {
            return $this->redirectToRoute('app_territorial_council_candidature_edit');
        }

        return $this->render('territorial_council/candidacy_step3_confirmation.html.twig', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * @Route("/mes-invitations", name="_invitation_list", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function invitationListAction(UserInterface $adherent, CandidacyInvitationRepository $repository): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (($candidacy = $membership->getCandidacyForElection($election)) && $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/invitation_list.html.twig', [
            'invitations' => $repository->findAllPendingForMembership($membership, $election),
        ]);
    }

    /**
     * @Route("/mes-invitations/{uuid}/accepter", name="_invitation_accept", methods={"GET", "POST"})
     *
     * @Security("invitation.getMembership() == user.getTerritorialCouncilMembership()")
     *
     * @param Adherent $adherent
     */
    public function acceptInvitationAction(
        Request $request,
        CandidacyInvitation $invitation,
        UserInterface $adherent
    ): Response {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        $acceptedBy = $membership->getCandidacyForElection($election) ?? new Candidacy($membership, $election, $adherent->getGender());

        if ($acceptedBy->isConfirmed()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        $acceptedBy->setBinome($invitedBy = $invitation->getCandidacy());
        $invitedBy->setBinome($acceptedBy);

        $acceptedBy->updateFromBinome();

        $form = $this
            ->createForm(
                TerritorialCouncilCandidacyType::class,
                $acceptedBy,
                [
                    'validation_groups' => ['Default', 'accept_invitation'],
                    'with_accept' => !$membership->containsQualities(
                        [TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, TerritorialCouncilQualityEnum::CITY_COUNCILOR]
                    ),
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->acceptInvitation($invitation, $acceptedBy);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/candidacy_step1_edit.html.twig', [
            'form' => $form->createView(),
            'candidacy' => $acceptedBy,
            'invitation' => $invitation,
        ]);
    }

    /**
     * @Route("/mes-invitations/{uuid}/decliner", name="_invitation_decline", methods={"GET"})
     *
     * @Security("invitation.getMembership() == user.getTerritorialCouncilMembership()")
     *
     * @param Adherent $adherent
     */
    public function declineInvitationAction(CandidacyInvitation $invitation, UserInterface $adherent): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!$invitation->isPending()) {
            $this->addFlash('error', 'Vous ne pouvez pas décliner cette invitation');

            return $this->redirectToRoute('app_territorial_council_candidature_invitation_list');
        }

        $this->manager->declineInvitation($invitation);

        $this->addFlash('info', 'Invitation a bien été déclinée');

        return $this->redirectToRoute('app_territorial_council_candidature_invitation_list');
    }
}
