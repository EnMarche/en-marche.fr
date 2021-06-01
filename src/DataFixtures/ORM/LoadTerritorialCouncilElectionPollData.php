<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\TerritorialCouncil\ElectionPoll\PollChoice;
use App\Entity\TerritorialCouncil\ElectionPoll\Vote;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTerritorialCouncilElectionPollData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $electionPoll = new Poll(Genders::FEMALE);
        /** @var Election $election */
        $election = $this->getReference('election_coTerr_75');
        $election->setElectionPoll($electionPoll);
//        $manager->persist($electionPoll);

        $pollChoice0 = new PollChoice($electionPoll, 0);
        $pollChoice1 = new PollChoice($electionPoll, 1);
        $pollChoice2 = new PollChoice($electionPoll, 2);
        $electionPoll->addChoice($pollChoice0);
        $electionPoll->addChoice($pollChoice1);
        $electionPoll->addChoice($pollChoice2);

        $pollVote1 = new Vote($pollChoice0, $this->getReference('member_1_coTerr_75'));
        $pollVote2 = new Vote($pollChoice0, $this->getReference('member_2_coTerr_75'));
        $pollVote3 = new Vote($pollChoice1, $this->getReference('member_3_coTerr_75'));
        $pollVote4 = new Vote($pollChoice2, $this->getReference('member_4_coTerr_75'));
        $pollVote5 = new Vote($pollChoice2, $this->getReference('member_5_coTerr_75'));
        $pollVote6 = new Vote($pollChoice2, $this->getReference('member_6_coTerr_75'));
        $pollVote7 = new Vote($pollChoice0, $this->getReference('member_7_coTerr_75'));
        $manager->persist($pollVote1);
        $manager->persist($pollVote2);
        $manager->persist($pollVote3);
        $manager->persist($pollVote4);
        $manager->persist($pollVote5);
        $manager->persist($pollVote6);
        $manager->persist($pollVote7);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadTerritorialCouncilElectionData::class,
            LoadTerritorialCouncilMembershipData::class,
        ];
    }
}
