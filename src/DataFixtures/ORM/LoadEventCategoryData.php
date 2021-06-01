<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\EventCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadEventCategoryData extends Fixture implements DependentFixtureInterface
{
    public const LEGACY_EVENT_CATEGORIES = [
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

    public const LEGACY_EVENT_CATEGORIES_GROUPED = [
        'CE012' => [
            'name' => 'ancrage local',
            'status' => BaseEventCategory::ENABLED,
            'group' => 'event-group-category-1',
        ],
        'CE013' => [
            'name' => 'projets citoyens',
            'status' => BaseEventCategory::ENABLED,
            'group' => 'event-group-category-1',
        ],
        'CE014' => [
            'name' => 'Un An',
            'status' => BaseEventCategory::ENABLED,
            'group' => 'event-group-category-1',
        ],
        'CE015' => [
            'name' => 'Débat',
            'status' => BaseEventCategory::ENABLED,
            'group' => 'event-group-category-2',
        ],
        'CE016' => [
            'name' => 'Catégorie masquée',
            'status' => BaseEventCategory::DISABLED,
            'group' => 'event-group-category-0',
        ],
        'CE017' => [
            'name' => 'Élections régionales',
            'status' => BaseEventCategory::ENABLED,
            'group' => 'event-group-category-4',
        ],
        'CE018' => [
            'name' => 'Élections départementales',
            'status' => BaseEventCategory::ENABLED,
            'group' => 'event-group-category-4',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'events_categories');

        foreach (self::LEGACY_EVENT_CATEGORIES as $reference => $name) {
            $category = new EventCategory($name);
            $category->setEventGroupCategory($this->getReference('event-group-category-0'));

            $this->addReference($reference, $category);

            $manager->persist($category);
        }

        foreach (self::LEGACY_EVENT_CATEGORIES_GROUPED as $reference => $dataCategory) {
            $category = new EventCategory($dataCategory['name'], $dataCategory['status']);
            if ($dataCategory['group']) {
                $category->setEventGroupCategory($this->getReference($dataCategory['group']));
            }
            $this->addReference($reference, $category);
            $manager->persist($category);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadEventGroupCategoryData::class,
        ];
    }
}
