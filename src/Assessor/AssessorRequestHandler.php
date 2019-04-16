<?php

namespace AppBundle\Assessor;

use AppBundle\Repository\VotePlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\StateMachine;

final class AssessorRequestHandler
{
    public const SESSION_KEY = 'assessor.request';

    private $stateMachine;
    private $manager;
    private $votePlaceRepository;
    private $assessorRequestFactory;
    private $session;

    public function __construct(
        StateMachine $stateMachine,
        EntityManagerInterface $manager,
        VotePlaceRepository $votePlaceRepository,
        AssessorRequestFactory $assessorRequestFactory,
        SessionInterface $session
    ) {
        $this->stateMachine = $stateMachine;
        $this->manager = $manager;
        $this->votePlaceRepository = $votePlaceRepository;
        $this->assessorRequestFactory = $assessorRequestFactory;
        $this->session = $session;
    }

    public function handle(AssessorRequestCommand $assessorRequestCommand): bool
    {
        if ($this->stateMachine->can($assessorRequestCommand, AssessorRequestEnum::TRANSITION_VALID_SUMMARY)) {
            $assessorRequest = $this->assessorRequestFactory->createFromCommand($assessorRequestCommand);

            $this->manager->persist($assessorRequest);
            $this->manager->flush();

            $this->terminate();
            $this->stateMachine->apply($assessorRequestCommand, AssessorRequestEnum::TRANSITION_VALID_SUMMARY);

            return true;
        }

        $this->stateMachine->apply($assessorRequestCommand, $this->getCurrentTransition($assessorRequestCommand));
        $this->save($assessorRequestCommand);

        return false;
    }

    public function start(string $recaptcha = ''): AssessorRequestCommand
    {
        /** @var AssessorRequestCommand $assessorRequest */
        $assessorRequest = $this->session->get(self::SESSION_KEY, new AssessorRequestCommand());

        if ($recaptcha) {
            $assessorRequest->setRecaptcha($recaptcha);
        }

        return $assessorRequest;
    }

    public function save(AssessorRequestCommand $assessorRequestCommand): void
    {
        $this->session->set(self::SESSION_KEY, $assessorRequestCommand);
    }

    public function terminate(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }

    public function back(): void
    {
        $assessorRequest = $this->session->get(self::SESSION_KEY);

        $this->stateMachine->apply($assessorRequest, $this->getBackTransition($assessorRequest));
        $this->save($assessorRequest);
    }

    public function getCurrentTransition(AssessorRequestCommand $assessorRequestCommand): string
    {
        return current($this->stateMachine->getEnabledTransitions($assessorRequestCommand))->getName();
    }

    public function getBackTransition(AssessorRequestCommand $assessorRequestCommand): string
    {
        $availableTransitions = $this->stateMachine->getEnabledTransitions($assessorRequestCommand);

        return end($availableTransitions)->getName();
    }

    public function getVotePlaceWishesLabels(AssessorRequestCommand $assessorRequestCommand): ?array
    {
        if ($this->stateMachine->can($assessorRequestCommand, AssessorRequestEnum::TRANSITION_VALID_SUMMARY)) {
            return $this->votePlaceRepository->findVotePlaceNameByIds(
                $assessorRequestCommand->getVotePlaceWishes()
            );
        }

        return null;
    }
}
