<?php

namespace Tests\App\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\PostAddress;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\PoliticalCommitteeQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository;
use App\Repository\ElectedRepresentative\MandateRepository;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException;
use App\TerritorialCouncil\Exception\TerritorialCouncilQualityException;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use App\ValueObject\Genders;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class PoliticalCommitteeManagerTest extends TestCase
{
    /** @var MockObject|EntityManagerInterface */
    private $entityManager;
    /** @var MockObject|MandateRepository */
    private $mandateRepository;
    /** @var MockObject|PoliticalCommitteeMembershipRepository */
    private $membershipRepository;
    /** @var MockObject|TerritorialCouncilAdherentMandateRepository */
    private $tcMandateRepository;
    /** @var MockObject|TranslatorInterface */
    private $translator;
    /** @var PoliticalCommitteeManager */
    private $politicalCommitteeManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this
            ->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class))
        ;

        $this->mandateRepository = $this->createMock(MandateRepository::class);
        $this->membershipRepository = $this->createMock(PoliticalCommitteeMembershipRepository::class);
        $this->tcMandateRepository = $this->createMock(TerritorialCouncilAdherentMandateRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->politicalCommitteeManager = new PoliticalCommitteeManager(
            $this->entityManager,
            $this->mandateRepository,
            $this->membershipRepository,
            $this->tcMandateRepository,
            $this->translator
        );
    }

    protected function tearDown(): void
    {
        $this->entityManager = null;
        $this->mandateRepository = null;
        $this->membershipRepository = null;
        $this->translator = null;

        parent::tearDown();
    }

    public function testCreateMembershipSuccessfully(): void
    {
        $adherent = new Adherent();
        $politicalCommittee = new PoliticalCommittee('PC name', new TerritorialCouncil());
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $membership = $this->politicalCommitteeManager->createMembership($adherent, $politicalCommittee, $qualityName);

        $this->assertSame([$qualityName], $membership->getQualityNames());
        $this->assertSame($adherent, $membership->getAdherent());
        $this->assertSame($politicalCommittee, $membership->getPoliticalCommittee());
    }

    public function testCannotCreateMembershipWithInvalidQualityName(): void
    {
        $this->expectException(TerritorialCouncilQualityException::class);
        $adherent = new Adherent();
        $politicalCommittee = new PoliticalCommittee('PC name', new TerritorialCouncil());
        $qualityName = 'invalid';

        $this->politicalCommitteeManager->createMembership($adherent, $politicalCommittee, $qualityName);
    }

    public function testAddPoliticalCommitteeQualityDoNothingWhenNoPCMembershipAndTCMembership(): void
    {
        $adherent = new Adherent();
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testAddPoliticalCommitteeQualityWhenNoPCMembership(): void
    {
        $tc = new TerritorialCouncil();
        $pc = new PoliticalCommittee('My PC', $tc, true);
        $tc->setPoliticalCommittee($pc);
        $adherent = new Adherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($tc));
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertNotNull($adherent->getPoliticalCommitteeMembership());
        $this->assertSame($pc, $adherent->getPoliticalCommitteeMembership()->getPoliticalCommittee());
        $this->assertSame([$qualityName], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    public function testAddPoliticalCommitteeQuality(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([$qualityName], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    /**
     * @dataProvider provideOfficioQualities
     */
    public function tesCanAddOfficioQualities(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $can = $this->politicalCommitteeManager->canAddQuality($qualityName, $adherent);

        $this->assertTrue($can);
    }

    /**
     * @dataProvider provideNotPassingQualities
     */
    public function testCannotAddPoliticalCommitteeQualityWhenNotPassingQuality(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $can = $this->politicalCommitteeManager->canAddQuality($qualityName, $adherent);

        $this->assertFalse($can);
    }

    /**
     * @dataProvider provideElectedMemberQualities
     */
    public function testCannotAddElectedMemberQualityWhenNoTcMembership(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $can = $this->politicalCommitteeManager->canAddQuality($qualityName, $adherent);

        $this->assertFalse($can);
    }

    /**
     * @dataProvider provideElectedMemberQualities
     */
    public function testCannotAddElectedMemberQualityButWhenTcMandate(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership(new TerritorialCouncil()));

        $can = $this->politicalCommitteeManager->canAddQuality($qualityName, $adherent);

        $this->assertFalse($can);
    }

    /**
     * @dataProvider provideElectedMemberQualities
     */
    public function testCanAddElectedMemberQuality(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $territorialCouncil = new TerritorialCouncil();
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->tcMandateRepository
            ->expects($this->once())
            ->method('findActiveMandateWithQuality')
            ->with($adherent, $territorialCouncil, $qualityName)
            ->willReturn(TerritorialCouncilAdherentMandate::create(
                $territorialCouncil,
                $adherent,
                new \DateTime(),
                Genders::MALE,
                $qualityName
            ))
        ;
        $can = $this->politicalCommitteeManager->canAddQuality($qualityName, $adherent);

        $this->assertTrue($can);
    }

    public function testRemovePoliticalCommitteeQualitySuccessfully(): void
    {
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;
        $membership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $membership->addQuality(new PoliticalCommitteeQuality($qualityName));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($membership);

        $this->politicalCommitteeManager->removePoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    public function testRemovePoliticalCommitteeQualityDoNothingWhenNoPoliticalCommitteeMembership(): void
    {
        $adherent = new Adherent();

        $this->politicalCommitteeManager->removePoliticalCommitteeQuality($adherent, TerritorialCouncilQualityEnum::SENATOR);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testCreateMembershipFromTerritorialCouncilMembershipSuccessfully(): void
    {
        $territorialCouncil = new TerritorialCouncil();
        $politicalCommittee = new PoliticalCommittee('PC name', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $tcMembership = new TerritorialCouncilMembership();
        $tcMembership->setTerritorialCouncil($territorialCouncil);
        $tcMembership->setAdherent(new Adherent());
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::SENATOR, 'TC zone'));
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Committee'));

        $this->entityManager
            ->expects($this->any())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->any())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);

        $this->assertSame(null, $tcMembership->getAdherent()->getPoliticalCommitteeMembership());
    }

    public function testCannotCreateMembershipFromTerritorialCouncilMembershipIfNoOfficioQualities(): void
    {
        $territorialCouncil = new TerritorialCouncil();
        $politicalCommittee = new PoliticalCommittee('PC name', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $tcMembership = new TerritorialCouncilMembership();
        $tcMembership->setTerritorialCouncil($territorialCouncil);
        $tcMembership->setAdherent(new Adherent());
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Committee'));

        $this->entityManager
            ->expects($this->never())
            ->method('persist')
        ;
        $this->politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);
    }

    public function testHandleTerritorialCouncilMembershipUpdateDoNothing(): void
    {
        $adherent = new Adherent();

        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testHandleTerritorialCouncilMembershipUpdateRemovePCMembership(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->entityManager
            ->expects($this->any())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testHandleTerritorialCouncilMembershipUpdateRemovePCMembershipSuccessfully(): void
    {
        $territorialCouncil = new TerritorialCouncil();
        $politicalCommittee = new PoliticalCommittee('PC name', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $tcMembership = new TerritorialCouncilMembership();
        $tcMembership->setTerritorialCouncil($territorialCouncil);
        $tcMembership->setAdherent(new Adherent());
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::SENATOR, 'TC zone'));
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Committee'));
        $adherent = new Adherent();
        $adherent->setTerritorialCouncilMembership($tcMembership);

        $this->entityManager
            ->expects($this->any())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->any())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testCannotCreateMayorOrLeaderMembershipIfNoTerritorialCouncilMembership(): void
    {
        $this->expectException(PoliticalCommitteeMembershipException::class);
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('territorial_council.adherent_has_no_membership', $this->anything())
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    public function testCannotCreateMayorOrLeaderMembershipIfAdherentHasAlreadyPoliticalCommitteeMembership(): void
    {
        $this->expectException(PoliticalCommitteeMembershipException::class);
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setPoliticalCommitteeMembership(new PoliticalCommitteeMembership(new PoliticalCommittee('Test CoPol', new TerritorialCouncil())));
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('political_committee.membership.adherent_has_already', $this->anything())
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    public function testCannotCreateMayorOrLeaderMembershipIfMaxNumberExceeded(): void
    {
        $this->expectException(PoliticalCommitteeMembershipException::class);
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $politicalCommittee = new PoliticalCommittee('Test CoPol', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->membershipRepository
            ->expects($this->once())
            ->method('countLeaderAndMayorMembersFor')
            ->with($politicalCommittee)
            ->willReturn(3)
        ;
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('political_committee.membership.has_max_number_of_mayor_and_leader', $this->anything())
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    public function testCreateMayorMembership(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $politicalCommittee = new PoliticalCommittee('Test CoPol', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->membershipRepository
            ->expects($this->once())
            ->method('countLeaderAndMayorMembersFor')
            ->with($politicalCommittee)
            ->willReturn(1)
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(true)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertNotNull($adherent->getPoliticalCommitteeMembership());
        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));
        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));
    }

    public function testCreateLeaderMembership(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $politicalCommittee = new PoliticalCommittee('Test CoPol', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->membershipRepository
            ->expects($this->once())
            ->method('countLeaderAndMayorMembersFor')
            ->with($politicalCommittee)
            ->willReturn(1)
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(false)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertNotNull($adherent->getPoliticalCommitteeMembership());
        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));
        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));
    }

    public function testCannotRemoveMayorOrLeaderMembershipIfNoTerritorialCouncil(): void
    {
        $this->expectException(PoliticalCommitteeMembershipException::class);
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('territorial_council.adherent_has_no_membership', $this->anything())
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    public function testCannotRemoveMayorOrLeaderMembershipIfNoPoliticalCommitteeMembership(): void
    {
        $this->expectException(PoliticalCommitteeMembershipException::class);
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('political_committee.membership.adherent_has_no_membership', $this->anything())
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    public function testRemoveMayorMembership(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $pcMembership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::MAYOR));
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));

        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(true)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));
    }

    public function testRemoveLeaderMembership(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $pcMembership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::LEADER));
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));

        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(false)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));
    }

    public function provideOfficioQualities(): iterable
    {
        yield [TerritorialCouncilQualityEnum::REFERENT];
        yield [TerritorialCouncilQualityEnum::GOVERNMENT_MEMBER];
        yield [TerritorialCouncilQualityEnum::REFERENT_JAM];
        yield [TerritorialCouncilQualityEnum::LRE_MANAGER];
        yield [TerritorialCouncilQualityEnum::SENATOR];
        yield [TerritorialCouncilQualityEnum::DEPUTY];
        yield [TerritorialCouncilQualityEnum::EUROPEAN_DEPUTY];
        yield [TerritorialCouncilQualityEnum::REGIONAL_COUNCIL_PRESIDENT];
        yield [TerritorialCouncilQualityEnum::DEPARTMENTAL_COUNCIL_PRESIDENT];
    }

    public function provideNotPassingQualities(): iterable
    {
        yield [TerritorialCouncilQualityEnum::MAYOR];
        yield [TerritorialCouncilQualityEnum::LEADER];
    }

    public function provideElectedMemberQualities(): iterable
    {
        yield [TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::CORSICA_ASSEMBLY_MEMBER];
        yield [TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::CITY_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::BOROUGH_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR];
        yield [TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT];
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'd.dupont@test.com',
            'password',
            Genders::MALE,
            'Damien',
            'DUPONT',
            new \DateTime('1979-03-25'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );
    }
}
