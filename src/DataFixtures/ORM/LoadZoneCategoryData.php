<?php

namespace App\DataFixtures\ORM;

use App\Entity\ElectedRepresentative\ZoneCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadZoneCategoryData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach (ZoneCategory::ALL as $name) {
            $category = new ZoneCategory($name);
            $manager->persist($category);
            $this->setReference('zone-category-'.mb_strtolower($name), $category);
        }

        $manager->flush();
    }
}
