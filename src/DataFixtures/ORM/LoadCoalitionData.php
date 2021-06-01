<?php

namespace App\DataFixtures\ORM;

use App\Entity\Coalition\Coalition;
use App\Image\ImageManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCoalitionData extends Fixture implements DependentFixtureInterface
{
    public const COALITION_1_UUID = 'd5289058-2a35-4cf0-8f2f-a683d97d8315';
    public const COALITION_2_UUID = '09d700f8-8813-4c3c-9bee-ff18d2051bba';
    public const COALITION_3_UUID = 'fc7fd104-71e5-4399-a874-f8fe752f846b';
    public const COALITION_4_UUID = 'fff11d8d-5cb5-4075-b594-fea265438d65';
    public const COALITION_5_UUID = 'eaa129cf-fcbd-4d7d-8cfa-2268d08527ec';
    public const COALITION_6_UUID = '0654ae09-ea1a-4142-bea4-2e82dc5da998';
    public const COALITION_7_UUID = '81e4a680-7ce0-4038-b8fe-6bf755db4c5b';
    public const COALITION_8_UUID = '429fa3a9-8288-4de5-8ba5-366e6afa366b';
    public const COALITION_9_UUID = '5b8db218-4da6-4f7f-a53e-29a7a349d45c';
    public const COALITION_10_UUID = '5e500dbe-5227-4b83-8a9c-8c36f3f25265';
    public const COALITION_11_UUID = 'bd64b020-cb5b-4dd9-a478-1a1fac619ee1';
    public const COALITION_12_UUID = '1cbcf3cd-d0e4-4bd7-8d33-a2fa3320791d';
    public const COALITION_13_UUID = 'fd0990f9-0148-4fed-84e5-4deee0af2d45';
    public const COALITION_14_UUID = '49202478-544e-4b00-9b90-f2945804c920';
    public const COALITION_15_UUID = '4b2a1335-362c-4611-bf82-d6c1216db389';
    public const COALITION_16_UUID = '9a552cda-2d7a-41b4-aaf0-1bcab14b76f8';
    public const COALITION_17_UUID = '5ce3b33c-75d6-4923-bffb-7385e7d8e15a';
    public const COALITION_18_UUID = '8b4a9add-c7cd-43a0-b4da-8eab51d8f02b';
    public const COALITION_19_UUID = '0abb99ea-c6fe-473b-bf88-f31f887a3233';
    public const COALITION_20_UUID = 'a82ee43a-c68d-4ed2-9cd5-56eb1f72d9c8';

    public const NAMES = [
        'Culture',
        'Démocratie',
        'Économie',
        'Éducation',
        'Égalité H/F',
        'Europe',
        'Inclusion',
        'International',
        'Justice',
        'Numérique',
        'Puissance publique',
        'République',
        'Ruralité',
        'Santé',
        'Sécurité',
        'Solidarités',
        'Transition écologique',
        'Travail',
        'Villes et quartiers',
    ];

    private $imageManager;

    public function __construct(ImageManagerInterface $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        $carl = $this->getReference('adherent-2');
        $jacques = $this->getReference('adherent-3');
        $lucie = $this->getReference('adherent-4');
        $gisele = $this->getReference('adherent-5');

        foreach (self::NAMES as $key => $name) {
            if (0 === $key) {
                $withImage = true;
                $youtubeId = 'yOuTUbe_';
                $followers = [$carl, $jacques, $lucie, $gisele];
            } else {
                $withImage = false;
                $youtubeId = null;
                $followers = [];
            }

            $coalition = $this->createCoalition(++$key, $name, $youtubeId, true, $withImage, $followers);
            $manager->persist($coalition);
        }

        $disabledCoalition = $this->createCoalition(20, 'Inactive', null, false);
        $manager->persist($disabledCoalition);

        $manager->flush();
    }

    public function createCoalition(
        int $id,
        string $name,
        string $youtubeId = null,
        bool $enabled = true,
        bool $withImage = false,
        array $followers = []
    ): Coalition {
        $c = "COALITION_${id}_UUID";
        $uuid = Uuid::fromString(\constant('self::'.$c));

        $coalition = new Coalition(
            $uuid,
            $name,
            "Description de la coalition '$name'",
            $youtubeId,
            $enabled
        );

        if ($withImage) {
            $coalition->setImage(new UploadedFile(
                __DIR__.'/../coalitions/default.png',
                'image.png',
                'image/png',
                null,
                null,
                true
            ));
            $this->imageManager->saveImage($coalition);
        }

        foreach ($followers as $adherent) {
            $coalition->addFollower($coalition->createFollower($adherent));
        }

        $this->addReference(sprintf('coalition-%s', mb_strtolower($name)), $coalition);

        return $coalition;
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
