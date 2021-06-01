<?php

namespace App\DataFixtures\ORM;

use App\Committee\Feed\CommitteeEvent;
use App\Committee\Feed\CommitteeFeedManager;
use App\Committee\Feed\CommitteeMessage;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\CommitteeEvent as EntityEvent;
use App\Entity\PostAddress;
use App\Event\EventFactory;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommitteeEventData extends Fixture implements DependentFixtureInterface
{
    public const EVENT_1_UUID = '1fc69fd0-2b34-4bd4-a0cc-834480480934';
    public const EVENT_2_UUID = 'defd812f-265c-4196-bd33-72fe39e5a2a1';
    public const EVENT_3_UUID = '47e5a8bf-8be1-4c38-aae8-b41e6908a1b3';
    public const EVENT_4_UUID = '5f10be0f-184b-47b8-9e45-39b9ec46f079';
    public const EVENT_5_UUID = '24a01f4f-94ea-43eb-8601-579385c59a82';
    public const EVENT_6_UUID = '5ba0daee-d9a7-47a8-8dbb-454500284af8';
    public const EVENT_7_UUID = '00871ce7-21bd-448c-9775-a23b808e1666';
    public const EVENT_8_UUID = '113876dd-87d2-426a-a12a-60ffd5107b10';
    public const EVENT_9_UUID = '633d4cdf-c7b9-4188-ad7a-96d18e80bc09';
    public const EVENT_10_UUID = '5c2471c7-8def-4757-9bec-8e0fa24361d8';
    public const EVENT_11_UUID = 'f48c4863-dc9b-404c-8fd5-72b1c002788c';
    public const EVENT_12_UUID = '50dd58d7-f370-4874-8fbb-3746ca06d5d5';
    public const EVENT_13_UUID = '30dd58d7-c540-3874-8fbb-6b26ca06d5d5';
    public const EVENT_14_UUID = 'f0574b51-40e0-4236-a2ff-62c42cb16029';
    public const EVENT_15_UUID = 'a6709808-b3fa-40fc-95a4-da49ddc314ff';
    public const EVENT_16_UUID = '15acb775-3425-4f3a-97fb-9c7725c53bbc';
    public const EVENT_17_UUID = '84e124a7-7afd-4f63-a2a7-e18545f18e24';
    public const EVENT_18_UUID = 'c09fde77-cc05-4139-a127-f71c2702f281';
    public const EVENT_19_UUID = '67e75e81-ad27-4414-bb0b-9e0c6e12b275';
    public const EVENT_20_UUID = '65610a6c-5f18-4e9d-b4ab-0e96c0a52d9e';
    public const EVENT_21_UUID = '0e5f9f02-fa33-4c2c-a700-4235d752315b';

    private $committeeFeedManager;
    private $eventFactory;
    private $eventRegistrationFactory;

    public function __construct(
        CommitteeFeedManager $committeeFeedManager,
        EventFactory $eventFactory,
        EventRegistrationFactory $eventRegistrationFactory
    ) {
        $this->committeeFeedManager = $committeeFeedManager;
        $this->eventFactory = $eventFactory;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
    }

    public function load(ObjectManager $manager)
    {
        $eventCategory1 = $this->getReference('CE001');
        $eventCategory2 = $this->getReference('CE002');
        $eventCategory3 = $this->getReference('CE003');
        $eventCategory5 = $this->getReference('CE005');
        $eventCategory6 = $this->getReference('CE006');
        $eventCategory9 = $this->getReference('CE009');
        $eventCategory10 = $this->getReference('CE010');
        $hiddenEventCategory = $this->getReference('CE016');

        $author3 = $this->getReference('adherent-3');
        $author7 = $this->getReference('adherent-7');
        $author11 = $this->getReference('adherent-11');
        $author12 = $this->getReference('adherent-12');
        $author13 = $this->getReference('adherent-13');
        $referent75and77 = $this->getReference('adherent-19');

        $adherent4 = $this->getReference('adherent-4');
        $coordinator = $this->getReference('adherent-17');

        $committee1 = $this->getReference('committee-1');
        $committee2 = $this->getReference('committee-2');
        $committee3 = $this->getReference('committee-3');
        $committee4 = $this->getReference('committee-4');
        $committee5 = $this->getReference('committee-5');
        $committee10 = $this->getReference('committee-10');

        // Singapore
        $committee8 = $this->getReference('committee-8');

        // New York
        $committee9 = $this->getReference('committee-9');

        $eventHidden = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_21_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Événement de la catégorie masquée',
            'category' => $hiddenEventCategory,
            'description' => 'Allons à la rencontre des citoyens.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => (new Chronos('+3 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('+3 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 10,
        ]);
        $eventHidden->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $eventHidden->setPublished(true);

        $event1 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_1_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Réunion de réflexion parisienne',
            'category' => $eventCategory5,
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => (new Chronos('+3 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('+3 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 50,
            'is_for_legislatives' => true,
        ]);
        $event1->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event1->incrementParticipantsCount();

        $event2 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_2_UUID,
            'organizer' => $author7,
            'committee' => $committee3,
            'name' => 'Réunion de réflexion dammarienne',
            'category' => $eventCategory1,
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', null, 48.5182194, 2.624205),
            'begin_at' => (new Chronos('+10 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('+10 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 50,
        ]);
        $event2->incrementParticipantsCount();
        $event2->incrementParticipantsCount();

        $event3 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_3_UUID,
            'organizer' => $author7,
            'committee' => $committee4,
            'name' => 'Réunion de réflexion bellifontaine',
            'category' => $eventCategory2,
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759),
            'begin_at' => (new Chronos('tomorrow'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('tomorrow'))->format('Y-m-d').' 19:00:00',
            'capacity' => 50,
        ]);
        $event3->incrementParticipantsCount();

        $event4 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_4_UUID,
            'organizer' => $author7,
            'committee' => $committee5,
            'name' => 'Réunion de réflexion évryenne',
            'category' => $eventCategory3,
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', null, 48.624157, 2.4266),
            'begin_at' => (new Chronos('+15 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('+15 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 50,
        ]);
        $event4->incrementParticipantsCount();

        $event5 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_5_UUID,
            'organizer' => $author7,
            'committee' => $committee2,
            'name' => 'Réunion de réflexion marseillaise',
            'category' => $eventCategory6,
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('2 Place de la Major', '13002-13202', null, 43.2984913, 5.3623771),
            'begin_at' => (new Chronos('+17 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('+17 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 1,
        ]);
        $event5->incrementParticipantsCount();

        $event6 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_6_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Réunion de réflexion parisienne annulé',
            'category' => $eventCategory5,
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => (new Chronos('+60 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('+60 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 50,
        ]);
        $event6->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event6->cancel();
        $event6->incrementParticipantsCount();

        $event7 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_7_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Grand Meeting de Paris',
            'category' => $eventCategory5,
            'description' => 'Unissons nos forces pour la Présidentielle !',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => '2017-02-20 09:30:00',
            'finish_at' => '2017-02-20 19:30:00',
            'capacity' => 2000,
        ]);
        $event7->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event7->incrementParticipantsCount();

        $event8 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_8_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Grand Meeting de Marseille',
            'category' => $eventCategory5,
            'description' => 'Unissons nos forces pour la Présidentielle !',
            'address' => PostAddress::createFrenchAddress('2 Place de la Major', '13002-13202', null, 43.2984913, 5.3623771),
            'begin_at' => '2017-02-20 09:30:00',
            'finish_at' => '2017-02-20 19:00:00',
            'capacity' => 2000,
        ]);
        $event8->incrementParticipantsCount();

        $event9 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_9_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Marche Parisienne',
            'category' => $eventCategory10,
            'description' => 'Allons à la rencontre des citoyens.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => '2017-03-07 12:30:00',
            'finish_at' => '2017-03-07 17:30:00',
            'capacity' => 20,
        ]);
        $event9->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event9->incrementParticipantsCount();

        $event10 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_10_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Grand débat parisien',
            'category' => $eventCategory10,
            'description' => 'Débatons ensemble du programme.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => (new Chronos('yesterday'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('yesterday'))->format('Y-m-d').' 19:00:00',
            'capacity' => 100,
        ]);
        $event10->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event10->incrementParticipantsCount();

        $event11 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_11_UUID,
            'organizer' => $author11,
            'committee' => $committee8,
            'name' => 'Meeting de Singapour',
            'category' => $eventCategory10,
            'description' => 'Ouvert aux français de Singapour.',
            'address' => PostAddress::createForeignAddress('SG', '018956', 'Singapour', '10 Bayfront Avenue', null, 1.2835627, 103.8606872),
            'begin_at' => (new Chronos('now', new \DateTimeZone('Asia/Singapore')))->modify('-4 hours')->format('Y-m-d H:00:00'),
            'finish_at' => (new Chronos('now', new \DateTimeZone('Asia/Singapore')))->modify('-2 hours')->format('Y-m-d H:00:00'),
            'capacity' => 100,
            'time_zone' => 'Asia/Singapore',
        ]);
        $event11->incrementParticipantsCount(2);

        $event12 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_12_UUID,
            'organizer' => $author12,
            'committee' => $committee9,
            'name' => 'Meeting de New York City',
            'category' => $eventCategory10,
            'description' => 'Ouvert aux français de New York.',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            'begin_at' => (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+10 hours')->format('Y-m-d H:00:00'),
            'finish_at' => (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+13 hours')->format('Y-m-d H:00:00'),
            'capacity' => 55,
            'time_zone' => 'America/New_York',
        ]);
        $event12->incrementParticipantsCount(2);

        $event13 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_13_UUID,
            'organizer' => $author12,
            'committee' => $committee9,
            'name' => 'Meeting de Brooklyn',
            'category' => $eventCategory10,
            'description' => 'Ouvert aux français de New York.',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            'begin_at' => (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+10 hours')->format('Y-m-d H:00:00'),
            'finish_at' => (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+13 hours')->format('Y-m-d H:00:00'),
            'capacity' => 55,
            'time_zone' => 'America/New_York',
        ]);
        $event13->setPublished(false);

        $event14 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_14_UUID,
            'organizer' => null,
            'committee' => $committee9,
            'name' => 'Meeting #11 de Brooklyn',
            'category' => $eventCategory10,
            'description' => 'Ouvert aux français de New York.',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            'begin_at' => (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+10 hours')->format('Y-m-d H:00:00'),
            'finish_at' => (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+13 hours')->format('Y-m-d H:00:00'),
            'capacity' => 55,
            'time_zone' => 'America/New_York',
        ]);
        $event14->setPublished(true);

        $event15 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_15_UUID,
            'organizer' => $author13,
            'committee' => $committee10,
            'name' => 'Event of non AL',
            'category' => $eventCategory10,
            'description' => 'HAPPINESS FOR EVERYBODY, FREE, AND NO ONE WILL GO AWAY UNSATISFIED!',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '12 Pilgerweg', null, 47.321569, 8.549968799999988),
            'begin_at' => (new Chronos('yesterday'))->format('Y-m-d').' 10:00:00',
            'finish_at' => (new Chronos('yesterday'))->format('Y-m-d').' 18:00:00',
            'capacity' => 5,
            'time_zone' => 'Europe/Zurich',
        ]);
        $event15->setPublished(true);

        $event16 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_16_UUID,
            'organizer' => $referent75and77,
            'name' => 'Référent event',
            'category' => $eventCategory9,
            'description' => 'Du bonheur pour tout le monde, gratuitement, et que personne ne reparte lésé ! ',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => (new Chronos('now'))->modify('-30 minutes')->format('Y-m-d H:i:00'),
            'finish_at' => (new Chronos('now'))->format('Y-m-d').' 18:00:00',
            'capacity' => 15,
        ]);
        $event16->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event16->setPublished(true);

        $event17 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_17_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Événement à Paris 1',
            'category' => $eventCategory10,
            'description' => 'Allons à la rencontre des citoyens.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => (new Chronos('-3 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('-3 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 100,
        ]);
        $event17->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event17->setPublished(true);

        $event18 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_18_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Événement à Paris 2',
            'category' => $eventCategory1,
            'description' => 'Allons à la rencontre des citoyens.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            'begin_at' => (new Chronos('-10 days'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('-10 days'))->format('Y-m-d').' 19:00:00',
            'capacity' => 100,
        ]);
        $event18->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event18->setPublished(true);

        $event19 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_19_UUID,
            'organizer' => $author7,
            'committee' => $committee4,
            'name' => 'Événements à Fontainebleau 1',
            'category' => $eventCategory5,
            'description' => 'Allons à la rencontre des citoyens.',
            'address' => PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759),
            'begin_at' => (new Chronos('-1 month'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('-1 month'))->format('Y-m-d').' 19:00:00',
            'capacity' => 50,
        ]);
        $event19->setPublished(true);

        $event20 = $this->eventFactory->createFromArray([
            'uuid' => self::EVENT_20_UUID,
            'organizer' => $author7,
            'committee' => $committee4,
            'name' => 'Événements à Fontainebleau 2',
            'category' => $eventCategory3,
            'description' => 'Allons à la rencontre des citoyens.',
            'address' => PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759),
            'begin_at' => (new Chronos('-1 month'))->format('Y-m-d').' 09:30:00',
            'finish_at' => (new Chronos('-1 month'))->format('Y-m-d').' 19:00:00',
            'capacity' => 50,
        ]);
        $event20->setPublished(true);

        $manager->persist($eventHidden);
        $manager->persist($event1);
        $manager->persist($event2);
        $manager->persist($event3);
        $manager->persist($event4);
        $manager->persist($event5);
        $manager->persist($event6);
        $manager->persist($event7);
        $manager->persist($event8);
        $manager->persist($event9);
        $manager->persist($event10);
        $manager->persist($event11);
        $manager->persist($event12);
        $manager->persist($event13);
        $manager->persist($event14);
        $manager->persist($event15);
        $manager->persist($event16);
        $manager->persist($event17);
        $manager->persist($event18);
        $manager->persist($event19);
        $manager->persist($event20);

        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event8, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event9, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event10, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $author7)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event3, $author7)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event4, $author7)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event5, $author7)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event6, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event7, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event11, $author11)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event11, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event12, $author12)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event12, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event16, $referent75and77)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event17, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event18, $author3)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event19, $author7)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event20, $author7)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event20, $adherent4)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event20, $coordinator)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $adherent4)));
        // Registrations of not connected users
        $eventRegistration1 = new EventRegistrationCommand($event10);
        $eventRegistration1->setFirstName('Marie');
        $eventRegistration1->setLastName('CLAIRE');
        $eventRegistration1->setEmailAddress('marie.claire@test.com');
        $eventRegistration2 = new EventRegistrationCommand($event10);
        $eventRegistration2->setFirstName('Pierre');
        $eventRegistration2->setLastName('FRANCE');
        $eventRegistration2->setEmailAddress('pierre.france@test.com');
        $eventRegistration3 = new EventRegistrationCommand($event16);
        $eventRegistration3->setFirstName('Jean');
        $eventRegistration3->setLastName('PIERRE');
        $eventRegistration3->setEmailAddress('jean.pierre@test.com');
        $manager->persist($this->eventRegistrationFactory->createFromCommand($eventRegistration1));
        $manager->persist($this->eventRegistrationFactory->createFromCommand($eventRegistration2));
        $manager->persist($this->eventRegistrationFactory->createFromCommand($eventRegistration3));

        $manager->flush();

        foreach ($this->getCommitteeMessageData($committee1) as $data) {
            $this->publishCommitteeMessage($committee1, $author3, $data['subject'], $data['text'], $data['created_at']);
        }

        foreach ($this->getCommitteeMessageData($committee3) as $data) {
            $this->publishCommitteeMessage($committee3, $author3, $data['subject'], $data['text'], $data['created_at']);
        }

        for ($day = 1; $day <= 31; ++$day) {
            $this->publishCommitteeMessage($committee1, $author3, 'Foo subject', sprintf("Rapport d'activité du %u janvier 2017.", $day), sprintf('2017-01-%02u 09:00:00', $day));
        }

        for ($day = 1; $day <= 5; ++$day) {
            $this->publishCommitteeMessage($committee1, $author7, 'Foo subject', sprintf("Rapport d'activité du %u janvier 2017.", $day), sprintf('2017-01-%02u 09:00:00', $day));
        }

        $this->publishCommitteeEvent($event1);
    }

    private function publishCommitteeMessage(
        Committee $committee,
        Adherent $author,
        string $subject,
        string $text,
        string $createdAt = 'now'
    ) {
        return $this->committeeFeedManager->createMessage(
            new CommitteeMessage($author, $committee, $subject, $text, true, $createdAt)
        );
    }

    private function publishCommitteeEvent(EntityEvent $event)
    {
        return $this->committeeFeedManager->createEvent(new CommitteeEvent($event->getOrganizer(), $event));
    }

    private function getCommitteeMessageData(Committee $committee): \Generator
    {
        $uuid = (string) $committee->getUuid();

        if (LoadCommitteeData::COMMITTEE_1_UUID === $uuid) {
            yield [
                'subject' => '[Comité local] Nouveau message',
                'text' => 'Ouverture du comité !',
                'created_at' => '2017-01-12 20:13:26',
            ];
            yield [
                'subject' => '[Comité local] Nouveau message',
                'text' => "Comment ça va aujourd'hui les Marcheurs ?",
                'created_at' => '2017-01-13 08:31:12',
            ];
            yield [
                'subject' => '[Comité local] Nouveau message',
                'text' => 'Tout le monde est prêt pour le porte à porte ?',
                'created_at' => '2017-01-13 10:08:45',
            ];
            yield [
                'subject' => '[Comité local] Nouveau message',
                'text' => 'Réunion écologiste en préparation !',
                'created_at' => '2017-01-14 11:14:54',
            ];
            yield [
                'subject' => '[Comité local] Nouveau message',
                'text' => "Visite d'Émmanuel Macron le 20 janvier.",
                'created_at' => '2017-01-15 13:28:33',
            ];
        }

        if (LoadCommitteeData::COMMITTEE_3_UUID === $uuid) {
            yield [
                'subject' => '[Comité local] Nouveau message',
                'text' => 'Lancement du comité !',
                'created_at' => '2017-01-16 13:14:56',
            ];
            yield [
                'subject' => '[Comité local] Nouveau message',
                'text' => 'À la recherche de volontaires !',
                'created_at' => '2017-01-17 20:02:21',
            ];
        }
    }

    public function getDependencies()
    {
        return [
            LoadEventCategoryData::class,
            LoadCommitteeData::class,
            LoadReferentTagsZonesLinksData::class,
        ];
    }
}
