<?php

namespace Tests\App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Repository\AdherentRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\Committee\FollowerCommitteeVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\UuidInterface;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class FollowCommitteeVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var AdherentRepository|MockObject
     */
    private $adherentRepository;

    protected function setUp(): void
    {
        $this->adherentRepository = $this->createMock(AdherentRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;

        parent::tearDown();
    }

    public function provideAnonymousCases(): iterable
    {
        yield 'Anonymous cannot follow committees' => [false, true, CommitteePermissions::FOLLOW, $this->getCommitteeMock()];
        yield 'Anonymous cannot unfollow committees' => [false, true, CommitteePermissions::UNFOLLOW, $this->getCommitteeMock()];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new FollowerCommitteeVoter($this->adherentRepository);
    }

    public function testAdherentCannotFollowCommitteeIfAlreadyFollowing()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::FOLLOW, $committee);
    }

    public function testAdherentCannotFollowCommitteeIfNotApproved()
    {
        $committee = $this->getCommitteeMock(false);
        $adherent = $this->getAdherentMock();

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::FOLLOW, $committee);
    }

    public function testAdherentCanFollowCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::FOLLOW, $committee);
    }

    public function testAdherentCannotUnFollowCommitteeIfNotAlreadyFollowing()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testAdherentCannotUnfollowCommitteeIfNotApproved()
    {
        $committee = $this->getCommitteeMock(false);
        $adherent = $this->getAdherentMock();

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testSupervisorCannotUnfollowCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, true);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testAdherentCanUnfollowCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, false);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testHostCannotUnfollowCommitteeIfOnlyHost()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, false, true);

        $this->assertRepositoryBehavior(false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testHostCanUnfollowCommitteeIfManyHosts()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, false, true);

        $this->assertRepositoryBehavior(true);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(
        Committee $committee = null,
        bool $isFollower = null,
        bool $isSupervisor = null,
        bool $isHost = false
    ): Adherent {
        $adherent = $this->createAdherentMock();

        if ($committee) {
            $adherent->expects($this->once())
                ->method('getMembershipFor')
                ->with($committee)
                ->willReturn($this->getMembershipMock($isFollower, $isSupervisor, $isHost))
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getMembershipFor')
            ;
        }

        return $adherent;
    }

    /**
     * @return CommitteeMembership|MockObject|null
     */
    private function getMembershipMock(
        ?bool $isFollower,
        ?bool $isSupervisor,
        bool $isHost = false
    ): ?CommitteeMembership {
        if (!$isFollower) {
            return null;
        }

        $membership = $this->createMock(CommitteeMembership::class);

        if (null !== $isSupervisor) {
            $membership->expects($this->once())
                ->method('isSupervisor')
                ->willReturn($isSupervisor)
            ;

            if ($isSupervisor) {
                $membership->expects($this->never())
                    ->method('isFollower')
                ;
            } else {
                $membership->expects($this->once())
                    ->method('isFollower')
                    ->willReturn(!$isHost)
                ;
            }
        } else {
            $membership->expects($this->never())
                ->method('isSupervisor')
            ;
        }

        return $membership;
    }

    /**
     * @param bool $approved
     *
     * @return Committee|MockObject
     */
    private function getCommitteeMock(bool $approved = null): Committee
    {
        $committee = $this->createMock(Committee::class);

        if (null !== $approved) {
            $committee->expects($this->once())
                ->method('isApproved')
                ->willReturn($approved)
            ;
        } else {
            $committee->expects($this->never())
                ->method('isApproved')
            ;
        }

        $uuid = $this->createMock(UuidInterface::class);
        $uuid->expects($this->any())
            ->method('toString')
            ->willReturn('test')
        ;

        $committee->expects($this->any())
            ->method('getUuid')
            ->willReturn($uuid)
        ;

        return $committee;
    }

    private function assertRepositoryBehavior(?bool $manyHost): void
    {
        if (null !== $manyHost) {
            $this->adherentRepository->expects($this->once())
                ->method('countCommitteeHosts')
                ->with($this->isInstanceOf(Committee::class))
                ->willReturn($manyHost ? 2 : 1)
            ;
        } else {
            $this->adherentRepository->expects($this->never())
                ->method('countCommitteeHosts')
            ;
        }
    }
}
