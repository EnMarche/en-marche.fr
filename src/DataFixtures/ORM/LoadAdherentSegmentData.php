<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentSegment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentSegmentData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $segment = new AdherentSegment();

        $segment->setLabel('ma super liste');
        $segment->setMemberIds([
            $this->getReference('adherent-7')->getId(),
            $this->getReference('adherent-13')->getId(),
        ]);
        $segment->setAuthor($this->getReference('adherent-8'));

        $manager->persist($segment);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
