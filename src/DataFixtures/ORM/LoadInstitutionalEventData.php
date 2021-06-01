<?php

namespace App\DataFixtures\ORM;

use App\Entity\PostAddress;
use App\Event\EventFactory;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadInstitutionalEventData extends Fixture implements DependentFixtureInterface
{
    public const INSTITUTIONAL_EVENT_1_UUID = '3f46976e-e76a-476e-86d7-575c6d3bc15e';

    private $eventFactory;

    public function __construct(EventFactory $eventFactory)
    {
        $this->eventFactory = $eventFactory;
    }

    public function load(ObjectManager $manager)
    {
        $institutionalEvent1 = $this->eventFactory->createInstitutionalEventFromArray([
            'uuid' => self::INSTITUTIONAL_EVENT_1_UUID,
            'organizer' => $this->getReference('adherent-8'),
            'name' => 'Evénement institutionnel numéro 1',
            'category' => $this->getReference('institutional-event-category-1'),
            'description' => 'Un événement institutionnel',
            'address' => PostAddress::createFrenchAddress('16 rue de la Paix', '75008-75108', null, 48.869331, 2.331595),
            'begin_at' => (new Chronos('+3 days'))->setTime(9, 30, 00, 000),
            'finish_at' => (new Chronos('+3 days'))->setTime(19, 00, 00, 000),
            'capacity' => 10,
            'time_zone' => 'Europe/Paris',
        ]);
        $institutionalEvent1->setPublished(true);

        $manager->persist($institutionalEvent1);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadInstitutionalEventCategoryData::class,
        ];
    }
}
