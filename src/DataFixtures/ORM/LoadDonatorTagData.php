<?php

namespace App\DataFixtures\ORM;

use App\Entity\DonatorTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadDonatorTagData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new DonatorTag('Très grand donateur', '#32cd32'));
        $manager->persist(new DonatorTag('Grand donateur', '#ff4500'));
        $manager->persist(new DonatorTag('Résident étranger', '#00bfff'));

        $manager->flush();
    }
}
