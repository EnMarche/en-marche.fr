<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\CitizenActionCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadCitizenActionCategoryData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $category = new CitizenActionCategory('Action citoyenne');

        $this->addReference('citizen-action-category', $category);

        $manager->persist($category);
        $manager->flush();
    }
}
