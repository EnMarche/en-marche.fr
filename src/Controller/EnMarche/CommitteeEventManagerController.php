<?php

namespace App\Controller\EnMarche;

use App\Controller\PrintControllerTrait;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\EventRegistration;
use App\Event\EventCanceledHandler;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Event\EventContactMembersCommand;
use App\Event\EventContactMembersCommandHandler;
use App\Event\EventRegistrationExporter;
use App\Exception\BadUuidRequestException;
use App\Exception\InvalidUuidException;
use App\Form\ContactMembersType;
use App\Form\EventCommandType;
use App\Repository\EventRegistrationRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\SnappyResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/evenements/{slug}")
 * @Security("is_granted('HOST_EVENT', event)")
 */
class CommitteeEventManagerController extends AbstractController
{
    use PrintControllerTrait;

    private const ACTION_CONTACT = 'contact';
    private const ACTION_EXPORT = 'export';
    private const ACTION_PRINT = 'print';
    private const ACTIONS = [
        self::ACTION_CONTACT,
        self::ACTION_EXPORT,
        self::ACTION_PRINT,
    ];

    private $eventRegistrationRepository;

    public function __construct(EventRegistrationRepository $eventRegistrationRepository)
    {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    /**
     * @Route("/modifier", name="app_committee_event_edit", methods={"GET", "POST"})
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function editAction(Request $request, CommitteeEvent $event, EventCommandHandler $handler): Response
    {
        $form = $this->createForm(
            EventCommandType::class,
            $command = EventCommand::createFromEvent($event),
            [
                'image_path' => $event->getImagePath(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handleUpdate($event, $command);
            $this->addFlash('info', 'event.update.success');

            return $this->redirectToRoute('app_committee_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annuler", name="app_committee_event_cancel", methods={"GET", "POST"})
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function cancelAction(
        Request $request,
        CommitteeEvent $event,
        EventCanceledHandler $eventCanceledHandler
    ): Response {
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventCanceledHandler->handle($event);
            $this->addFlash('info', 'event.cancel.success');

            return $this->redirectToRoute('app_committee_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/cancel.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscrits", name="app_committee_event_members", methods={"GET"})
     */
    public function membersAction(BaseEvent $event): Response
    {
        return $this->render('events/members.html.twig', [
            'event' => $event,
            'registrations' => $this->eventRegistrationRepository->findByEvent($event),
        ]);
    }

    /**
     * @Route("/inscrits/exporter", name="app_committee_event_export_members", methods={"POST"})
     */
    public function exportMembersAction(
        Request $request,
        BaseEvent $event,
        EventRegistrationExporter $exporter
    ): Response {
        $registrations = $this->getRegistrations($request, $event, self::ACTION_EXPORT);

        if (!$registrations) {
            return $this->redirectToRoute('app_committee_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $exported = $exporter->export($registrations);

        return new SnappyResponse($exported, 'inscrits-a-l-evenement.csv', 'text/csv');
    }

    /**
     * @Route("/inscrits/contacter", name="app_committee_event_contact_members", methods={"POST"})
     */
    public function contactMembersAction(
        Request $request,
        BaseEvent $event,
        EventContactMembersCommandHandler $handler
    ): Response {
        if ($event->isCoalitionsEvent()) {
            throw $this->createNotFoundException();
        }

        $registrations = $this->getRegistrations($request, $event, self::ACTION_CONTACT);

        if (!$registrations) {
            return $this->redirectToRoute('app_committee_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $command = new EventContactMembersCommand($registrations, $this->getUser());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'event.contact_members'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($command);
            $this->addFlash('info', 'committee.event.contact.success');

            return $this->redirectToRoute('app_committee_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $uuids = array_map(function (EventRegistration $registration) {
            return $registration->getUuid()->toString();
        }, $registrations);

        return $this->render('events/contact_members.html.twig', [
            'event' => $event,
            'contacts' => $uuids,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscrits/imprimer", name="app_committee_event_print_members", methods={"POST"})
     */
    public function printMembersAction(Request $request, BaseEvent $event): Response
    {
        $registrations = $this->getRegistrations($request, $event, self::ACTION_PRINT);

        if (!$registrations) {
            return $this->redirectToRoute('app_committee_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->getPdfResponse(
            'events/print_members.html.twig',
            [
                'registrations' => $registrations,
            ],
            'Liste des participants.pdf'
        );
    }

    private function getRegistrations(Request $request, BaseEvent $event, string $action): array
    {
        if (!\in_array($action, self::ACTIONS)) {
            throw new \InvalidArgumentException("Action '$action' is not allowed.");
        }

        if (!$this->isCsrfTokenValid(sprintf('event.%s_members', $action), $request->request->get('token'))) {
            throw $this->createAccessDeniedException("Invalid CSRF protection token to $action members.");
        }

        if (!$uuids = json_decode($request->request->get(sprintf('%ss', $action)), true)) {
            if (self::ACTION_CONTACT === $action) {
                $this->addFlash('info', 'committee.event.contact.none');
            }

            return [];
        }

        try {
            $registrations = $this->eventRegistrationRepository->findByEventAndUuid($event, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        return $registrations;
    }
}
