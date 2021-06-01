<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTerritorialCouncilElectionData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');
        $coTerrParis->setCurrentElection($election = new Election($this->getReference('designation-6')));
        $election->setElectionMode(DesignationVoteModeEnum::VOTE_MODE_ONLINE);
        $this->setReference('election_coTerr_75', $election);

        /** @var TerritorialCouncil $coTerr */
        $coTerr = $this->getReference('coTerr_92');
        $coTerr->setCurrentElection($election = new Election($this->getReference('designation-7')));
        $election->setElectionMode(DesignationVoteModeEnum::VOTE_MODE_ONLINE);

        /** @var TerritorialCouncil $coTerr */
        $coTerr = $this->getReference('coTerr_59');
        $coTerr->setCurrentElection($election = new Election($this->getReference('designation-10')));
        $election->setElectionMode(DesignationVoteModeEnum::VOTE_MODE_ONLINE);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadDesignationData::class,
            LoadTerritorialCouncilData::class,
        ];
    }
}
