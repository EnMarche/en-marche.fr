<?php

namespace App\DataFixtures\ORM;

use App\Entity\IdeasWorkshop\Guideline;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadIdeaGuidelineData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $guidelineMainFeature = new Guideline(
            'POUR COMMENCER',
            true,
            1
        );
        $this->addReference('guideline-main-feature', $guidelineMainFeature);

        $guidelineImplementation = new Guideline(
            'POUR ALLER PLUS LOIN :',
            true,
            3
        );
        $this->addReference('guideline-implementation', $guidelineImplementation);

        $guidelineDisabled = new Guideline(
            'Masquée.',
            false,
            2
        );

        $manager->persist($guidelineMainFeature);
        $manager->persist($guidelineImplementation);
        $manager->persist($guidelineDisabled);

        $manager->flush();
    }
}
