<?php

namespace App\Controller\EnMarche\Coalition;

use App\Coalition\Filter\CauseFilter;
use App\Coalition\MessageNotifier;
use App\Entity\Coalition\Cause;
use App\Form\Coalition\CauseFilterType;
use App\Form\Coalition\CauseType;
use App\Repository\Coalition\CauseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-coalition/causes", name="app_coalition_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_COALITION_MODERATOR')")
 */
class CausesController extends AbstractController
{
    /**
     * @Route("", name="causes_list", methods={"GET"})
     */
    public function list(Request $request, CauseRepository $causeRepository): Response
    {
        $filter = new CauseFilter(Cause::STATUS_PENDING);

        $form = $this->createForm(CauseFilterType::class, $filter)->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new CauseFilter(Cause::STATUS_PENDING);
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
        MessageNotifier $notifier
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
        }

        return $this->json('', Response::HTTP_OK);
    }

    /**
     * @Route("/{slug}/editer", name="cause_edit", requirements={"slug": "[A-Za-z0-9\-]+"}, methods={"GET", "POST"})
     */
    public function editAction(Request $request, Cause $cause, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CauseType::class, $cause)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('info', \sprintf('La cause "%s" a bien été modifiée.', $cause->getName()));

            return $this->redirectToRoute('app_coalition_causes_list');
        }

        return $this->render('coalition/edit_cause.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
