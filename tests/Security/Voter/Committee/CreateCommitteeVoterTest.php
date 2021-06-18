<?php

namespace Tests\App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\Committee\CreateCommitteeVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class CreateCommitteeVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var ElectedRepresentativeRepository|MockObject
     */
    private $electedRepresentativeRepository;

    protected function setUp(): void
    {
        $this->electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->electedRepresentativeRepository = null;

        parent::tearDown();
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, CommitteePermissions::CREATE];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new CreateCommitteeVoter($this->electedRepresentativeRepository);
    }

    public function testAdherentCannotCreateIfMinor()
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())
            ->method('isMinor')
            ->willReturn(true)
        ;

        $this->assertElectedRepresentativeRepositoryBehavior(false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testAdherentCannotCreateIfNotCertified()
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())
            ->method('isMinor')
            ->willReturn(false)
        ;
        $adherent->expects($this->once())
            ->method('isCertified')
            ->willReturn(false)
        ;

        $this->assertElectedRepresentativeRepositoryBehavior(false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testAdherentCannotCreateIfSupervisor()
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())
            ->method('isMinor')
            ->willReturn(false)
        ;
        $adherent->expects($this->once())
            ->method('isCertified')
            ->willReturn(true)
        ;
        $adherent->expects($this->once())
            ->method('isSupervisor')
            ->willReturn(true)
        ;

        $this->assertElectedRepresentativeRepositoryBehavior(false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testAdherentCannotCreateIfHasActiveParliamentaryMandate()
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())
            ->method('isMinor')
            ->willReturn(false)
        ;
        $adherent->expects($this->once())
            ->method('isCertified')
            ->willReturn(true)
        ;
        $adherent->expects($this->once())
            ->method('isSupervisor')
            ->willReturn(false)
        ;

        $this->assertElectedRepresentativeRepositoryBehavior(true, $adherent, false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testAdherentWithCorrectConditionsCanCreate()
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())
            ->method('isMinor')
            ->willReturn(false)
        ;
        $adherent->expects($this->once())
            ->method('isCertified')
            ->willReturn(true)
        ;
        $adherent->expects($this->once())
            ->method('isSupervisor')
            ->willReturn(false)
        ;

        $this->assertElectedRepresentativeRepositoryBehavior(true, $adherent, true);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testReferentCanCreate()
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())
            ->method('isReferent')
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::CREATE);
    }

    private function assertElectedRepresentativeRepositoryBehavior(
        bool $isCalled,
        Adherent $adherent = null,
        bool $allowedToCreate = false
    ): void {
        if ($isCalled) {
            $this->electedRepresentativeRepository->expects($this->once())
                ->method('hasActiveParliamentaryMandate')
                ->with($adherent)
                ->willReturn(!$allowedToCreate)
            ;
        } else {
            $this->electedRepresentativeRepository->expects($this->never())
                ->method('hasActiveParliamentaryMandate')
            ;
        }
    }
}
