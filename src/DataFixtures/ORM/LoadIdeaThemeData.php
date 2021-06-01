<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadIdeaThemeData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_theme');

        $themeArmyDefense = new Theme(
            'Armées et défense',
            'default.png',
            true
        );
        $this->addReference('theme-army-defense', $themeArmyDefense);

        $themeTreasure = new Theme(
            'Trésorerie',
            'tresory.png',
            true,
            2
        );
        $this->addReference('theme-treasure', $themeTreasure);

        $themeEcology = new Theme(
            'Écologie',
            'ecology.png',
            true,
            3
        );
        $this->addReference('theme-ecology', $themeEcology);

        $themeNotPublished = new Theme(
            'Thème non publié',
            'not_published.png',
            false,
            4
        );
        $this->addReference('theme-not-published', $themeNotPublished);

        $manager->persist($themeArmyDefense);
        $manager->persist($themeTreasure);
        $manager->persist($themeEcology);
        $manager->persist($themeNotPublished);

        $manager->flush();
    }
}
