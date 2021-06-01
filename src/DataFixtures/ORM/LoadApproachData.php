<?php

namespace App\DataFixtures\ORM;

use App\Entity\ProgrammaticFoundation\Approach;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadApproachData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $subApproach1 = $this->getReference('sub-approach-0');
        $subApproach1->setPosition(1);

        $subApproach2 = $this->getReference('sub-approach-1');
        $subApproach2->setPosition(2);

        $manager->persist($subApproach1);
        $manager->persist($subApproach2);

        $approach = new Approach(
            1,
            'Améliorer le quotidien',
            "Considérant cette rigueur induite, je suggère fortement d'anticiper systématiquement les stratégies imaginables, rapidement."
        );
        $approach->addSubApproach($subApproach1);
        $approach->addSubApproach($subApproach2);

        $manager->persist($approach);

        $subApproach3 = $this->getReference('sub-approach-2');
        $subApproach3->setPosition(1);

        $manager->persist($subApproach3);

        $approach = new Approach(
            2,
            'Changer de méthode pour changer les choses',
            "Malgré la dualité de la situation contextuelle, il faut uniformiser l'ensemble des options imaginables, à court terme."
        );
        $approach->addSubApproach($subApproach3);

        $manager->persist($approach);

        $subApproach4 = $this->getReference('sub-approach-3');
        $subApproach4->setPosition(1);

        $subApproach5 = $this->getReference('sub-approach-4');
        $subApproach5->setPosition(2);

        $manager->persist($subApproach4);
        $manager->persist($subApproach5);

        $approach = new Approach(
            3,
            'Faire de la commune un modèle écologique', "Si vous voulez mon avis concernant la baisse de confiance actuelle, je suggère fortement d'examiner la globalité des actions déclinables, parce qu'il est temps d'agir."
        );
        $approach->addSubApproach($subApproach4);
        $approach->addSubApproach($subApproach5);

        $manager->persist($approach);

        $subApproach6 = $this->getReference('sub-approach-5');
        $subApproach6->setPosition(1);

        $manager->persist($subApproach6);

        $approach = new Approach(
            4,
            'Préparer l\'avenir au-delà de notre mandat', "Quelle que soit l'inertie présente, il est préférable de prendre en considération systématiquement les décisions possibles, parce que nous ne faisons plus le même métier."
        );
        $approach->addSubApproach($subApproach6);

        $manager->persist($approach);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadSubApproachData::class,
        ];
    }
}
