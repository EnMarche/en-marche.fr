<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Reporting\CommitteeMembershipAction;
use App\Entity\Reporting\CommitteeMembershipHistory;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommitteeMembershipHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $memberships = $manager->getRepository(CommitteeMembership::class)->findAll();

        foreach ($memberships as $membership) {
            $event = new CommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN(), $membership->getSubscriptionDate());

            $manager->persist($event);
        }

        $manager->persist($this->createJoinHistory($this->getReference('adherent-3'), $this->getReference('committee-1'), '2018-01-18'));
        $manager->persist($this->createLeaveHistory($this->getReference('adherent-3'), $this->getReference('committee-1'), '2018-02-18'));

        $manager->persist($this->createJoinHistory($this->getReference('adherent-4'), $this->getReference('committee-1'), '2018-03-18'));
        $manager->persist($this->createLeaveHistory($this->getReference('adherent-4'), $this->getReference('committee-1'), '2018-04-18'));

        $manager->persist($this->createJoinHistory($this->getReference('adherent-7'), $this->getReference('committee-4'), '2017-12-11'));
        $manager->persist($this->createLeaveHistory($this->getReference('adherent-7'), $this->getReference('committee-4'), '2017-12-13'));

        $manager->persist($this->createJoinHistory($this->getReference('adherent-7'), $this->getReference('committee-3'), '2017-10-18'));
        $manager->persist($this->createLeaveHistory($this->getReference('adherent-7'), $this->getReference('committee-3'), '2018-04-19'));

        $manager->flush();
    }

    private function createJoinHistory(
        Adherent $adherent,
        Committee $committee,
        string $date
    ): CommitteeMembershipHistory {
        return $this->createHistory(CommitteeMembershipAction::JOIN(), $adherent, $committee, $date);
    }

    private function createLeaveHistory(
        Adherent $adherent,
        Committee $committee,
        string $date
    ): CommitteeMembershipHistory {
        return $this->createHistory(CommitteeMembershipAction::LEAVE(), $adherent, $committee, $date);
    }

    private function createHistory(
        CommitteeMembershipAction $action,
        Adherent $adherent,
        Committee $committee,
        string $date
    ): CommitteeMembershipHistory {
        $membership = $adherent->getMembershipFor($committee);

        return new CommitteeMembershipHistory($membership, $action, new Chronos($date));
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeData::class,
        ];
    }
}
