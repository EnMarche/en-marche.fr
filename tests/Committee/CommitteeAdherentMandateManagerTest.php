<?php

namespace Tests\App\Committee;

use App\Committee\CommitteeAdherentMandateCommand;
use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\CommitteeManager;
use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\BaseGroup;
use App\Entity\Committee;
use App\Entity\PostAddress;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group committee
 */
class CommitteeAdherentMandateManagerTest extends TestCase
{
    /** @var MockObject|EntityManagerInterface */
    private $entityManager;
    /** @var MockObject|CommitteeAdherentMandateRepository */
    private $mandateRepository;
    /** @var MockObject|ElectedRepresentativeRepository */
    private $electedRepresentativeRepository;
    /** @var MockObject|TranslatorInterface */
    private $translator;
    /** @var MockObject|CommitteeAdherentMandateManager */
    private $mandateManager;
    /** @var MockObject|CommitteeManager */
    private $committeeManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mandateRepository = $this->createMock(CommitteeAdherentMandateRepository::class);
        $this->electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $this->committeeManager = $this->createMock(CommitteeManager::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->mandateManager = new CommitteeAdherentMandateManager(
            $this->entityManager,
            $this->mandateRepository,
            $this->electedRepresentativeRepository,
            $this->committeeManager,
            $this->translator
        );
    }

    protected function tearDown(): void
    {
        $this->entityManager = null;
        $this->mandateRepository = null;
        $this->electedRepresentativeRepository = null;
        $this->translator = null;
        $this->mandateManager = null;
        $this->committeeManager = null;

        parent::tearDown();
    }

    public function testCannotCreateMandateIfIncorrectGender()
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::OTHER);
        $committee = $this->createCommittee();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.not_valid_gender', $this->anything())
        ;

        $this->mandateManager->createMandate($adherent, $committee);
    }

    public function testCannotCreateMandateIfAdherentHasActiveMandate()
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $activeMandate = new CommitteeAdherentMandate(
            $this->createAdherent(),
            Genders::FEMALE,
            new \DateTime()
        );
        $activeMandate->setCommittee($this->createCommittee());

        $adherent = $this->createAdherent(Genders::FEMALE);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate(
            new Adherent(),
            Genders::FEMALE,
            new \DateTime('2020-07-07')
        );
        $mandate->setCommittee($this->createCommittee());

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.adherent_with_active_mandate', $this->anything())
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandate')
            ->with($adherent, $committee)
            ->willReturn($activeMandate)
        ;

        $this->mandateManager->createMandate($adherent, $committee);
    }

    public function testCannotCreateMandateIfAdherentIsMemberOfTerritorialCouncil()
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::FEMALE);
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership());
        $committee = $this->createCommittee();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.adherent_has_territorial_council_membership', $this->anything())
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandate')
            ->with($adherent, $committee)
            ->willReturn(null)
        ;

        $this->mandateManager->createMandate($adherent, $committee);
    }

    /**
     * @dataProvider provideGenders
     */
    public function testCannotCreateMandateIfCommitteeHasActiveMandate(string $gender)
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate(new Adherent(), $gender, new \DateTime('2020-07-07'));
        $mandate->setCommittee($committee);
        $committee->addAdherentMandate($mandate);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.committee_has_already_active_mandate', $this->anything())
        ;

        $this->mandateManager->createMandate($adherent, $committee);
    }

    /**
     * @dataProvider provideGenders
     */
    public function testCreateMandate(string $gender)
    {
        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommitteeAdherentMandate::class))
        ;
        $this->entityManager->expects($this->once())->method('flush');

        $this->mandateManager->createMandate($adherent, $committee);

        $this->assertCount(1, $committee->getAdherentMandates());
        $this->assertInstanceOf(CommitteeAdherentMandate::class, $committee->getAdherentMandates()->first());
    }

    /**
     * @dataProvider provideGenders
     */
    public function testCannotEndMandateBecauseMandateNotFound(string $gender)
    {
        $this->expectException(CommitteeAdherentMandateException::class);
        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();

        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandateFor')
            ->with($adherent, $committee)
            ->willReturn(null)
        ;

        $this->mandateManager->endMandate($adherent, $committee);
    }

    /**
     * @dataProvider provideGenders
     */
    public function testEndMandate(string $gender)
    {
        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate($adherent, $gender, new \DateTime('2020-08-26 10:10:10'));
        $mandate->setCommittee($committee);

        $this->assertNull($mandate->getFinishAt());

        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandateFor')
            ->with($adherent, $committee)
            ->willReturn($mandate)
        ;

        $this->mandateManager->endMandate($adherent, $committee);

        $this->assertNotNull($mandate->getFinishAt());
    }

    public function testCannotUpdateSupervisorProvisionalMandateIfIncorrectGender()
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::OTHER);
        $committee = $this->createCommittee();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.not_valid_gender', $this->anything())
        ;

        $this->mandateManager->updateSupervisorMandate($adherent, $committee);
    }

    public function testCannotUpdateSupervisorProvisionalMandateIfMinor()
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::MALE, '2005-04-04');
        $committee = $this->createCommittee();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.provisional_supervisor.not_valid', $this->anything())
        ;

        $this->mandateManager->updateSupervisorMandate($adherent, $committee);
    }

    public function testCannotUpdateSupervisorProvisionalMandateIfHasActiveParliamentaryMandate()
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::MALE);
        $committee = $this->createCommittee();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.provisional_supervisor.not_valid', $this->anything())
        ;
        $this->electedRepresentativeRepository
            ->expects($this->once())
            ->method('hasActiveParliamentaryMandate')
            ->with($adherent)
            ->willReturn(true)
        ;

        $this->mandateManager->updateSupervisorMandate($adherent, $committee);
    }

    public function testCheckAdherentForMandateReplacementFailsIfAdherentHasInappropriateGender(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::MALE);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.inappropriate_gender')
        ;

        $this->mandateManager->checkAdherentForMandateReplacement($adherent, Genders::FEMALE);
    }

    public function testCheckAdherentForMandateReplacementFailsIfAdherentMinor(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::MALE, '2005-04-04');

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.adherent.not_valid')
        ;

        $this->mandateManager->checkAdherentForMandateReplacement($adherent, Genders::MALE);
    }

    public function testCheckAdherentForMandateReplacementFailsIfAdherentHasActiveParliamentaryMandate(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createAdherent(Genders::MALE);

        $this->electedRepresentativeRepository
            ->expects($this->once())
            ->method('hasActiveParliamentaryMandate')
            ->with($adherent)
            ->willReturn(true)
        ;
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.adherent.not_valid')
        ;

        $this->mandateManager->checkAdherentForMandateReplacement($adherent, Genders::MALE);
    }

    public function testCanReplaceMandate(): void
    {
        $committee = $this->createCommittee();
        $adherent = $this->createAdherent(Genders::MALE);
        $mandate = $this->createMandate(Genders::MALE, $committee);
        $command = new CommitteeAdherentMandateCommand($committee);
        $command->setGender($mandate->getGender());
        $command->setAdherent($adherent);
        $command->setProvisional(false);

        $this->translator
            ->expects($this->never())
            ->method('trans')
        ;
        $this->committeeManager
            ->expects($this->once())
            ->method('followCommittee')
            ->with($adherent, $committee)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommitteeAdherentMandate::class))
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $this->mandateManager->replaceMandate($mandate, $command);

        $this->assertNotNull($mandate->getFinishAt());
        $this->assertSame(AdherentMandateInterface::REASON_REPLACED, $mandate->getReason());
    }

    public function testCanCreateMandateFromCommand(): void
    {
        $committee = $this->createCommittee();
        $adherent = $this->createAdherent(Genders::MALE);
        $mandateCommand = new CommitteeAdherentMandateCommand($committee);
        $mandateCommand->setAdherent($adherent);
        $mandateCommand->setGender($gender = Genders::MALE);
        $mandateCommand->setQuality($quality = CommitteeMandateQualityEnum::SUPERVISOR);
        $mandateCommand->setProvisional($isProvisional = true);

        $this->committeeManager
            ->expects($this->once())
            ->method('followCommittee')
            ->with($adherent, $committee)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommitteeAdherentMandate::class))
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $mandate = $this->mandateManager->createMandateFromCommand($mandateCommand);

        $this->assertSame($committee, $mandate->getCommittee());
        $this->assertSame($adherent, $mandate->getAdherent());
        $this->assertSame($gender, $mandate->getGender());
        $this->assertSame($quality, $mandate->getQuality());
        $this->assertSame($isProvisional, $mandate->isProvisional());
        $this->assertSame((new \DateTime())->format('Y/m/d'), $mandate->getBeginAt()->format('Y/m/d'));
        $this->assertNull($mandate->getFinishAt());
        $this->assertNull($mandate->getReason());
    }

    private function createAdherent(string $gender = Genders::MALE, string $birthday = null): Adherent
    {
        return Adherent::create(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'd.dupont@test.com',
            'password',
            $gender,
            'Damien',
            'DUPONT',
            new \DateTime($birthday ?: '1979-03-25'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );
    }

    private function createCommittee(): Committee
    {
        return new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Lyon 1',
            'Le comité En Marche ! de Lyon village',
            PostAddress::createFrenchAddress('50 Rue de la Villette', '69003-69383'),
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69003-en-marche-lyon',
            BaseGroup::APPROVED
        );
    }

    private function createMandate(string $gender, Committee $committee = null): CommitteeAdherentMandate
    {
        return CommitteeAdherentMandate::createForCommittee(
            $committee ?? $this->createCommittee(),
            $this->createAdherent($gender)
        );
    }

    public function provideGenders(): iterable
    {
        yield [Genders::MALE];
        yield [Genders::FEMALE];
    }
}
