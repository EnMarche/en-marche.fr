<?php

namespace App\DataFixtures\ORM;

use App\Entity\City;
use App\Entity\Department;
use App\Entity\Region;
use App\Utils\AreaUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadCityData extends Fixture
{
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
        'COM' => 'Collectivités d\'Outre-Mer',
    ];

    private const DEPARTMENTS = [
        '06' => [
            'region_code' => '93',
            'name' => 'Alpes-Maritimes',
            'label' => 'Dans les Alpes-Maritimes',
        ],
        '44' => [
            'region_code' => '52',
            'name' => 'Loire-Atlantique',
            'label' => 'En Loire-Atlantique',
        ],
        '59' => [
            'region_code' => '32',
            'name' => 'Nord',
            'label' => 'Dans le Nord',
        ],
    ];

    private const CITIES = [
        '59350' => [
            'department_code' => '59',
            'name' => 'Lille',
            'postal_codes' => [
                '59000',
            ],
        ],
        '59512' => [
            'department_code' => '59',
            'name' => 'Roubaix',
            'postal_codes' => [
                '59000',
            ],
        ],
        '59560' => [
            'department_code' => '59',
            'name' => 'Seclin',
            'postal_codes' => [
                '59100',
            ],
        ],
        '06105' => [
            'department_code' => '06',
            'name' => 'Roquefort-les-Pins',
            'postal_codes' => [
                '06330',
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::REGIONS as $code => $name) {
            $region = new Region($name, $code, AreaUtils::CODE_FRANCE);

            $manager->persist($region);
            $this->setReference("region-$code", $region);
        }

        $manager->flush();

        foreach (self::DEPARTMENTS as $code => $departmentMetadatas) {
            $department = new Department(
                $this->getReference(sprintf('region-%s', $departmentMetadatas['region_code'])),
                $departmentMetadatas['name'],
                $departmentMetadatas['label'],
                $code
            );

            $manager->persist($department);
            $this->setReference("department-$code", $department);
        }

        $manager->flush();

        foreach (self::CITIES as $inseeCode => $cityMetadatas) {
            $city = new City(
                $this->getReference(sprintf('department-%s', $cityMetadatas['department_code'])),
                $cityMetadatas['name'],
                $inseeCode,
                $cityMetadatas['postal_codes']
            );

            $manager->persist($city);
            $this->setReference(sprintf('city-%s', mb_strtolower($cityMetadatas['name'])), $city);
        }

        $manager->flush();
    }
}
