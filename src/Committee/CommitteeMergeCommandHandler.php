<?php

namespace App\Committee;

use App\Committee\Event\CommitteeMergeEvent;
use App\Entity\AdherentMandate\AbstractAdherentMandate;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacyInvitation;
use App\Entity\CommitteeMembership;
use App\Entity\Reporting\CommitteeMembershipAction;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Entity\Reporting\CommitteeMergeHistory;
use App\Events;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\CommitteeCandidacyRepository;
use App\Repository\CommitteeMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeMergeCommandHandler
{
    private $dispatcher;
    private $em;
    private $committeeMembershipRepository;
    private $committeeCandidacyRepository;
    private $mandateRepository;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $em,
        CommitteeMembershipRepository $committeeMembershipRepository,
        CommitteeCandidacyRepository $committeeCandidacyRepository,
        CommitteeAdherentMandateRepository $mandateRepository
    ) {
        $this->dispatcher = $dispatcher;
        $this->em = $em;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->committeeCandidacyRepository = $committeeCandidacyRepository;
        $this->mandateRepository = $mandateRepository;
    }

    public function countNewMembers(CommitteeMergeCommand $committeeMergeCommand): int
    {
        $sourceCommittee = $committeeMergeCommand->getSourceCommittee();
        $destinationCommittee = $committeeMergeCommand->getDestinationCommittee();

        return \count($this
            ->committeeMembershipRepository
            ->findMembersToMerge($sourceCommittee, $destinationCommittee)
        );
    }

    public function handle(CommitteeMergeCommand $committeeMergeCommand): void
    {
        $sourceCommittee = $committeeMergeCommand->getSourceCommittee();
        $destinationCommittee = $committeeMergeCommand->getDestinationCommittee();
        $administrator = $committeeMergeCommand->getMergedBy();

        try {
            $this->em->beginTransaction();

            // demote host memberships to normal member for both committees
            $sourceHostMemberships = $this->committeeMembershipRepository->findHostMemberships($sourceCommittee);
            foreach ($sourceHostMemberships as $membership) {
                $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
            }
            $destinationHostMemberships = $this->committeeMembershipRepository->findHostMemberships($destinationCommittee);
            foreach ($destinationHostMemberships as $membership) {
                $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
            }

            $newFollowers = $this->committeeMembershipRepository->findMembersToMerge($sourceCommittee, $destinationCommittee);

            $mergedMemberships = [];
            foreach ($newFollowers as $newFollower) {
                $adherent = $newFollower->getAdherent();
                $this->em->persist($membership = $adherent->followCommittee($destinationCommittee, $newFollower->getSubscriptionDate()));

                $this->em->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN()));

                $mergedMemberships[] = $membership;
            }

            $this
                ->committeeMembershipRepository
                ->findHostMemberships($sourceCommittee)
                ->map(function (CommitteeMembership $membership) {
                    $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
                })
            ;

            $sourceCommittee->close();

            $this->em->persist($this->createCommitteeMergeHistory(
                $sourceCommittee,
                $destinationCommittee,
                $mergedMemberships,
                $administrator
            ));

            $this->mandateRepository->closeCommitteeMandate($sourceCommittee, AbstractAdherentMandate::REASON_COMMITTEE_MERGE);
            $this->mandateRepository->closeCommitteeMandate($destinationCommittee, AbstractAdherentMandate::REASON_COMMITTEE_MERGE);

            $this->em->flush();

            $this->transferCandidatures($sourceCommittee, $destinationCommittee);

            $this->transferVotingMemberships(
                $destinationCommittee,
                $this->committeeMembershipRepository->findVotingMemberships($sourceCommittee)
            );

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $this->dispatchCommitteeUpdate($sourceCommittee);
        $this->dispatchCommitteeUpdate($destinationCommittee);

        $this->dispatcher->dispatch(new CommitteeMergeEvent($sourceCommittee, $destinationCommittee));
    }

    public function revert(CommitteeMergeHistory $committeeMergeHistory, Administrator $administrator): void
    {
        $sourceCommittee = $committeeMergeHistory->getSourceCommittee();
        $destinationCommittee = $committeeMergeHistory->getDestinationCommittee();
        $mergedMemberships = $committeeMergeHistory->getMergedMemberships();

        try {
            $this->em->beginTransaction();

            $committeeMergeHistory->revert($administrator);
            $sourceCommittee->approved();

            $this->em->flush();

            $this->transferVotingMemberships($sourceCommittee, array_filter($mergedMemberships, function (CommitteeMembership $membership) {
                return $membership->isVotingCommittee();
            }));
            $this->revertDestinationCommitteeMemberships($destinationCommittee, $mergedMemberships);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $this->dispatchCommitteeUpdate($sourceCommittee);
        $this->dispatchCommitteeUpdate($destinationCommittee);

        $this->dispatcher->dispatch(new CommitteeMergeEvent($sourceCommittee, $destinationCommittee));
    }

    private function revertDestinationCommitteeMemberships(Committee $committee, array $memberships): void
    {
        foreach ($memberships as $membership) {
            $committee->decrementMembersCount();

            $this->em->remove($membership);

            $this->em->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::LEAVE()));
        }

        $this->em->flush();
    }

    private function dispatchCommitteeUpdate(Committee $committee): void
    {
        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
    }

    private function transferVotingMemberships(Committee $committee, array $votingMemberships): void
    {
        if (!$votingMemberships) {
            return;
        }

        $votingAdherents = array_map(static function (CommitteeMembership $membership) {
            if ($election = $membership->getCommittee()->getCommitteeElection()) {
                $membership->removeCommitteeCandidacyForElection($election);
            }

            return $membership->getAdherent();
        }, $votingMemberships);

        $this->committeeMembershipRepository->updateVoteStatusForAdherents(current($votingMemberships)->getCommittee(), $votingAdherents, false);

        $this->em->flush();

        $this->committeeMembershipRepository->updateVoteStatusForAdherents($committee, $votingAdherents, true);
    }

    private function createCommitteeMembershipHistory(
        CommitteeMembership $membership,
        CommitteeMembershipAction $action
    ): CommitteeMembershipHistory {
        return new CommitteeMembershipHistory($membership, $action);
    }

    private function createCommitteeMergeHistory(
        Committee $sourceCommittee,
        Committee $destinationCommittee,
        array $mergedMemberships,
        Administrator $administrator
    ): CommitteeMergeHistory {
        return new CommitteeMergeHistory($sourceCommittee, $destinationCommittee, $mergedMemberships, $administrator);
    }

    private function transferCandidatures(?Committee $sourceCommittee, ?Committee $destinationCommittee)
    {
        $sourceElection = $sourceCommittee->getCurrentElection();
        $destinationElection = $destinationCommittee->getCurrentElection();

        // Move all candidacies from the source committee to new committee if both have the same election type
        if ($sourceElection && $destinationElection && $sourceElection->getDesignation() === $destinationElection->getDesignation()) {
            $candidacies = $this->committeeCandidacyRepository->findByCommittee($sourceCommittee, $sourceElection->getDesignation());

            foreach ($candidacies as $candidacy) {
                if ($newMembership = $candidacy->getAdherent()->getMembershipFor($destinationCommittee)) {
                    $candidacy->setCommitteeMembership($newMembership);
                    $candidacy->setCommitteeElection($destinationElection);

                    /** @var CommitteeCandidacyInvitation $invitation */
                    if (
                        ($invitation = $candidacy->getFirstInvitation())
                        && ($newInvitedMembership = $invitation->getMembership()->getAdherent()->getMembershipFor($destinationCommittee))
                    ) {
                        $invitation->setMembership($newInvitedMembership);
                    }
                }
            }

            $this->em->flush();
        }
    }
}
