<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Theme;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaThemeData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $themeArmyDefense = new Theme(
            'Armées et défense',
            true
        );
        $this->addReference('theme-army-defense', $themeArmyDefense);

        $themeTreasure = new Theme(
            'Trésorerie',
            true
        );
        $this->addReference('theme-treasure', $themeTreasure);

        $themeNotPublished = new Theme(
            'Thème non publié'
        );
        $this->addReference('theme-not-published', $themeNotPublished);

        $manager->persist($themeArmyDefense);
        $manager->persist($themeTreasure);
        $manager->persist($themeNotPublished);

        $manager->flush();
    }
}
