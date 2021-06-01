<?php

namespace App\DataFixtures\ORM;

use App\Entity\ApplicationRequest\TechnicalSkill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadApplicationRequestTechnicalSkillData extends Fixture
{
    private const SKILLS = [
        'application-skill-01' => 'Communication',
        'application-skill-02' => 'Management',
        'application-skill-03' => 'Comptabilité',
        'application-skill-04' => 'Animation',
        'application-skill-05' => 'Graphisme',
        'application-skill-06' => 'Mobilisation',
        'application-skill-07' => 'Conformité et juridique',
        'application-skill-08' => 'Autre(s)',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (static::SKILLS as $reference => $name) {
            $manager->persist($skill = new TechnicalSkill($name));

            $this->setReference($reference, $skill);
        }

        $manager->flush();
    }
}
