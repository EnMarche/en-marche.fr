<?php

namespace App\DataFixtures\ORM;

use App\Entity\ThematicCommunity\ThematicCommunity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadThematicCommunityData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tc1 = new ThematicCommunity();
        $tc1->setName('Santé');
        $tc1->setDescription('Une communauté autour de la santé');

        $this->addReference('tc-sante', $tc1);
        $manager->persist($tc1);

        $tc2 = new ThematicCommunity();
        $tc2->setName('Ecole');
        $tc2->setDescription('Une communtauté autour de l\'éducation');

        $this->addReference('tc-education', $tc2);
        $manager->persist($tc2);

        $tc3 = new ThematicCommunity();
        $tc3->setName('Inactive');
        $tc3->setDescription('Cette communauté n\'est pas active !');
        $tc3->setEnabled(false);

        $this->addReference('tc-disabled', $tc3);
        $manager->persist($tc3);

        $manager->flush();
    }
}
