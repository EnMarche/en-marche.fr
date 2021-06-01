<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\Coalition\QuickAction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadQuickActionData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'cause_quick_action');

        $causeCulture = $this->getReference('cause-culture-1');
        $causeEducation = $this->getReference('cause-education-1');
        $quickActionC1 = new QuickAction(
            'Première action rapide de la culture',
            'http://culture.fr',
            $causeCulture
        );
        $quickActionC2 = new QuickAction(
            'Deuxième action rapide de la culture',
            'http://test.culture.fr',
            $causeCulture
        );
        $quickActionC3 = new QuickAction(
            'Troisième action rapide de la culture',
            'http://culture.com',
            $causeCulture
        );
        $quickActionE1 = new QuickAction(
            'Action rapide de la l\'éducation 1',
            'http://education.fr',
            $causeEducation
        );

        $manager->persist($quickActionC1);
        $manager->persist($quickActionC2);
        $manager->persist($quickActionC3);
        $manager->persist($quickActionE1);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCauseData::class,
        ];
    }
}
