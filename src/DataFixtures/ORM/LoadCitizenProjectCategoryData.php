<?php

namespace App\DataFixtures\ORM;

use App\Entity\CitizenProjectCategory;
use App\Entity\Event\EventCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadCitizenProjectCategoryData extends Fixture
{
    public const CITIZEN_PROJECT_CATEGORIES = [
        'CPC001' => 'Nature et Environnement',
        'CPC002' => 'Education, culture et citoyenneté',
        'CPC003' => 'Culture',
        'CPC004' => 'Lien social et aide aux personnes en difficulté',
        'CPC005' => 'Santé',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CITIZEN_PROJECT_CATEGORIES as $code => $name) {
            $category = new CitizenProjectCategory($name, EventCategory::ENABLED);
            $manager->persist($category);
            $this->addReference(strtolower($code), $category);
        }

        $manager->flush();
    }
}
