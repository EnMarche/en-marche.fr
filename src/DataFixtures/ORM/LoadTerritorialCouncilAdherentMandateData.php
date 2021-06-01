<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTerritorialCouncilAdherentMandateData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        $adherentMandate = new TerritorialCouncilAdherentMandate(
            $this->getReference('adherent-3'),
            $coTerrParis,
            TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
            Genders::MALE,
            new \DateTime('2020-06-06')
        );
        $manager->persist($adherentMandate);

        $adherentMandate = new TerritorialCouncilAdherentMandate(
            $this->getReference('adherent-5'),
            $coTerrParis,
            TerritorialCouncilQualityEnum::BOROUGH_COUNCILOR,
            Genders::FEMALE,
            new \DateTime('2020-02-02')
        );
        $manager->persist($adherentMandate);

        $adherentMandate = new TerritorialCouncilAdherentMandate(
            $this->getReference('adherent-19'),
            $coTerrParis,
            TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            Genders::FEMALE,
            new \DateTime('2020-02-02')
        );
        $manager->persist($adherentMandate);

        /** @var TerritorialCouncil $coTerr92 */
        $coTerr92 = $this->getReference('coTerr_92');

        $adherentMandate = new TerritorialCouncilAdherentMandate(
            $this->getReference('adherent-2'),
            $coTerr92,
            TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            Genders::FEMALE,
            new \DateTime('2020-02-02')
        );
        $manager->persist($adherentMandate);

        $adherentMandate = new TerritorialCouncilAdherentMandate(
            $this->getReference('municipal-manager-1'),
            $coTerr92,
            TerritorialCouncilQualityEnum::CITY_COUNCILOR,
            Genders::FEMALE,
            new \DateTime('2020-02-02')
        );
        $manager->persist($adherentMandate);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadTerritorialCouncilData::class,
        ];
    }
}
