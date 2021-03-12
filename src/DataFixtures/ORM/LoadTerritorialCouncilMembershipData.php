<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadTerritorialCouncilMembershipData extends Fixture implements DependentFixtureInterface
{
    public const MEMBERSHIP_UUID1 = 'ad3780fe-d607-4d01-bc1a-d537fe351908';

    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-3'), new \DateTime('2020-06-06'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, 'Super comité de Paris'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, '75'));
        $this->setReference('member_1_coTerr_75', $membership);
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-4'), new \DateTime('2020-07-07'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::LRE_MANAGER, 'Paris 75'));
        $this->setReference('member_2_coTerr_75', $membership);
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-5'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, '75'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CONSULAR_COUNCILOR, '75'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Paris 75010'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::BOROUGH_COUNCILOR, '75010'));
        $this->setReference('member_3_coTerr_75', $membership);
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-8'), new \DateTime('2020-03-03'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::REFERENT, '75'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::MAYOR, '75012'));
        $this->setReference('member_4_coTerr_75', $membership);
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-19'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'En Marche Paris 8'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CONSULAR_COUNCILOR, '75009'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, '75'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::REFERENT, '75'));
        $this->setReference('member_5_coTerr_75', $membership);
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('deputy-75-1'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPUTY, 'CIRCO 75010'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::MAYOR, 'Paris 7eme (75007)'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, '75010'));
        $this->setReference('member_6_coTerr_75', $membership);
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-12'), new \DateTime('2020-02-02'), Uuid::fromString(self::MEMBERSHIP_UUID1));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::MAYOR, '75011'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Paris 75011'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::BOROUGH_COUNCILOR, '75011'));
        $this->setReference('member_7_coTerr_75', $membership);
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-6'), new \DateTime('2020-04-04'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Paris 75016'));
        $this->setReference('member_8_coTerr_75', $membership);
        $manager->persist($membership);

        /** @var TerritorialCouncil $coTerr92 */
        $coTerr92 = $this->getReference('coTerr_92');

        $membership = new TerritorialCouncilMembership($coTerr92, $this->getReference('adherent-2'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, 'Comité de 92 en marche!'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Chatillon'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR, 'Ile de France'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerr92, $this->getReference('municipal-manager-1'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Chatillon'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerr92, $this->getReference('senatorial-candidate'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Chatillon'));
        $manager->persist($membership);

        /** @var TerritorialCouncil $coTerr59 */
        $coTerr59 = $this->getReference('coTerr_59');

        for ($i = 30; $i <= 40; ++$i) {
            $manager->persist($membership = new TerritorialCouncilMembership($coTerr59, $this->getReference('adherent-'.$i), new \DateTime(sprintf('- %d days', $i))));

            if (0 === $i % 4) {
                $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Lille'));
                $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR, 'Nord'));
            } elseif (0 === $i % 3) {
                $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, 'Lille'));
            } elseif (0 === $i % 2) {
                $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Comité de Lille'));
            } else {
                $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPUTY, 'Député de Lille'));
            }
        }

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
