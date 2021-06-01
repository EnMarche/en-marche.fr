<?php

namespace App\DataFixtures\ORM;

use App\Entity\IdeasWorkshop\Consultation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadIdeaConsultationData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $consultationRetirement = new Consultation(
            'Consultation sur les retraites',
            'https://fr.lipsum.com/',
            new \DateTime('-4 days'),
            new \DateTime('tomorrow'),
            2,
            true
        );

        $this->addReference('consultation-retirement', $consultationRetirement);

        $consultationHousingPolicy = new Consultation(
            'Consultation sur la politique du logement',
            'https://google.fr/',
            new \DateTime('-3 days'),
            new \DateTime('yesterday'),
            3
        );

        $this->addReference('consultation-housing-policy', $consultationHousingPolicy);

        $manager->persist($consultationRetirement);
        $manager->persist($consultationHousingPolicy);

        $manager->flush();
    }
}
