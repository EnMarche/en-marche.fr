<?php

namespace App\DataFixtures\ORM;

use App\Entity\Election\VotePlaceResult;
use App\Entity\ElectionRound;
use App\Entity\VotePlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadVotePlaceResultData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var VotePlace $assessor1VotePlace */
        $assessor1VotePlace = $this->getReference('vote-place-lille-wazemmes');

        /** @var ElectionRound $round1 */
        $round1 = $this->getReference('round-1-municipal');
        /** @var ElectionRound $round2 */
        $round2 = $this->getReference('round-2-municipal');

        $manager->persist($this->createVoteResult(
            $assessor1VotePlace,
            $round1,
            1000,
            300,
            500,
            400
        ));

        $manager->persist($this->createVoteResult(
            $assessor1VotePlace,
            $round2,
            1000,
            200,
            600,
            500
        ));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadElectionData::class,
            LoadVotePlaceData::class,
            LoadVoteResultListCollectionData::class,
        ];
    }

    private function createVoteResult(
        VotePlace $votePlace,
        ElectionRound $electionRound,
        int $registered,
        int $abstentions,
        int $participated,
        int $expressed
    ): VotePlaceResult {
        $voteResult = new VotePlaceResult($votePlace, $electionRound);

        $voteResult->updateLists($this->getReference('vote-result-list-collection-lille'));

        $voteResult->setRegistered($registered);
        $voteResult->setAbstentions($abstentions);
        $voteResult->setParticipated($participated);
        $voteResult->setExpressed($expressed);

        return $voteResult;
    }
}
