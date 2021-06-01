<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\MunicipalEvent;
use App\Entity\PostAddress;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadMunicipalEventData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $eventCategory1 = $this->getReference('CE001');

        $event = new MunicipalEvent(
            Uuid::uuid4(),
            $this->getReference('municipal-chief-1'),
            null,
            'Event municipal',
            $eventCategory1,
            'Allons à la rencontre des citoyens.',
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+3 days'))->format('Y-m-d').' 09:30:00',
            (new Chronos('+3 days'))->format('Y-m-d').' 19:00:00'
        );

        $manager->persist($event);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadEventCategoryData::class,
        ];
    }
}
