<?php

namespace App\DataFixtures\ORM;

use App\VotePlace\VotePlaceFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadVotePlaceData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($votePlaceLilleWazemmes = VotePlaceFactory::createFromArray([
                'name' => 'Salle Polyvalente De Wazemmes',
                'code' => '59350_0113',
                'postalCode' => '59000,59100',
                'city' => 'Lille',
                'address' => "Rue De L'Abbé Aerts",
            ]
        ));

        $manager->persist($votePlaceLilleJeanZay = VotePlaceFactory::createFromArray([
                'name' => 'Restaurant Scolaire - Rue H. Lefebvre',
                'code' => '59350_0407',
                'postalCode' => '59350',
                'city' => 'Lille',
                'address' => 'Groupe Scolaire Jean Zay',
            ]
        ));

        $manager->persist($votePlaceBobignyBlanqui = VotePlaceFactory::createFromArray([
                'name' => 'Ecole Maternelle La Source',
                'code' => '93066_0004',
                'postalCode' => '93200,93066',
                'city' => 'Saint-Denis',
                'address' => '15, Rue Auguste Blanqui',
            ]
        ));

        $manager->persist(VotePlaceFactory::createFromArray([
                'name' => 'Milan',
                'code' => '99999_0249',
                'postalCode' => null,
                'city' => 'Milan',
                'address' => 'Ecole suisse de Milan',
                'country' => 'IT',
            ]
        ));

        $manager->persist(VotePlaceFactory::createFromArray([
                'name' => 'Naples',
                'code' => '99999_0251',
                'postalCode' => null,
                'city' => 'Naples',
                'address' => 'Consulat général de France à Naples',
                'country' => 'IT',
            ]
        ));

        $this->addReference('vote-place-lille-wazemmes', $votePlaceLilleWazemmes);
        $this->addReference('vote-place-lille-jean-zay', $votePlaceLilleJeanZay);
        $this->addReference('vote-place-bobigny-blanqui', $votePlaceBobignyBlanqui);
        $this->addReference('vote-place-ecole-suisse-milan', $votePlaceLilleJeanZay);
        $this->addReference('vote-place-consulat-france-naples', $votePlaceLilleJeanZay);

        $manager->flush();
    }
}
