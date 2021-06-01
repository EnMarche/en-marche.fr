<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Coalition\Coalition;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\EventCategory;
use App\Entity\PostAddress;
use App\Event\EventFactory;
use App\Event\EventRegistrationFactory;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCoalitionEventData extends Fixture implements DependentFixtureInterface
{
    public const EVENT_1_UUID = '472d1f86-6522-4122-a0f4-abd69d17bb2d';
    public const EVENT_2_UUID = '8d706ed2-0312-4c21-9923-c573005b338e';
    public const EVENT_3_UUID = '2dd3d37d-5cfb-41a1-9254-8d53b8cb9d19';
    public const EVENT_4_UUID = 'c9364c5b-e277-47b7-aa79-f5ab3fa82437';
    public const EVENT_5_UUID = '9bdc4411-8f4a-4ac7-99fe-c2e68b4fdca1';
    public const EVENT_6_UUID = '2f36a0b9-ac1d-4bee-b9ef-525bc89a7c8e';
    public const EVENT_7_UUID = '462d7faf-09d2-4679-989e-287929f50be8';
    public const EVENT_8_UUID = '4e805f54-4af6-40f8-91f9-d133407289b3';
    public const EVENT_9_UUID = 'b40e5c7d-e1b9-4dfa-b8a3-84704d1d73c9';
    public const EVENT_10_UUID = '0c64936a-bb76-4e75-a729-2b7f72d530e6';
    public const EVENT_11_UUID = 'd9a48ca4-385c-4cb0-bf93-8ee4ee68fe46';
    public const EVENT_12_UUID = 'd7e72e52-b81a-4adf-b022-d547672ce095';
    public const EVENT_13_UUID = 'de7f027c-f6c3-439f-b1dd-bf2b110a0fb0';
    public const EVENT_14_UUID = 'aa2b835e-0944-45bb-b244-068b469c013e';
    public const EVENT_15_UUID = 'd16f0ab4-292b-4698-847c-005f58ec3119';
    public const EVENT_16_UUID = 'a9d45d86-0333-4767-9853-6e9e7268d778';
    public const EVENT_17_UUID = '0eae5f3f-86f7-4750-9f37-a5f8f47d67f4';
    public const EVENT_18_UUID = '67b6775b-195d-4c15-9c62-8e280fa389f1';
    public const EVENT_19_UUID = '9119c140-4251-48f2-8a27-eefc413bea0a';
    public const EVENT_20_UUID = 'ed5791e9-5ae5-4c5b-934b-9cdb1fb30c58';

    private $eventFactory;
    private $eventRegistrationFactory;

    public function __construct(EventFactory $eventFactory, EventRegistrationFactory $eventRegistrationFactory)
    {
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

        $adherent3 = $this->getReference('adherent-3');
        $adherent7 = $this->getReference('adherent-7');
        $adherent11 = $this->getReference('adherent-11');
        $adherent12 = $this->getReference('adherent-12');
        $adherent13 = $this->getReference('adherent-13');
        $referent75and77 = $this->getReference('adherent-19');

        $coalitionCulture = $this->getReference('coalition-culture');
        $coalitionEconomie = $this->getReference('coalition-économie');
        $coalitionEurope = $this->getReference('coalition-europe');
        $coalitionInternational = $this->getReference('coalition-international');
        $coalitionNumerique = $this->getReference('coalition-numérique');

        $eventCulture1 = $this->createEvent(
            self::EVENT_1_UUID,
            $adherent3,
            $coalitionCulture,
            $eventCategory5,
            'Événement culturel 1',
            'Nous allons échanger autour de différents sujets',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+5 days'))->format('Y-m-d').' 10:30:00',
            (new Chronos('+5 days'))->format('Y-m-d').' 18:00:00'
        );

        $eventCulture2 = $this->createEvent(
            self::EVENT_7_UUID,
            $adherent3,
            $coalitionCulture,
            $eventCategory5,
            'Événement culturel 2',
            'Nous allons échanger encore autour de différents sujets culturels',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+3 days'))->format('Y-m-d').' 09:30:00',
            (new Chronos('+3 days'))->format('Y-m-d').' 19:30:00'
        );

        $eventCulture3 = $this->createEvent(
            self::EVENT_12_UUID,
            $adherent12,
            $coalitionCulture,
            $eventCategory5,
            'Événement culturel 3',
            'Nous allons échanger encore autour de différents sujets culturels',
            PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            (new Chronos('+1 days'))->format('Y-m-d').' 10:00:00',
            (new Chronos('+1 days'))->format('Y-m-d').' 18:00:00'
        );

        $eventCulture4 = $this->createEvent(
            self::EVENT_14_UUID,
            $adherent12,
            $coalitionCulture,
            $eventCategory5,
            'Événement culturel 4',
            'Description d\'un événement culturel',
            PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+10 hours')->format('Y-m-d H:00:00'),
            (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+13 hours')->format('Y-m-d H:00:00')
        );

        $eventCulture5 = $this->createEvent(
            self::EVENT_15_UUID,
            $adherent13,
            $coalitionCulture,
            $eventCategory10,
            'Événement culturel 5',
            'HAPPINESS FOR EVERYBODY, FREE, AND NO ONE WILL GO AWAY UNSATISFIED!',
            PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '12 Pilgerweg', null, 47.321569, 8.549968799999988),
            (new Chronos('+2 days'))->format('Y-m-d').' 10:00:00',
            (new Chronos('+2 days'))->format('Y-m-d').' 18:00:00'
        );

        $eventCulture6 = $this->createEvent(
            self::EVENT_16_UUID,
            $referent75and77,
            $coalitionCulture,
            $eventCategory9,
            'Événement culturel 6',
            'Du bonheur pour tout le monde, gratuitement, et que personne ne reparte lésé ! ',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('now'))->modify('+30 minutes')->format('Y-m-d H:i:00'),
            (new Chronos('now'))->modify('+3 hours')->format('Y-m-d H:i:00'),
        );

        $eventCultureCancelled = $this->createEvent(
            self::EVENT_6_UUID,
            $adherent3,
            $coalitionCulture,
            $eventCategory5,
            'Événement culturel annulé',
            'Cet événement est annulé',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+30 days'))->format('Y-m-d').' 09:30:00',
            (new Chronos('+30 days'))->format('Y-m-d').' 19:00:00'
        );
        $eventCultureCancelled->cancel();

        $eventCultureFinished = $this->createEvent(
            self::EVENT_8_UUID,
            $adherent3,
            $coalitionCulture,
            $eventCategory5,
            'Événement culturel passé',
            'Cet événement est passé',
            PostAddress::createFrenchAddress('2 Place de la Major', '13002-13202', null, 43.2984913, 5.3623771),
            '2021-02-10 09:30:00',
            '2021-02-10 19:00:00'
        );

        $eventCultureNotPublished = $this->createEvent(
            self::EVENT_11_UUID,
            $adherent11,
            $coalitionCulture,
            $eventCategory5,
            'Événement culturel non publié',
            'Cet événement n\'est pas publié',
            PostAddress::createForeignAddress('SG', '018956', 'Singapour', '10 Bayfront Avenue', null, 1.2835627, 103.8606872),
            (new Chronos('now', new \DateTimeZone('Asia/Singapore')))->modify('-4 hours')->format('Y-m-d H:00:00'),
            (new Chronos('now', new \DateTimeZone('Asia/Singapore')))->modify('-2 hours')->format('Y-m-d H:00:00')
        );
        $eventCultureNotPublished->setPublished(false);

        $eventEurope1 = $this->createEvent(
            self::EVENT_2_UUID,
            $adherent7,
            $coalitionEurope,
            $eventCategory1,
            'Événement Europe 1',
            'Nous allons échanger autour de différents sujets concenant l\'Europe',
            PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', null, 48.5182194, 2.624205),
            (new Chronos('+11 days'))->format('Y-m-d').' 10:30:00',
            (new Chronos('+11 days'))->format('Y-m-d').' 18:00:00'
        );

        $eventInternational1 = $this->createEvent(
            self::EVENT_3_UUID,
            $adherent7,
            $coalitionInternational,
            $eventCategory2,
            'Événement international',
            'Nous allons échanger autour de différents sujets internationals',
            PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759),
            (new Chronos('tomorrow'))->format('Y-m-d').' 10:30:00',
            (new Chronos('tomorrow'))->format('Y-m-d').' 18:00:00'
        );

        $eventNumerique1 = $this->createEvent(
            self::EVENT_4_UUID,
            $adherent7,
            $coalitionNumerique,
            $eventCategory3,
            'Événement numérique',
            'Nous allons échanger autour de différents sujets numériques',
            PostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', null, 48.624157, 2.4266),
            (new Chronos('+16 days'))->format('Y-m-d').' 09:30:00',
            (new Chronos('+16 days'))->format('Y-m-d').' 19:00:00'
        );

        $eventEconomie1 = $this->createEvent(
            self::EVENT_5_UUID,
            $adherent7,
            $coalitionEconomie,
            $eventCategory6,
            'Événement économique',
            'Nous allons échanger autour de différents sujets économiques',
            PostAddress::createFrenchAddress('2 Place de la Major', '13002-13202', null, 43.2984913, 5.3623771),
            (new Chronos('+17 days'))->format('Y-m-d').' 09:30:00',
            (new Chronos('+17 days'))->format('Y-m-d').' 19:00:00'
        );

        $eventNumeriqueNotPublished = $this->createEvent(
            self::EVENT_13_UUID,
            $adherent12,
            $coalitionNumerique,
            $eventCategory3,
            'Événement numérique non publié',
            'Événement non publié',
            PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+10 hours')->format('Y-m-d H:00:00'),
            (new Chronos('now', new \DateTimeZone('America/New_York')))->modify('+13 hours')->format('Y-m-d H:00:00')
        );
        $eventNumeriqueNotPublished->setPublished(false);

        $manager->persist($eventCulture1);
        $manager->persist($eventCulture2);
        $manager->persist($eventCulture3);
        $manager->persist($eventCulture4);
        $manager->persist($eventCulture5);
        $manager->persist($eventCulture6);
        $manager->persist($eventCultureCancelled);
        $manager->persist($eventCultureFinished);
        $manager->persist($eventCultureNotPublished);
        $manager->persist($eventEurope1);
        $manager->persist($eventInternational1);
        $manager->persist($eventNumerique1);
        $manager->persist($eventEconomie1);
        $manager->persist($eventNumeriqueNotPublished);

        $manager->flush();
    }

    private function createEvent(
        string $uuid,
        Adherent $organizer,
        Coalition $coalition,
        EventCategory $category,
        string $name,
        string $description,
        PostAddress $postAddress,
        string $beginAt,
        string $finishAt
    ): CoalitionEvent {
        $event = new CoalitionEvent(Uuid::fromString($uuid));
        $event->setOrganizer($organizer);
        $event->addCoalition($coalition);
        $event->setCategory($category);
        $event->setName($name);
        $event->setDescription($description);
        $event->setPostAddress($postAddress);
        $event->setBeginAt(new \DateTime($beginAt));
        $event->setFinishAt(new \DateTime($finishAt));

        return $event;
    }

    public function getDependencies()
    {
        return [
            LoadEventCategoryData::class,
            LoadCoalitionData::class,
            LoadReferentTagsZonesLinksData::class,
        ];
    }
}
