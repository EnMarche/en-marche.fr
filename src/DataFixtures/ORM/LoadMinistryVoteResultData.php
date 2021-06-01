<?php

namespace App\DataFixtures\ORM;

use App\Election\VoteListNuanceEnum;
use App\Entity\Election\MinistryListTotalResult;
use App\Entity\Election\MinistryVoteResult;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadMinistryVoteResultData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $result = new MinistryVoteResult(
            $this->getReference('city-lille'),
            $this->getReference('round-1-municipal')
        );
        $result->setUpdatedBy($this->getReference('adherent-8'));
        $result->setUpdatedAt(new \DateTime());
        $result->setRegistered(1000);
        $result->setParticipated(666);
        $result->setExpressed(660);
        $result->setAbstentions(140);

        $list = new MinistryListTotalResult();
        $list->setAdherentCount(10);
        $list->setEligibleCount(7);
        $list->setLabel('Liste 1');
        $list->setNuance(VoteListNuanceEnum::REM);
        $list->setTotal(5);
        $list->setPosition(1);
        $list->setCandidateFirstName('Michel');
        $list->setCandidateLastName('Dupont');
        $list->setOutgoingMayor(true);

        $result->addListTotalResult($list);

        $list = new MinistryListTotalResult();
        $list->setLabel('Liste 2');
        $list->setNuance(VoteListNuanceEnum::ECO);
        $list->setTotal(5);
        $list->setPosition(2);
        $list->setCandidateFirstName('Jean-Bernard');
        $list->setCandidateLastName('Devops');

        $result->addListTotalResult($list);

        $manager->persist($result);

        $result = new MinistryVoteResult(
            $this->getReference('city-lille'),
            $this->getReference('round-1-municipal-2014')
        );
        $result->setUpdatedBy($this->getReference('adherent-8'));
        $result->setUpdatedAt(new \DateTime());
        $result->setRegistered(900);
        $result->setParticipated(566);
        $result->setExpressed(560);
        $result->setAbstentions(40);

        $list = new MinistryListTotalResult();
        $list->setAdherentCount(5);
        $list->setEligibleCount(4);
        $list->setLabel('Liste 1');
        $list->setNuance(VoteListNuanceEnum::REM);
        $list->setTotal(5);
        $list->setPosition(1);
        $list->setCandidateFirstName('Rémi');
        $list->setCandidateLastName('Gautier');

        $result->addListTotalResult($list);

        $list = new MinistryListTotalResult();
        $list->setLabel('Liste 2');
        $list->setNuance(VoteListNuanceEnum::UC);
        $list->setTotal(10);
        $list->setPosition(2);

        $result->addListTotalResult($list);

        $manager->persist($result);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadElectionData::class,
            LoadCityData::class,
            LoadAdherentData::class,
        ];
    }
}
