<?php

namespace AppBundle\TonMacron;

use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\TonMacronFriendMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Workflow\StateMachine;

final class InvitationProcessorHandler
{
    const SESSION_KEY = 'ton_macron.invitation';

    private $builder;
    private $manager;
    private $mailjet;
    private $stateMachine;

    public function __construct(
        TonMacronMessageBodyBuilder $builder,
        ObjectManager $manager,
        MailjetService $mailjet,
        StateMachine $stateMachine
    ) {
        $this->builder = $builder;
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->stateMachine = $stateMachine;
    }

    public function start(Session $session): InvitationProcessor
    {
        return $session->get(self::SESSION_KEY, new InvitationProcessor());
    }

    public function save(Session $session, InvitationProcessor $processor): void
    {
        $session->set(self::SESSION_KEY, $processor);
    }

    public function terminate(Session $session): void
    {
        $session->remove(self::SESSION_KEY);
    }

    public function getCurrentTransition(InvitationProcessor $processor): string
    {
        return current($this->stateMachine->getEnabledTransitions($processor))->getName();
    }

    /**
     * Returns whether the process is finished or not.
     */
    public function handle(Session $session, InvitationProcessor $processor): bool
    {
        if ($this->stateMachine->can($processor, InvitationProcessor::TRANSITION_SEND)) {
            // End process
            $invitation = TonMacronFriendInvitation::createFromProcessor($processor);

            $this->manager->persist($invitation);
            $this->manager->flush();

            $this->mailjet->sendMessage(TonMacronFriendMessage::createFromInvitation($invitation));
            $this->terminate($session);
            $this->stateMachine->apply($processor, InvitationProcessor::TRANSITION_SEND);

            return true;
        }

        // Continue processing
        $this->stateMachine->apply($processor, $this->getCurrentTransition($processor));

        if ($this->stateMachine->can($processor, InvitationProcessor::TRANSITION_SEND)) {
            $this->builder->buildMessageBody($processor);
        }

        $this->save($session, $processor);

        return false;
    }
}
