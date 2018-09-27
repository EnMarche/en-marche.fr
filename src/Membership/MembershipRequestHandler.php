<?php

namespace AppBundle\Membership;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\History\EmailSubscriptionHistoryHandler;
use AppBundle\Mail\Transactional\AdherentAccountConfirmationMail;
use AppBundle\Mail\Transactional\AdherentTerminateMembershipMail;
use AppBundle\Mail\Transactional\AdherentAccountActivationMail;
use AppBundle\Mail\Transactional\AdherentAccountActivationReminderMail;
use AppBundle\OAuth\CallbackManager;
use AppBundle\Referent\ReferentTagManager;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $addressFactory;
    private $callbackManager;
    private $mailPost;
    private $manager;
    private $adherentRegistry;
    private $referentTagManager;
    private $membershipRegistrationProcess;
    private $emailSubscriptionHistoryHandler;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        CallbackManager $callbackManager,
        MailPostInterface $mailPost,
        ObjectManager $manager,
        AdherentRegistry $adherentRegistry,
        ReferentTagManager $referentTagManager,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->callbackManager = $callbackManager;
        $this->mailPost = $mailPost;
        $this->manager = $manager;
        $this->adherentRegistry = $adherentRegistry;
        $this->referentTagManager = $referentTagManager;
        $this->membershipRegistrationProcess = $membershipRegistrationProcess;
        $this->emailSubscriptionHistoryHandler = $emailSubscriptionHistoryHandler;
    }

    public function registerAsUser(MembershipRequest $membershipRequest): Adherent
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $this->manager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);

        $this->sendEmailValidation($adherent);

        $this->dispatcher->dispatch(
            UserEvents::USER_CREATED,
            new UserEvent(
                $adherent,
                $membershipRequest->getAllowNotifications(),
                false
            )
        );
        $this->emailSubscriptionHistoryHandler->handleSubscriptions($adherent);

        return $adherent;
    }

    public function sendEmailValidation(Adherent $adherent, bool $isReminder = false): bool
    {
        $token = AdherentActivationToken::generate($adherent);

        $this->manager->persist($token);
        $this->manager->flush();

        $activationUrl = $this->generateMembershipActivationUrl($adherent, $token);

        /** @var AdherentAccountActivationMail|AdherentAccountActivationReminderMail $mailClass */
        $mailClass = $isReminder ? AdherentAccountActivationReminderMail::class : AdherentAccountActivationMail::class;

        $this->mailPost->address(
            $mailClass,
            $mailClass::createRecipientFor($adherent, $activationUrl)
        );

        return true;
    }

    public function registerAsAdherent(MembershipRequest $membershipRequest): void
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $this->manager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);

        $this->membershipRegistrationProcess->start($adherent->getUuid()->toString());

        $adherent->join();
        $this->sendEmailValidation($adherent);

        $this->dispatcher->dispatch(
            UserEvents::USER_CREATED,
            new UserEvent(
                $adherent,
                $membershipRequest->getAllowNotifications(),
                $membershipRequest->getAllowNotifications()
            )
        );

        $this->emailSubscriptionHistoryHandler->handleSubscriptions($adherent);

        $this->dispatcher->dispatch(AdherentEvents::REGISTRATION_COMPLETED, new AdherentAccountWasCreatedEvent($adherent, $membershipRequest));
    }

    public function join(Adherent $user, MembershipRequest $membershipRequest): void
    {
        $user->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));
        $user->join();

        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($user);

        $this->manager->flush();

        $this->sendConfirmationJoinMessage($user);

        $this->dispatcher->dispatch(AdherentEvents::REGISTRATION_COMPLETED, new AdherentAccountWasCreatedEvent($user, $membershipRequest));
        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($user));
    }

    public function sendConfirmationJoinMessage(Adherent $adherent): void
    {
        $this->mailPost->address(
            AdherentAccountConfirmationMail::class,
            AdherentAccountConfirmationMail::createRecipientFor($adherent)
        );
    }

    public function update(Adherent $adherent, MembershipRequest $membershipRequest): void
    {
        $adherent->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));

        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($adherent);

        $this->dispatcher->dispatch(AdherentEvents::PROFILE_UPDATED, new AdherentProfileWasUpdatedEvent($adherent));
        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($adherent));

        $this->manager->flush();
    }

    /**
     * /!\ Only relevant for update not for creation.
     */
    private function updateReferentTagsAndSubscriptionHistoryIfNeeded(Adherent $adherent): void
    {
        if ($this->referentTagManager->isUpdateNeeded($adherent)) {
            $oldReferentTags = $adherent->getReferentTags()->toArray();
            $this->referentTagManager->assignReferentLocalTags($adherent);
            $this->emailSubscriptionHistoryHandler->handleReferentTagsUpdate($adherent, $oldReferentTags);
        }
    }

    private function generateMembershipActivationUrl(Adherent $adherent, AdherentActivationToken $token): string
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'activation_token' => (string) $token->getValue(),
        ];

        return $this->callbackManager->generateUrl('app_membership_activate', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function terminateMembership(UnregistrationCommand $command, Adherent $adherent): void
    {
        $unregistrationFactory = new UnregistrationFactory();
        $unregistration = $unregistrationFactory->createFromUnregistrationCommandAndAdherent($command, $adherent);

        $this->adherentRegistry->unregister($adherent, $unregistration);

        $this->mailPost->address(
            AdherentTerminateMembershipMail::class,
            AdherentTerminateMembershipMail::createRecipientFor($adherent)
        );

        $this->dispatcher->dispatch(UserEvents::USER_DELETED, new UserEvent($adherent));
    }
}
