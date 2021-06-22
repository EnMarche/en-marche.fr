<?php

namespace App\Controller\EnMarche\Coalition;

use App\Coalition\Filter\CauseFilter;
use App\Coalition\MessageNotifier;
use App\Entity\Coalition\Cause;
use App\Exporter\CausesExporter;
use App\Form\Coalition\CauseFilterType;
use App\Form\Coalition\CauseType;
use App\Mailchimp\Synchronisation\Command\CoalitionMemberChangeCommand;
use App\Repository\Coalition\CauseFollowerRepository;
use App\Repository\Coalition\CauseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-coalition/causes", name="app_coalition_")
 *
 * @Security("is_granted('ROLE_COALITION_MODERATOR')")
 */
class CausesController extends AbstractController
{
    /**
     * @Route(".{_format}", name="causes_list", methods={"GET"}, defaults={"_format": "html"}, requirements={"_format": "html|csv|xls"})
     */
    public function list(
        Request $request,
        string $_format,
        CauseRepository $causeRepository,
        CausesExporter $exporter
    ): Response {
        $filter = new CauseFilter();

        $form = $this->createForm(CauseFilterType::class, $filter)->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new CauseFilter(Cause::STATUS_PENDING);
        }

        if ('html' !== $_format) {
            return $exporter->getResponse($_format, $filter);
        }

        return $this->render('coalition/causes_list.html.twig', [
            'causes' => $causeRepository->searchByFilter($filter, $request->query->getInt('page', 1)),
            'total_count' => $causeRepository->countCauses(),
            'form' => $form->createView(),
            'filter' => $filter,
        ]);
    }

    /**
     * @Route("/approuver", name="approve_causes", defaults={"status": Cause::STATUS_APPROVED}, condition="request.isXmlHttpRequest()", methods={"POST"})
     * @Route("/refuser", name="refuse_causes", defaults={"status": Cause::STATUS_REFUSED}, condition="request.isXmlHttpRequest()", methods={"POST"})
     */
    public function changeCauseStatus(
        string $status,
        Request $request,
        CauseRepository $causeRepository,
        EntityManagerInterface $entityManager,
        MessageNotifier $notifier,
        MessageBusInterface $bus
    ): Response {
        if (!$ids = (array) $request->request->get('ids')) {
            return $this->json('"ids" not provided', Response::HTTP_BAD_REQUEST);
        }

        foreach ($causeRepository->getByIds($ids) as $cause) {
            if (Cause::STATUS_APPROVED === $status) {
                if ($cause->isApproved()) {
                    continue;
                }

                $cause->approve();
                $entityManager->flush();

                $notifier->sendCauseApprovalMessage($cause);
            } else {
                if ($cause->isRefused()) {
                    continue;
                }

                $cause->refuse();
                $entityManager->flush();
            }

            foreach ($cause->getFollowers() as $follower) {
                $adherent = $follower->getAdherent();
                $bus->dispatch(
                    new CoalitionMemberChangeCommand(
                        $adherent ? $adherent->getEmailAddress() : $follower->getEmailAddress(),
                        $follower->isAdherent()
                ));
            }
        }

        return $this->json('', Response::HTTP_OK);
    }

    /**
     * @Route("/{slug}/editer", name="cause_edit", requirements={"slug": "[A-Za-z0-9\-]+"}, methods={"GET", "POST"})
     */
    public function editAction(Request $request, Cause $cause, EntityManagerInterface $entityManager): Response
    {
        $actualAuthor = $cause->getAuthor();
        $form = $this->createForm(CauseType::class, $cause)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($actualAuthor !== ($newAuthor = $cause->getAuthor()) && !$cause->hasFollower($newAuthor)) {
                $entityManager->persist($cause->createFollower($newAuthor));
                $entityManager->flush();
                $cause->refreshFollowersCount();
            }

            $entityManager->flush();

            $this->addFlash('info', sprintf('La cause "%s" a bien été modifiée.', $cause->getName()));

            return $this->redirectToRoute('app_coalition_causes_list');
        }

        return $this->render('coalition/edit_cause.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
