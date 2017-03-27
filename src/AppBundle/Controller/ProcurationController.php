<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Form\ProcurationProfileType;
use AppBundle\Form\ProcurationElectionsType;
use AppBundle\Form\ProcurationProxyType;
use AppBundle\Form\ProcurationVoteType;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Procuration\ProcurationRequestFlow;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/procuration")
 */
class ProcurationController extends Controller
{
    /**
     * @Route("", name="app_procuration_index")
     * @Method("GET")
     */
    public function indexAction(Request $request): Response
    {
        $this->getProcurationFlow()->reset();

        return $this->render('procuration/index.html.twig', [
            'has_error' => $request->query->getBoolean('has_error'),
            'form' => $this->createForm(ProcurationVoteType::class, new ProcurationRequest())->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/mon-lieu-de-vote", name="app_procuration_request_vote")
     * @Method("GET|POST")
     */
    public function voteAction(Request $request): Response
    {
        $command = $this->getProcurationFlow()->getCurrentModel();

        $form = $this->createForm(ProcurationVoteType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getProcurationFlow()->save($command);

            return $this->redirectToRoute('app_procuration_request_profile');
        }

        return $this->render('procuration/vote.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/mes-coordonnees", name="app_procuration_request_profile")
     * @Method("GET|POST")
     */
    public function profileAction(Request $request): Response
    {
        $command = $this->getProcurationFlow()->getCurrentModel();

        if ($this->getUser() instanceof Adherent) {
            $command->importAdherentData($this->getUser());
        }

        $form = $this->createForm(ProcurationProfileType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getProcurationFlow()->save($command);

            return $this->redirectToRoute('app_procuration_request_elections');
        }

        return $this->render('procuration/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/ma-procuration", name="app_procuration_request_elections")
     * @Method("GET|POST")
     */
    public function electionsAction(Request $request): Response
    {
        $command = $this->getProcurationFlow()->getCurrentModel();
        $command->recaptcha = (string) $request->request->get('g-recaptcha-response');

        $form = $this->createForm(ProcurationElectionsType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($command);
            $manager->flush();

            $this->getProcurationFlow()->reset();

            return $this->redirectToRoute('app_procuration_request_thanks');
        }

        return $this->render('procuration/elections.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/merci", name="app_procuration_request_thanks")
     * @Method("GET")
     */
    public function requestThanksAction(): Response
    {
        return $this->render('procuration/thanks.html.twig');
    }

    /**
     * @Route("/je-propose", name="app_procuration_proxy_proposal")
     * @Method("GET|POST")
     */
    public function proxyProposalAction(Request $request): Response
    {
        $referentUuid = $request->query->get('uuid');

        if (!$referentUuid || !Uuid::isValid($referentUuid)) {
            throw $this->createNotFoundException();
        }

        $referent = $this->getDoctrine()->getRepository(Adherent::class)->findOneBy(['uuid' => $referentUuid]);

        if (!$referent || (!$referent->isReferent() && !$referent->isProcurationManager())) {
            throw $this->createNotFoundException();
        }

        $proposal = new ProcurationProxy($referent);
        $proposal->recaptcha = (string) $request->request->get('g-recaptcha-response');

        if ($this->getUser() instanceof Adherent) {
            $proposal->importAdherentData($this->getUser());
        }

        $form = $this->createForm(ProcurationProxyType::class, $proposal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($proposal);
            $manager->flush();

            return $this->redirectToRoute('app_procuration_proposal_thanks', [
                'uuid' => $referentUuid,
            ]);
        }

        return $this->render('procuration/proposal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-propose/merci", name="app_procuration_proposal_thanks")
     * @Method("GET")
     */
    public function proposalThanksAction(Request $request): Response
    {
        return $this->render('procuration/proposal_thanks.html.twig', [
            'uuid' => (string) $request->query->get('uuid'),
        ]);
    }

    /**
     * @Route("/ma-demande/{id}/{token}", name="app_procuration_my_request")
     * @Method("GET")
     */
    public function myRequestAction(ProcurationRequest $request, string $token): Response
    {
        if (!$request->isProcessed() || !$request->hasFoundProxy() || $token !== $request->generatePrivateToken()) {
            throw $this->createNotFoundException();
        }

        return $this->render('procuration/my_request.html.twig', [
            'request' => $request,
            'proxy' => $request->getFoundProxy(),
        ]);
    }

    private function getProcurationFlow(): ProcurationRequestFlow
    {
        return $this->get('app.procuration.request_flow');
    }
}
