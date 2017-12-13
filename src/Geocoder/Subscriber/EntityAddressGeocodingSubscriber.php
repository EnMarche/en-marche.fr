<?php

namespace AppBundle\Geocoder\Subscriber;

use AppBundle\CitizenAction\CitizenActionEvent;
use AppBundle\CitizenInitiative\CitizenInitiativeUpdatedEvent;
use AppBundle\Committee\CommitteeWasUpdatedEvent;
use AppBundle\CitizenInitiative\CitizenInitiativeCreatedEvent;
use AppBundle\Event\EventUpdatedEvent;
use AppBundle\Events;
use AppBundle\Committee\CommitteeWasCreatedEvent;
use AppBundle\Event\EventCreatedEvent;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Geocoder\GeocoderInterface;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\CitizenProject\CitizenProjectWasCreatedEvent;
use AppBundle\Membership\AdherentAccountWasCreatedEvent;
use AppBundle\Membership\AdherentEvents;
use AppBundle\Membership\AdherentProfileWasUpdatedEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityAddressGeocodingSubscriber implements EventSubscriberInterface
{
    private $geocoder;
    private $manager;

    public function __construct(GeocoderInterface $geocoder, ObjectManager $manager)
    {
        $this->geocoder = $geocoder;
        $this->manager = $manager;
    }

    public function onAdherentAccountRegistrationCompleted(AdherentAccountWasCreatedEvent $event): void
    {
        $this->updateGeocodableEntity($event->getAdherent());
    }

    public function onAdherentProfileUpdated(AdherentProfileWasUpdatedEvent $event): void
    {
        $adherent = $event->getAdherent();

        if (!$adherent->getLatitude()) {
            $this->updateGeocodableEntity($adherent);
        }
    }

    public function onCommitteeCreated(CommitteeWasCreatedEvent $event): void
    {
        $this->updateGeocodableEntity($event->getCommittee());
    }

    public function onCommitteeUpdated(CommitteeWasUpdatedEvent $event): void
    {
        $this->updateGeocodableEntity($event->getCommittee());
    }

    public function onEventCreated(EventCreatedEvent $event): void
    {
        $this->updateGeocodableEntity($event->getEvent());
    }

    public function onEventUpdated(EventUpdatedEvent $event): void
    {
        $this->updateGeocodableEntity($event->getEvent());
    }

    private function updateGeocodableEntity(GeoPointInterface $geocodable): void
    {
        if ($coordinates = $this->geocode($geocodable->getGeocodableAddress())) {
            $geocodable->updateCoordinates($coordinates);
            $this->manager->flush();
        }
    }

    public function onCitizenActionCreated(CitizenActionEvent $actionEvent): void
    {
        $this->updateGeocodableEntity($actionEvent->getCitizenAction());
    }

    public function onCitizenInitiativeCreated(CitizenInitiativeCreatedEvent $initiative): void
    {
        $this->updateGeocodableEntity($initiative->getCitizenInitiative());
    }

    public function onCitizenInitiativeUpdated(CitizenInitiativeUpdatedEvent $initiative): void
    {
        $this->updateGeocodableEntity($initiative->getCitizenInitiative());
    }

    public function onCitizenProjectCreated(CitizenProjectWasCreatedEvent $event): void
    {
        $this->updateGeocodableEntity($event->getCitizenProject());
    }

    private function geocode(string $address): ?Coordinates
    {
        try {
            return $this->geocoder->geocode($address);
        } catch (GeocodingException $e) {
            // do nothing when an exception arises
            return null;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['onAdherentAccountRegistrationCompleted', -256],
            AdherentEvents::PROFILE_UPDATED => ['onAdherentProfileUpdated', -256],
            Events::COMMITTEE_CREATED => ['onCommitteeCreated', -256],
            Events::COMMITTEE_UPDATED => ['onCommitteeUpdated', -256],
            Events::EVENT_CREATED => ['onEventCreated', -256],
            Events::EVENT_UPDATED => ['onEventUpdated', -256],
            Events::CITIZEN_ACTION_CREATED => ['onCitizenActionCreated', -256],
            Events::CITIZEN_INITIATIVE_CREATED => ['onCitizenInitiativeCreated', -256],
            Events::CITIZEN_INITIATIVE_UPDATED => ['onCitizenInitiativeUpdated', -256],
            Events::CITIZEN_PROJECT_CREATED => ['onCitizenProjectCreated', -256],
        ];
    }
}
