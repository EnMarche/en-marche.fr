<?php

namespace App\DataFixtures\ORM;

use App\Entity\MemberSummary\MissionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadMissionTypeData extends Fixture
{
    public const MISSION_TYPES = [
        'MT_001' => 'Me former à l\'action politique et citoyenne',
        'MT_002' => 'Faire émerger des idées nouvelles',
        'MT_003' => 'Faire remonter les opinions du terrain',
        'MT_004' => 'M\'engager dans des projets citoyens concrètes',
        'MT_005' => 'Expérimenter des projets concrets',
        'MT_006' => 'Participer aux conventions démocratiques européennes',
        'MT_007' => 'Recruter des nouveaux adhérents et attirer de nouveaux talents',
        'MT_008' => 'Participer à des actions militantes',
        'MT_009' => 'Être un relai de l\'action gouvernementale',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::MISSION_TYPES as $mission) {
            $missionType = new MissionType();
            $missionType->setName($mission);
            $manager->persist($missionType);
        }

        $manager->flush();
    }
}
