<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\EventCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventCategoryData implements FixtureInterface
{
    const LEGACY_EVENT_CATEGORIES = [
        'CE001' => 'Kiosque',
        'CE002' => 'Réunion d\'équipe',
        'CE003' => 'Conférence-débat',
        'CE004' => 'Porte-à-porte',
        'CE005' => 'Atelier du programme',
        'CE006' => 'Tractage',
        'CE007' => 'Convivialité',
        'CE008' => 'Action ciblée',
        'CE009' => 'Événement innovant',
        'CE010' => 'Marche',
        'CE011' => 'Support party',
    ];
    const HIDDEN_CATEGORY_NAME = 'Catégorie masquée';

    public function load(ObjectManager $manager)
    {
        foreach (self::LEGACY_EVENT_CATEGORIES as $name) {
            $manager->persist(new EventCategory($name));
        }

        $manager->persist(new EventCategory(self::HIDDEN_CATEGORY_NAME, EventCategory::DISABLED));

        $manager->flush();
    }
}
