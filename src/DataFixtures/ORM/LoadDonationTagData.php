<?php

namespace App\DataFixtures\ORM;

use App\Entity\DonationTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadDonationTagData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new DonationTag('Inachevé', '#32cd32'));
        $manager->persist(new DonationTag('En cours', '#ff4500'));
        $manager->persist(new DonationTag('Terminé', '#00bfff'));
        $manager->persist(new DonationTag('Annulé', '#00ffd9'));
        $manager->persist(new DonationTag('Erreur', '#ff0000'));
        $manager->persist(new DonationTag('A valider', '#a9a9a9'));

        $manager->flush();
    }
}
