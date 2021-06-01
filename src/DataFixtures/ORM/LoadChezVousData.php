<?php

namespace App\DataFixtures\ORM;

use App\ChezVous\Marker\MaisonServiceAccueilPublic;
use App\ChezVous\Measure\BaisseNombreChomeurs;
use App\ChezVous\Measure\MaisonServiceAccueilPublic as MaisonServiceAccueilPublicMeasure;
use App\ChezVous\Measure\QuartierReconqueteRepublicaine;
use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Department;
use App\Entity\ChezVous\Marker;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\Region;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadChezVousData extends Fixture implements DependentFixtureInterface
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
            'name' => 'Lille',
            'postal_codes' => [
                '59000',
                '59160',
                '59260',
                '59777',
                '59800',
            ],
            'department_code' => '59',
            'latitude' => 50.633333,
            'longitude' => 3.066667,
            'measures' => [
                ['type' => QuartierReconqueteRepublicaine::TYPE],
                ['type' => MaisonServiceAccueilPublicMeasure::TYPE],
                [
                    'type' => BaisseNombreChomeurs::TYPE,
                    'payload' => [
                        BaisseNombreChomeurs::KEY_BAISSE_VILLE => 300,
                        BaisseNombreChomeurs::KEY_BAISSE_DEPARTEMENT => 4000,
                    ],
                ],
            ],
            'markers' => [
                [
                    'type' => MaisonServiceAccueilPublic::TYPE,
                    'latitude' => 50.6346419,
                    'longitude' => 3.0302644,
                ],
                [
                    'type' => MaisonServiceAccueilPublic::TYPE,
                    'latitude' => 50.6257989,
                    'longitude' => 3.0787775,
                ],
            ],
        ],
        '44109' => [
            'name' => 'Nantes',
            'postal_codes' => [
                '44000',
                '44100',
                '44200',
                '44300',
            ],
            'department_code' => '44',
            'latitude' => 47.216667,
            'longitude' => -1.55,
            'measures' => [
                ['type' => QuartierReconqueteRepublicaine::TYPE],
            ],
            'markers' => [
                [
                    'type' => MaisonServiceAccueilPublic::TYPE,
                    'latitude' => 47.2441827,
                    'longitude' => -1.5258725,
                ],
                [
                    'type' => MaisonServiceAccueilPublic::TYPE,
                    'latitude' => 47.2239202,
                    'longitude' => -1.5517449,
                ],
            ],
        ],
        '06088' => [
            'name' => 'Nice',
            'postal_codes' => [
                '06000',
                '06100',
                '06200',
                '06300',
            ],
            'department_code' => '06',
            'latitude' => 43.7,
            'longitude' => 7.25,
            'measures' => [
                ['type' => QuartierReconqueteRepublicaine::TYPE],
                [
                    'type' => BaisseNombreChomeurs::TYPE,
                    'payload' => [
                        BaisseNombreChomeurs::KEY_BAISSE_VILLE => 300,
                        BaisseNombreChomeurs::KEY_BAISSE_DEPARTEMENT => 4000,
                    ],
                ],
            ],
            'markers' => [
                [
                    'type' => MaisonServiceAccueilPublic::TYPE,
                    'latitude' => 43.7014582,
                    'longitude' => 7.2536172,
                ],
                [
                    'type' => MaisonServiceAccueilPublic::TYPE,
                    'latitude' => 43.6755154,
                    'longitude' => 7.206959,
                ],
            ],
        ],
    ];

    private $slugify;

    public function __construct()
    {
        $this->slugify = Slugify::create();
    }

    public function load(ObjectManager $manager)
    {
        foreach (self::REGIONS as $code => $name) {
            $region = new Region($name, $code);

            $manager->persist($region);
            $this->setReference("chez-vous-region-$code", $region);
        }

        $manager->flush();

        foreach (self::DEPARTMENTS as $code => $departmentMetadatas) {
            $department = new Department(
                $this->getReference(sprintf('chez-vous-region-%s', $departmentMetadatas['region_code'])),
                $departmentMetadatas['name'],
                $departmentMetadatas['label'],
                $code
            );

            $manager->persist($department);
            $this->setReference("chez-vous-department-$code", $department);
        }

        $manager->flush();

        foreach (self::CITIES as $inseeCode => $cityMetadatas) {
            $city = new City(
                $this->getReference(sprintf('chez-vous-department-%s', $cityMetadatas['department_code'])),
                $cityMetadatas['name'],
                $cityMetadatas['postal_codes'],
                $inseeCode,
                sprintf('%s-%s', $inseeCode, $this->slugify->slugify($cityMetadatas['name'])),
                $cityMetadatas['latitude'],
                $cityMetadatas['longitude']
            );

            if (isset($cityMetadatas['measures'])) {
                foreach ($cityMetadatas['measures'] as $measureMetadatas) {
                    $measure = new Measure(
                        $city,
                        $this->getReference(sprintf('chez-vous-measure-type-%s', $measureMetadatas['type'])),
                        $measureMetadatas['payload'] ?? null
                    );

                    $city->addMeasure($measure);
                }
            }

            if (isset($cityMetadatas['markers'])) {
                foreach ($cityMetadatas['markers'] as $markerMetadatas) {
                    $marker = new Marker(
                        $city,
                        $markerMetadatas['type'],
                        $markerMetadatas['latitude'],
                        $markerMetadatas['longitude']
                    );

                    $city->addMarker($marker);
                }
            }

            $manager->persist($city);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadChezVousMeasureTypeData::class,
        ];
    }
}
