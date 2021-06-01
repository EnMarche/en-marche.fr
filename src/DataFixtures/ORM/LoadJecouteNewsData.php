<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Jecoute\JecouteSpaceEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadJecouteNewsData extends Fixture implements DependentFixtureInterface
{
    public const NEWS_1_UUID = '16373659-fed1-443c-a956-a257e2c2bae4';
    public const NEWS_2_UUID = '0bc3f920-da90-4773-80e1-a388005926fc';
    public const NEWS_3_UUID = '232f99b8-7a0c-40ed-ba9e-bf8f33e19052';

    public function load(ObjectManager $manager)
    {
        $news1 = $this->createNews(
            self::NEWS_1_UUID,
            'Nouveau sondage disponible',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            'global_topic',
            null,
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_59')
        );
        $news1->setSpace(JecouteSpaceEnum::CANDIDATE_SPACE);
        $manager->persist($news1);

        $news2 = $this->createNews(
            self::NEWS_2_UUID,
            'Rassemblement',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            'global_topic',
            'https://en-marche.fr',
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11')
        );
        $news2->setSpace(JecouteSpaceEnum::CANDIDATE_SPACE);
        $manager->persist($news2);

        $manager->persist($this->createNews(
            self::NEWS_3_UUID,
            'Nouveau assemblement',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            null,
            'https://en-marche.fr',
            null,
            true,
            false
        ));

        $manager->flush();
    }

    private function createNews(
        string $uuid,
        string $title,
        string $text,
        string $topic = null,
        string $externalLink = null,
        Zone $zone = null,
        bool $notification = true,
        bool $published = true
    ): News {
        return new News(
            Uuid::fromString($uuid),
            $title,
            $text,
            $topic,
            $externalLink,
            $zone,
            $notification,
            $published
        );
    }

    public function getDependencies()
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}
