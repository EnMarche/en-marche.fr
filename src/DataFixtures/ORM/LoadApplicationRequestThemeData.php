<?php

namespace App\DataFixtures\ORM;

use App\Entity\ApplicationRequest\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadApplicationRequestThemeData extends Fixture
{
    private const THEMES = [
        'application-theme-01' => 'Urbanisme',
        'application-theme-02' => 'Logement',
        'application-theme-03' => 'Attractivités économiques',
        'application-theme-04' => 'Cohésion sociale',
        'application-theme-05' => 'Europe et coopération internationale',
        'application-theme-06' => 'Sécurité',
        'application-theme-07' => 'Mobilités',
        'application-theme-08' => 'Environnement',
        'application-theme-09' => 'Enseignement',
        'application-theme-10' => 'Santé publique',
        'application-theme-11' => 'Autre(s)',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (static::THEMES as $reference => $name) {
            $manager->persist($theme = new Theme($name));

            $this->setReference($reference, $theme);
        }

        $manager->flush();
    }
}
