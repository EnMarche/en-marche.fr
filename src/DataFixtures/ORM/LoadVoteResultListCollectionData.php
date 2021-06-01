<?php

namespace App\DataFixtures\ORM;

use App\Entity\Election\VoteResultList;
use App\Entity\Election\VoteResultListCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadVoteResultListCollectionData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $listCollection = new VoteResultListCollection(
            $this->getReference('city-lille'),
            $this->getReference('round-1-municipal')
        );

        $listCollection->addList($this->createList('Liste 1', 'REM', 1));
        $listCollection->addList($this->createList('Liste 2', '', 2));
        $listCollection->addList($this->createList('Liste 3', '', 3));

        $this->setReference('vote-result-list-collection-lille', $listCollection);

        $manager->persist($listCollection);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCityData::class,
            LoadElectionData::class,
        ];
    }

    private function createList(string $label, string $nuance, int $position): VoteResultList
    {
        $list = new VoteResultList();

        $list->setLabel($label);
        $list->setNuance($nuance);
        $list->setPosition($position);

        return $list;
    }
}
