<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CauseFollower;
use App\Entity\Coalition\Coalition;
use App\Entity\FollowerInterface;
use App\Entity\Geo\Zone;
use App\Image\ImageManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCauseData extends Fixture implements DependentFixtureInterface
{
    public const CAUSE_1_UUID = '55056e7c-2b5f-4ef6-880e-cde0511f79b2';
    public const CAUSE_2_UUID = '017491f9-1953-482e-b491-20418235af1f';
    public const CAUSE_3_UUID = '5f8a6d40-9e69-4311-a45b-67c00d30ad41';
    public const CAUSE_4_UUID = 'fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8';
    public const CAUSE_5_UUID = '44249b1d-ea10-41e0-b288-5eb74fa886ba';
    public const CAUSE_6_UUID = '13814069-1dd2-11b2-98d6-2fdf8179626a';
    public const CAUSE_7_UUID = '253b0ed7-7426-15f8-97f9-2bb32d0a4d17';

    private $imageManager;

    public function __construct(ImageManagerInterface $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        $carl = $this->getReference('adherent-2');
        $jacques = $this->getReference('adherent-3');
        $referent = $this->getReference('adherent-8');

        $causeCulture1 = $this->createCause(
            self::CAUSE_1_UUID,
            'Cause pour la culture',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-culture'),
            $this->getReference('adherent-1'),
            '-1 hour',
            5,
            true
        );
        $causeCulture1->addFollower($causeCulture1->createFollower($jacques));
        $causeCulture1->addFollower($causeCulture1->createFollower($carl));
        $causeCulture1->addFollower($causeCulture1->createFollower($referent));
        $causeCulture1->addFollower($this->createFollowerByEmail(
            $causeCulture1,
            'adherent@en-marche-dev.fr',
            'Follower',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'),
            true,
            true,
            true
        ));
        $causeCulture1->addFollower($this->createFollowerByEmail(
            $causeCulture1,
            'jean-paul@dupont.tld',
            'Jean',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'),
            false
        ));
        $this->addReference('cause-culture-1', $causeCulture1);

        $causeCulture2 = $this->createCause(
            self::CAUSE_2_UUID,
            'Cause pour la culture 2',
            'Description de la cause pour la culture 2',
            $this->getReference('coalition-culture'),
            $this->getReference('adherent-1'),
            '-1 day',
            0,
            true
        );

        $causeCulture3 = $this->createCause(
            self::CAUSE_3_UUID,
            'Cause pour la culture 3',
            'Description de la cause pour la culture 3',
            $this->getReference('coalition-culture'),
            $this->getReference('adherent-1'),
            '-3 days'
        );

        $causeEducation1 = $this->createCause(
            self::CAUSE_4_UUID,
            'Cause pour l\'éducation',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-éducation'),
            $this->getReference('adherent-3'),
            '-4 days',
            0,
            true
        );
        $this->addReference('cause-education-1', $causeEducation1);

        $causeJustice1 = $this->createCause(
            self::CAUSE_5_UUID,
            'Cause pour la justice',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-justice'),
            $this->getReference('adherent-3'),
            '-5 days'
        );

        $manager->persist($this->createCause(
            self::CAUSE_6_UUID,
            'Cause d\'une coalition désactivée',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-inactive'),
            $this->getReference('adherent-3'),
            '-6 days'
        ));

        $manager->persist($this->createCause(
            self::CAUSE_7_UUID,
            'Cause en attente',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-justice'),
            $this->getReference('adherent-3'),
            '-7 days',
            0,
            false,
            Cause::STATUS_PENDING
        ));

        $manager->persist($causeCulture1);
        $manager->persist($causeCulture2);
        $manager->persist($causeCulture3);
        $manager->persist($causeEducation1);
        $manager->persist($causeJustice1);

        $manager->flush();
    }

    public function createCause(
        string $uuid,
        string $name,
        string $description,
        Coalition $coalition,
        Adherent $author,
        string $createdAt = 'now',
        int $followersCount = 0,
        bool $withImage = false,
        string $status = Cause::STATUS_APPROVED
    ): Cause {
        $cause = new Cause(Uuid::fromString($uuid), $followersCount);
        $cause->setCoalition($coalition);
        $cause->setName($name);
        $cause->setDescription($description);
        $cause->setAuthor($author);
        $cause->setStatus($status);
        $cause->setCreatedAt(new \DateTime($createdAt));

        if ($withImage) {
            $cause->setImage(new UploadedFile(
                __DIR__.'/../coalitions/default.png',
                'image.png',
                'image/png',
                null,
                null,
                true
            ));
            $this->imageManager->saveImage($cause);
        }

        return $cause;
    }

    public function createFollowerByEmail(
        Cause $cause,
        string $email,
        string $firstName,
        Zone $zone,
        bool $cguAccepted,
        bool $causeSubscription = null,
        bool $coalitionSubscription = null
    ): FollowerInterface {
        $follower = new CauseFollower($cause);
        $follower->setEmailAddress($email);
        $follower->setFirstName($firstName);
        $follower->setZone($zone);
        $follower->setCguAccepted($cguAccepted);
        $follower->setCauseSubscription($causeSubscription ?? false);
        $follower->setCoalitionSubscription($coalitionSubscription ?? false);

        return $follower;
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCoalitionData::class,
            LoadGeoZoneData::class,
        ];
    }
}
