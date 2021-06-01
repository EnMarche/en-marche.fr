<?php

namespace App\DataFixtures\ORM;

use App\Entity\CitizenProjectSkill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadCitizenProjectSkillData extends Fixture
{
    public const SKILLS = [
        'CPS001' => 'Paysage',
        'CPS002' => 'Jardinage / Botanique',
        'CPS003' => 'Gestion des parcs nationaux',
        'CPS004' => 'Isolation thermique et acoustique',
        'CPS005' => 'Horticulture',
        'CPS006' => 'Gestion des déchets',
        'CPS007' => 'Professeurs du primaire',
        'CPS008' => 'Professeurs du secondaire',
        'CPS009' => 'Professeurs d’université',
        'CPS010' => 'Chercheurs',
        'CPS011' => 'Éducateurs et animateurs spécialisés',
        'CPS012' => 'Parents d’élèves',
        'CPS013' => 'Artistes',
        'CPS014' => 'Professionnels de la culture',
        'CPS015' => 'Architecte',
        'CPS016' => 'Peintres et sculpteurs',
        'CPS017' => 'Solidarité intergénérationnel ',
        'CPS018' => 'Lutte contre l’exclusion',
        'CPS019' => 'Egalité des chances',
        'CPS020' => 'Conversations',
        'CPS021' => 'Médecin',
        'CPS022' => 'Infirmier-e',
        'CPS023' => 'Aide-soignant',
        'CPS024' => 'Psychologue',
        'CPS025' => 'Secouriste',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::SKILLS as $code => $name) {
            $skill = new CitizenProjectSkill($name);
            $manager->persist($skill);
            $this->addReference(strtolower($code), $skill);
        }
        $manager->flush();
    }
}
