<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTerritorialCouncilMembershipLogData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $membershipLog1 = new TerritorialCouncilMembershipLog(
            TerritorialCouncilMembershipLog::TYPE_WARNING,
            'Plusieurs conseils territorials ont été trouvés pour cette qualité',
            $this->getReference('adherent-7'),
            TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            null,
            [],
            ['Seine-et-Marne (77)', 'Essonne (91)']
        );
        $manager->persist($membershipLog1);

        $membershipLog2 = new TerritorialCouncilMembershipLog(
            TerritorialCouncilMembershipLog::TYPE_WARNING,
            'Adhérent est déjà membre avec cette qualité (de priorité majeure)',
            $this->getReference('adherent-2'),
            TerritorialCouncilQualityEnum::SENATOR,
            $this->getReference('coTerr_78'),
            [TerritorialCouncilQualityEnum::SENATOR],
            ['Alpes-de-Haute-Provence (04)']
        );
        $membershipLog2->setIsResolved(true);
        $manager->persist($membershipLog2);

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
