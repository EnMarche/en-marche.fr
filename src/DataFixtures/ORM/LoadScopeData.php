<?php

namespace App\DataFixtures\ORM;

use App\Entity\Scope;
use App\Scope\AppEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadScopeData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createScope(
            ScopeEnum::CANDIDATE,
            'Candidat',
            FeatureEnum::ALL,
            AppEnum::ALL
        ));

        $manager->persist($this->createScope(
            ScopeEnum::REFERENT,
            'Référent',
            FeatureEnum::ALL,
            AppEnum::ALL
        ));

        $manager->persist($this->createScope(
            ScopeEnum::DEPUTY,
            'Député',
            FeatureEnum::ALL,
            AppEnum::ALL
        ));

        $manager->persist($this->createScope(
            ScopeEnum::SENATOR,
            'Sénateur',
            FeatureEnum::ALL,
            AppEnum::ALL
        ));

        $manager->flush();
    }

    private function createScope(string $code, string $name, array $features, array $apps): Scope
    {
        return new Scope($code, $name, $features, $apps);
    }
}
