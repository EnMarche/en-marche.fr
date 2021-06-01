<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\PoliticalCommitteeQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadPoliticalCommitteeMembershipData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var PoliticalCommittee $coPolParis */
        $coPolParis = $this->getReference('coPol_75');

        $membership = new PoliticalCommitteeMembership($coPolParis, $this->getReference('adherent-19'), new \DateTime('2020-01-01'));
        $membership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::REFERENT));
        $manager->persist($membership);

        $membership = new PoliticalCommitteeMembership($coPolParis, $this->getReference('adherent-3'), new \DateTime('2020-06-06'));
        $membership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR));
        $membership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::LEADER));
        $manager->persist($membership);

        $membership = new PoliticalCommitteeMembership($coPolParis, $this->getReference('adherent-4'), new \DateTime('2020-07-07'));
        $membership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::LRE_MANAGER));
        $manager->persist($membership);

        $membership = new PoliticalCommitteeMembership($coPolParis, $this->getReference('adherent-8'), new \DateTime('2020-03-03'));
        $membership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::REFERENT));
        $manager->persist($membership);

        $membership = new PoliticalCommitteeMembership($coPolParis, $this->getReference('deputy-75-1'), new \DateTime('2020-02-02'));
        $membership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::DEPUTY));
        $manager->persist($membership);

        /** @var PoliticalCommittee $coPol92 */
        $coPol92 = $this->getReference('coPol_92');

        $membership = new PoliticalCommitteeMembership($coPol92, $this->getReference('adherent-2'), new \DateTime('2020-02-02'));
        $membership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR));
        $manager->persist($membership);

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
