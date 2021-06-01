<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Coalition\Cause;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\EventCategory;
use App\Entity\PostAddress;
use App\Event\EventFactory;
use App\Event\EventRegistrationFactory;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCauseEventData extends Fixture implements DependentFixtureInterface
{
    public const EVENT_1_UUID = 'ef62870c-6d42-47b6-91ea-f454d473adf8';
    public const EVENT_2_UUID = '19242011-7fbe-47b7-b459-0ca724d4fca2';
    public const EVENT_3_UUID = '32669ec6-dbc1-4526-92af-ad50925e23d6';
    public const EVENT_4_UUID = '8047158c-8a3b-4c30-86fe-5e0148567051';
    public const EVENT_5_UUID = 'efad6d3b-52b2-4ef1-adfc-210733b1607a';
    public const EVENT_6_UUID = 'b0b53da1-cb0f-4387-9214-bb1f13ce3ee2';
    public const EVENT_7_UUID = '7773cccb-e5ac-425f-b653-2222a34445bb';

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
        $eventCategory5 = $this->getReference('CE005');

        $adherent3 = $this->getReference('adherent-3');
        $adherent7 = $this->getReference('adherent-7');
        $adherent11 = $this->getReference('adherent-11');
        $adherent12 = $this->getReference('adherent-12');

        $causeCulture1 = $this->getReference('cause-culture-1');
        $causeEducation1 = $this->getReference('cause-education-1');

        $eventCulture1 = $this->createEvent(
            self::EVENT_1_UUID,
            $adherent3,
            $causeCulture1,
            $eventCategory5,
            'Événement culturel 1 de la cause culturelle 1',
            'C\'est un événement culturel de la cause',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+6 days'))->format('Y-m-d').' 09:00:00',
            (new Chronos('+6 days'))->format('Y-m-d').' 18:00:00'
        );

        $eventCulture2 = $this->createEvent(
            self::EVENT_2_UUID,
            $adherent3,
            $causeCulture1,
            $eventCategory5,
            'Événement culturel 2 de la cause culturelle 1',
            'Un autre événement culturel',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+4 days'))->format('Y-m-d').' 09:00:00',
            (new Chronos('+4 days'))->format('Y-m-d').' 19:00:00'
        );

        $eventCulture3 = $this->createEvent(
            self::EVENT_3_UUID,
            $adherent12,
            $causeCulture1,
            $eventCategory5,
            'Événement culturel 3 de la cause culturelle 1',
            'Nous allons échanger encore autour de différents sujets culturels',
            PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            (new Chronos('+2 days'))->format('Y-m-d').' 10:00:00',
            (new Chronos('+2 days'))->format('Y-m-d').' 18:00:00'
        );

        $eventCultureCancelled = $this->createEvent(
            self::EVENT_4_UUID,
            $adherent3,
            $causeCulture1,
            $eventCategory5,
            'Événement culturel annulé de la cause',
            'Cet événement de la cause est annulé',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+20 days'))->format('Y-m-d').' 10:30:00',
            (new Chronos('+20 days'))->format('Y-m-d').' 19:00:00'
        );
        $eventCultureCancelled->cancel();

        $eventCultureFinished = $this->createEvent(
            self::EVENT_5_UUID,
            $adherent3,
            $causeCulture1,
            $eventCategory5,
            'Événement culturel passé de la cause',
            'Cet événement de la cause est passé',
            PostAddress::createFrenchAddress('2 Place de la Major', '13002-13202', null, 43.2984913, 5.3623771),
            '2021-02-10 09:30:00',
            '2021-02-10 19:00:00'
        );

        $eventCultureNotPublished = $this->createEvent(
            self::EVENT_6_UUID,
            $adherent11,
            $causeCulture1,
            $eventCategory5,
            'Événement culturel non publié de la cause',
            'Cet événement de la cause n\'est pas publié',
            PostAddress::createForeignAddress('SG', '018956', 'Singapour', '10 Bayfront Avenue', null, 1.2835627, 103.8606872),
            (new Chronos('now', new \DateTimeZone('Asia/Singapore')))->modify('-4 hours')->format('Y-m-d H:00:00'),
            (new Chronos('now', new \DateTimeZone('Asia/Singapore')))->modify('-2 hours')->format('Y-m-d H:00:00')
        );
        $eventCultureNotPublished->setPublished(false);

        $eventEducation1 = $this->createEvent(
            self::EVENT_7_UUID,
            $adherent7,
            $causeEducation1,
            $eventCategory1,
            'Événement de l\'éducation 1 de la cause',
            'C\'est un événement de l\'éducation de la cause',
            PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', null, 48.5182194, 2.624205),
            (new Chronos('+10 days'))->format('Y-m-d').' 10:30:00',
            (new Chronos('+10 days'))->format('Y-m-d').' 18:00:00'
        );

        $manager->persist($eventCulture1);
        $manager->persist($eventCulture2);
        $manager->persist($eventCulture3);
        $manager->persist($eventCultureCancelled);
        $manager->persist($eventCultureFinished);
        $manager->persist($eventCultureNotPublished);
        $manager->persist($eventEducation1);

        $manager->flush();
    }

    private function createEvent(
        string $uuid,
        Adherent $organizer,
        Cause $cause,
        EventCategory $category,
        string $name,
        string $description,
        PostAddress $postAddress,
        string $beginAt,
        string $finishAt
    ): CauseEvent {
        $event = new CauseEvent(Uuid::fromString($uuid));
        $event->setOrganizer($organizer);
        $event->addCause($cause);
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
            LoadCauseData::class,
            LoadReferentTagsZonesLinksData::class,
        ];
    }
}
