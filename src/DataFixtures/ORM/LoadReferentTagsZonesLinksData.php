<?php

namespace App\DataFixtures\ORM;

use App\Entity\ReferentTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadReferentTagsZonesLinksData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($manager->getRepository(ReferentTag::class)->findAll() as $referentTag) {
            $name = $referentTag->getName();
            $code = $referentTag->getCode();
            $type = $referentTag->getType();

            if (!$zoneReference = $this->determineZoneReference($name, $code, $type)) {
                continue;
            }

            if ($zone = LoadGeoZoneData::getZoneReference($manager, $zoneReference)) {
                $referentTag->setZone($zone);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
            LoadReferentTagData::class,
            LoadDistrictData::class,
        ];
    }

    private function determineZoneReference(string $name, string $code, ?string $type): ?string
    {
        $reference = null;

        switch ($type) {
            case ReferentTag::TYPE_COUNTRY:
                $reference = 'zone_country_'.$code;
                break;

            case ReferentTag::TYPE_DEPARTMENT:
                $code = [
                    '97' => '971', // Guadeloupe
                    '98' => '987', // Polynésie Française
                ][$code] ?? $code;
                $reference = 'zone_department_'.$code;
                break;

            case ReferentTag::TYPE_DISTRICT:
                if (0 === strpos($code, 'CIRCO_FDE')) {
                    $okCode = preg_replace('/\D/', '', $code);
                    $okCode = str_pad($okCode, 2, '0', \STR_PAD_LEFT);
                    $reference = 'zone_foreign_district_CIRCO_FDE-'.$okCode;
                } elseif (0 === strpos($code, 'CIRCO_')) {
                    $okCode = preg_replace('/\D/', '', $code);
                    $okCode = str_pad($okCode, 6, '0', \STR_PAD_LEFT);
                    $okCode = str_pad(ltrim(substr($okCode, 0, 3), '0'), 2, '0', \STR_PAD_LEFT).'-'.ltrim(substr($okCode, -3), '0');
                    $reference = 'zone_district_'.$okCode;
                }
                break;

            case ReferentTag::TYPE_METROPOLIS:
                if ('69M' === $code) {
                    // Lyon
                    $reference = 'zone_city_community_200046977';
                } elseif ('34M' === $code) {
                    // Marseille
                    $reference = 'zone_city_community_200054807';
                }
                break;

            default:
                if (0 === strpos($name, 'Paris 750')) {
                    $okCode = '751'.substr($code, 3);
                    $reference = 'zone_borough_'.$okCode;
                } elseif ('Corse' === $name) {
                    $reference = 'zone_region_94';
                } elseif ('FOF' === $code) {
                    $reference = 'zone_custom_FDE';
                }
        }

        return $reference;
    }
}
