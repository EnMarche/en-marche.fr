<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\EventCategory;
use App\Entity\Event\EventGroupCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadEventGroupCategoryData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $eventGroupCategory0 = new EventGroupCategory('événement');
        $this->addReference('event-group-category-0', $eventGroupCategory0);

        $eventGroupCategory1 = new EventGroupCategory('Atelier');
        $this->addReference('event-group-category-1', $eventGroupCategory1);

        $eventGroupCategory2 = new EventGroupCategory('Conférence');
        $this->addReference('event-group-category-2', $eventGroupCategory2);

        $eventGroupCategory3 = new EventGroupCategory('projets', EventCategory::DISABLED);
        $this->addReference('event-group-category-3', $eventGroupCategory3);

        $eventGroupCategory4 = new EventGroupCategory('Évènements de campagne');
        $this->addReference('event-group-category-4', $eventGroupCategory4);

        $manager->persist($eventGroupCategory0);
        $manager->persist($eventGroupCategory1);
        $manager->persist($eventGroupCategory2);
        $manager->persist($eventGroupCategory3);
        $manager->persist($eventGroupCategory4);

        $manager->flush();
    }
}
