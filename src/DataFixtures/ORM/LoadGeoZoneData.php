<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class LoadGeoZoneData extends Fixture
{
    public static $zoneCache;

    public function load(ObjectManager $manager): void
    {
        /** @var Connection $conn */
        $conn = $manager->getConnection();
        $conn->exec(file_get_contents(__DIR__.'/../../../dump/all-geo-zone.sql'));
    }

    public static function getZoneReference(EntityManagerInterface $manager, string $reference): ?Zone
    {
        static::initZoneCache();

        return isset(static::$zoneCache[$reference]) ? $manager->getPartialReference(Zone::class, static::$zoneCache[$reference]) : null;
    }

    protected static function initZoneCache(): void
    {
        if (null === static::$zoneCache) {
            $file = fopen(__DIR__.'/../geo/geo-zones.csv', 'rb');
            $header = fgetcsv($file, 0, ';');
            static::$zoneCache = [];

            while ($row = fgetcsv($file, 0, ';')) {
                $row = array_combine($header, $row);
                static::$zoneCache[$row['zone']] = (int) $row['id'];
            }
        }
    }
}
