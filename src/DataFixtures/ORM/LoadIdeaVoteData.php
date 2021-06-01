<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\Vote;
use App\Entity\IdeasWorkshop\VoteTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadIdeaVoteData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_vote');

        $adherents = [
            $this->getReference('adherent-2'),
            $this->getReference('adherent-3'),
            $this->getReference('adherent-4'),
            $this->getReference('adherent-5'),
            $this->getReference('adherent-6'),
            $this->getReference('adherent-7'),
            $this->getReference('adherent-9'),
            $this->getReference('adherent-13'),
        ];

        $ideas = [
            $this->getReference('idea-help-ecology'),
            $this->getReference('idea-peace'),
            $this->getReference('idea-help-people'),
        ];

        foreach ($adherents as $key => $adherent) {
            foreach (VoteTypeEnum::toArray() as $type) {
                foreach ($ideas as $idea) {
                    if (
                        !(\in_array($key, [4, 5, 7]) && VoteTypeEnum::INNOVATIVE === $type)
                        && !(\in_array($key, [2, 3, 5]) && VoteTypeEnum::FEASIBLE === $type)
                        && !(2 === $key && VoteTypeEnum::IMPORTANT === $type)
                    ) {
                        $vote = new Vote();

                        $vote->setType($type);
                        $vote->setAuthor($adherent);
                        $idea->addVote($vote);

                        $manager->persist($vote);
                    }
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadIdeaData::class,
        ];
    }
}
