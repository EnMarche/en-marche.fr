<?php

namespace App\DataFixtures\ORM;

use App\Entity\City;
use App\Entity\Election\CityCard;
use App\Entity\Election\CityContact;
use App\Entity\Election\CityManager;
use App\Entity\Election\CityPartner;
use App\Entity\Election\CityPrevision;
use App\Utils\PhoneNumberUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadElectionCityCardData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $cityCard1 = $this->createCityCard(
            $this->getReference('city-lille'),
            200000,
            CityCard::PRIORITY_HIGH
        );
        $cityCard1->setHeadquartersManager(new CityManager('John Doe'));
        $cityCard1->setPoliticManager(new CityManager(
            'Jane Doe',
            PhoneNumberUtils::create('+33612345678')
        ));
        $cityCard1->setPreparationPrevision(new CityPrevision(
            CityPrevision::STRATEGY_FUSION,
            'Marcel Doe',
            'Centre droit',
            'LaREM + Modem + LR',
            'Christian Doe'
        ));
        $cityCard1->setCandidatePrevision(new CityPrevision(CityPrevision::STRATEGY_RETENTION));
        $cityCard1->addPartner(new CityPartner($cityCard1, 'MODEM', CityPartner::CONSENSUS));
        $cityCard1->addPartner(new CityPartner($cityCard1, 'AGIR', CityPartner::DISSENSUS));
        $cityCard1->addContact(new CityContact(
            $cityCard1,
            'Michel Doe',
            'Manager',
            PhoneNumberUtils::create('+33698765432'),
            'Didier Doe',
            true,
            'No comment',
        ));
        $cityCard1->addContact(new CityContact(
            $cityCard1,
            'Jacques Doe',
            'Chef',
            PhoneNumberUtils::create('+33687654878'),
            'Didier Doe',
            false
        ));

        $manager->persist($cityCard1);

        $cityCard2 = $this->createCityCard(
            $this->getReference('city-roubaix'),
            50000,
            CityCard::PRIORITY_MEDIUM
        );

        $manager->persist($cityCard2);

        $manager->flush();
    }

    private function createCityCard(City $city, ?int $population = null, ?string $priority = null): CityCard
    {
        return new CityCard($city, $population, $priority);
    }

    public function getDependencies(): array
    {
        return [
            LoadCityData::class,
        ];
    }
}
