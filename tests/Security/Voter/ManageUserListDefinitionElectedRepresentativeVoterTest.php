<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Geo\Zone;
use App\Entity\ReferentManagedArea;
use App\Entity\ReferentTag;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\ManageUserListDefinitionElectedRepresentativeVoter;
use App\UserListDefinition\UserListDefinitionPermissions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ManageUserListDefinitionElectedRepresentativeVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var ElectedRepresentativeRepository|MockObject
     */
    private $electedRepresentativeRepository;

    /** @var SessionInterface */
    private $session;

    protected function setUp(): void
    {
        $this->electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $this->session = $this->createMock(SessionInterface::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->electedRepresentativeRepository = null;
        $this->session = null;

        parent::tearDown();
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new ManageUserListDefinitionElectedRepresentativeVoter($this->electedRepresentativeRepository, $this->session);
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER, $this->createMock(ElectedRepresentative::class)];
    }

    public function providePermissions(): iterable
    {
        yield [UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER];
    }

    public function testAdherentIsNotGrantedIfNotReferent()
    {
        $adherent = $this->getAdherentMock(true, false);
        $electedRepresentative = $this->createMock(ElectedRepresentative::class);

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER,
            $electedRepresentative
        );
    }

    public function testAdherentIsNotGrantedIfNotInReferentArea()
    {
        $zone = new Zone('', '', '');
        $tags = [new ReferentTag(null, null, $zone)];
        $adherent = $this->getAdherentMock(true, true, $tags);
        $electedRepresentative = $this->createMock(ElectedRepresentative::class);

        $this->electedRepresentativeRepository->expects($this->once())
            ->method('isInReferentManagedArea')
            ->with($electedRepresentative, [$zone])
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(
            true,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER,
            $electedRepresentative
        );
    }

    public function testAdherentIsGrantedIfReferent()
    {
        $zone = new Zone('', '', '');
        $tags = [new ReferentTag(null, null, $zone)];
        $adherent = $this->getAdherentMock(true, true, $tags);
        $electedRepresentative = $this->createMock(ElectedRepresentative::class);

        $this->electedRepresentativeRepository->expects($this->once())
            ->method('isInReferentManagedArea')
            ->with($electedRepresentative, [$zone])
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(
            true,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER,
            $electedRepresentative
        );
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(
        bool $isReferentCalled = true,
        bool $isReferent = true,
        array $referentTags = []
    ): Adherent {
        $adherent = $this->createAdherentMock();

        if ($referentTags) {
            $referentManagedArea = $this->createPartialMock(ReferentManagedArea::class, ['getTags']);
            $referentManagedArea->expects($this->once())
                ->method('getTags')
                ->willReturn(new ArrayCollection($referentTags))
            ;

            $adherent->expects($this->once())
                ->method('getManagedArea')
                ->willReturn($referentManagedArea)
            ;
        }

        $adherent->expects($isReferentCalled ? $this->once() : $this->never())
            ->method('isReferent')
            ->willReturn($isReferent)
        ;

        return $adherent;
    }
}
