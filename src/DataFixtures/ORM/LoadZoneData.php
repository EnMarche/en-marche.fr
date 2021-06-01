<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\ElectedRepresentative\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadZoneData extends Fixture implements DependentFixtureInterface
{
    private const CITIES = [
        '06000' => 'Nice (06000,06100,06200,06300)',
        '13001' => 'Marseille (13001)',
        '59000' => 'Lille (59000)',
        '69001' => 'Lyon 1er (69001)',
        '75007' => 'Paris 7eme (75007)',
        '76000' => 'Rouen (76000)',
        '77000' => 'Melun (77000)',
        '92110' => 'Clichy (92110)',
    ];

    private const EPCI = [
        '13' => [
            'Métropole d\'Aix-Marseille-Provence',
            'CA Terre de Provence',
            'CC Vallée des Baux-Alpilles (CC VBA)',
        ],
        '59' => [
            'Métropole Européenne de Lille',
            'CA du Douaisis Agglo',
            'CC Cœur d\'Ostrevent',
        ],
        '76' => [
            'Métropole Rouen Normandie',
            'CA Caux Seine Agglo',
            'CC des Quatre Rivières',
        ],
        '77' => [
            'CA Melun Val de Seine',
            'CA Val d\'Europe Agglomération',
            'CC du Provinois',
        ],
        '92' => [
            'Métropole du Grand Paris',
        ],
    ];

    private const DEPARTMENTS = [
        '01' => 'Ain (01)',
        '06' => 'Alpes-Maritimes (06)',
        '13' => 'Bouches-du-Rhône (13)',
        '59' => 'Nord (59)',
        '75' => 'Paris (75)',
        '69' => 'Rhône (69)',
        '76' => 'Seine-Maritime (76)',
        '77' => 'Seine-et-Marne (77)',
        '78' => 'Yvelines (78)',
        '92' => 'Hauts-de-Seine (92)',
        '2A' => 'Corse-du-Sud (2A)',
        '2B' => 'Haute-Corse (2B)',
    ];

    private const REGIONS = [
        '1' => 'Guadeloupe',
        '2' => 'Martinique',
        '3' => 'Guyane',
        '4' => 'La Réunion',
        '6' => 'Mayotte',
        '11' => 'Île-de-France',
        '24' => 'Centre-Val de Loire',
        '27' => 'Bourgogne-Franche-Comté',
        '28' => 'Normandie',
        '32' => 'Hauts-de-France',
        '44' => 'Grand Est',
        '52' => 'Pays de la Loire',
        '53' => 'Bretagne',
        '75' => 'Nouvelle-Aquitaine',
        '76' => 'Occitanie',
        '84' => 'Auvergne-Rhône-Alpes',
        '93' => 'Provence-Alpes-Côte d\'Azur',
        '94' => 'Corse',
    ];

    private const DISTRICTS = [
        '06003' => 'Alpes-Maritimes, 3ème circonscription (06-03)',
        '13001' => 'Bouches-du-Rhône, 1ère circonscription (13-01)',
        '59009' => 'Nord, 9ème circonscription (59-09)',
        '75007' => 'Paris, 7ème circonscription (75-07)',
        '75008' => 'Paris, 8ème circonscription (75-08)',
        '76002' => 'Seine-Maritime, 2ème circonscription (76-02)',
        '77001' => 'Seine-et-Marne, 1ère circonscription (77-01)',
        '69010' => 'Rhône, 10ème circonscription (69-10)',
        '78006' => 'Yvelines, 6ème circonscription (78-06)',
        '974002' => 'Réunion, 2ème circonscription (974-02)',
        '2A001' => 'Corse-du-Sud, 1ère circonscription (2A-01)',
    ];

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'elected_representative_zone');

        foreach (self::CITIES as $code => $name) {
            $zoneCity = new Zone($this->getReference('zone-category-ville'), $name);
            $zoneCity->addReferentTag($this->getReference('referent_tag_'.substr($code, 0, 2)));
            if ('75007' == $code) {
                $zoneCity->addReferentTag($this->getReference('referent_tag_'.$code));
            }

            $manager->persist($zoneCity);
            $this->setReference("zone-city-$code", $zoneCity);
        }

        foreach (self::EPCI as $codeDpt => $arrEpci) {
            foreach ($arrEpci as $key => $epci) {
                $zoneEPCI = new Zone($this->getReference('zone-category-epci'), $epci);
                $zoneEPCI->addReferentTag($this->getReference("referent_tag_$codeDpt"));

                $manager->persist($zoneEPCI);
                ++$key;
                $this->setReference("zone-epci-$codeDpt-$key", $zoneEPCI);
            }
        }

        foreach (self::DEPARTMENTS as $code => $name) {
            $zoneDpt = new Zone($this->getReference('zone-category-département'), $name);
            $zoneDpt->addReferentTag($this->getReference('referent_tag_'.mb_strtolower($code)));

            $manager->persist($zoneDpt);
            $this->setReference("zone-dpt-$code", $zoneDpt);
        }

        foreach (self::REGIONS as $code => $name) {
            $zoneRegion = new Zone($this->getReference('zone-category-région'), $name);
            $code = str_pad($code, 2, '0', \STR_PAD_LEFT);
            $zoneRegion->addReferentTag($this->getReference("referent_tag_$code"));

            $manager->persist($zoneRegion);
            $this->setReference("zone-region-$code", $zoneRegion);
        }

        foreach (self::DISTRICTS as $code => $name) {
            $zoneRegion = new Zone($this->getReference('zone-category-circonscription'), $name);
            $code = str_pad($code, 2, '0', \STR_PAD_LEFT);
            $zoneRegion->addReferentTag($this->getReference("referent_tag_circo_$code"));

            $manager->persist($zoneRegion);
            $this->setReference("zone-district-$code", $zoneRegion);
        }

        $zoneCorsica = new Zone($this->getReference('zone-category-corse'), 'Corse');
        $zoneCorsica->addReferentTag($this->getReference('referent_tag_20'));
        $manager->persist($zoneCorsica);
        $this->setReference('zone-corsica', $zoneCorsica);

        $zoneFOF = new Zone($this->getReference('zone-category-fde'), 'Français de l\'Étranger');
        $zoneFOF->addReferentTag($this->getReference('referent_tag_fof'));
        $manager->persist($zoneFOF);
        $this->setReference('zone-fof', $zoneFOF);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadZoneCategoryData::class,
            LoadReferentTagData::class,
            LoadDistrictData::class,
        ];
    }
}
