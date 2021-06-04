<?php

namespace Tests\App\Security\Voter;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\BaseGroup;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\ShowGroupVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\UuidInterface;

class ShowGroupVoterTest extends AbstractAdherentVoterTest
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new ShowGroupVoter();
    }

    public function provideAnonymousCases(): iterable
    {
        // Not approved groups should been shown to anonymous
        $notApprovedCommittee = $this->getGroupMock(Committee::class, false);

        yield [false, true, CommitteePermissions::SHOW, $notApprovedCommittee];

        // Approved groups should be shown to anonymous
        $approvedCommittee = $this->getGroupMock(Committee::class, true);

        yield [true, false, CommitteePermissions::SHOW, $approvedCommittee];
    }

    /**
     * @dataProvider provideGroupCases
     */
    public function testAdherentIsGrantedIfGroupIsApproved(string $groupClass, bool $approved, string $attribute)
    {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getGroupMock($groupClass, $approved, false);

        $this->assertGrantedForAdherent($approved, !$approved, $adherent, $attribute, $group);
    }

    /**
     * @dataProvider provideGroupCases
     */
    public function testAdherentIsGrantedWhenGroupIsNotApprovedIfCreator(
        string $groupClass,
        bool $approved,
        string $attribute
    ) {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getGroupMock($groupClass, $approved, true);

        $this->assertGrantedForAdherent(true, !$approved, $adherent, $attribute, $group);
    }

    public function provideGroupCases(): iterable
    {
        yield [Committee::class, true, CommitteePermissions::SHOW];
        yield [Committee::class, false, CommitteePermissions::SHOW];
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(bool $getUuidIsCalled): Adherent
    {
        $adherent = $this->createAdherentMock();

        if ($getUuidIsCalled) {
            $adherent->expects($this->once())
                ->method('getUuid')
                ->willReturn($this->createMock(UuidInterface::class))
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getUuid')
            ;
        }

        return $adherent;
    }

    /**
     * @return BaseGroup|MockObject
     */
    private function getGroupMock(string $groupClass, bool $approved, bool $withCreator = null): BaseGroup
    {
        $group = $this->createMock($groupClass);

        $group->expects($this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        if ($approved) {
            $group->expects($this->never())
                ->method('isCreatedBy')
            ;
        } elseif (null !== $withCreator) {
            $group->expects($this->once())
                ->method('isCreatedBy')
                ->with($this->isInstanceOf(UuidInterface::class))
                ->willReturn($withCreator)
            ;
        }

        return $group;
    }
}
