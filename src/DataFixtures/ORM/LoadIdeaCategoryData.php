<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadIdeaCategoryData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_category');

        $europeanCategory = new Category(
            'Echelle Européenne',
            true
        );

        $this->addReference('category-european', $europeanCategory);

        $nationalCategory = new Category(
            'Echelle Nationale',
            true
        );

        $this->addReference('category-national', $nationalCategory);

        $localCategory = new Category(
            'Echelle Locale',
            true
        );

        $this->addReference('category-local', $localCategory);

        $notPublishedCategory = new Category(
            'Echelle non publiée'
        );

        $this->addReference('category-not-published', $notPublishedCategory);

        $manager->persist($europeanCategory);
        $manager->persist($nationalCategory);
        $manager->persist($localCategory);
        $manager->persist($notPublishedCategory);

        $manager->flush();
    }
}
