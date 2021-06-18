<?php

namespace Tests\App\Adherent\Certification;

use App\Adherent\Certification\CertificationAuthorityManager;
use App\Adherent\Certification\CertificationRequestBlockCommand;
use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Mailer\Message\CertificationRequestApprovedMessage;
use App\Mailer\Message\CertificationRequestBlockedMessage;
use App\Mailer\Message\CertificationRequestRefusedMessage;
use App\Repository\AdherentRepository;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group certification
 */
class CertificationAuthorityManagerTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var CertificationAuthorityManager
     */
    private $certificationAuthorityManager;

    public function testCertify(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('lolodie.dutemps@hotnix.tld');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $this->certificationAuthorityManager->certify($adherent, $administrator);

        $this->manager->refresh($adherent);

        self::assertTrue($adherent->isCertified());
    }

    public function testUncertify(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('michelle.dufour@example.ch');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertTrue($adherent->isCertified());

        $this->certificationAuthorityManager->uncertify($adherent, $administrator);

        $this->manager->refresh($adherent);

        self::assertFalse($adherent->isCertified());
    }

    public function testApprove(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email = 'carl999@example.fr');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $certificationRequest = $adherent->getCertificationRequests()->getPendingCertificationRequest();
        self::assertTrue($certificationRequest->isPending());
        self::assertNull($certificationRequest->getProcessedBy());

        $this->certificationAuthorityManager->approve($certificationRequest, $administrator);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertTrue($adherent->isCertified());
        self::assertTrue($certificationRequest->isApproved());
        self::assertSame($administrator, $certificationRequest->getProcessedBy());
        $this->assertCountMails(1, CertificationRequestApprovedMessage::class, $email);
    }

    public function testRefuse(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email = 'carl999@example.fr');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $certificationRequest = $adherent->getCertificationRequests()->getPendingCertificationRequest();
        self::assertTrue($certificationRequest->isPending());
        self::assertNull($certificationRequest->getProcessedBy());

        $refuseCommand = new CertificationRequestRefuseCommand($certificationRequest, $administrator);
        $this->certificationAuthorityManager->refuse($refuseCommand);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertFalse($adherent->isCertified());
        self::assertTrue($certificationRequest->isRefused());
        self::assertSame($administrator, $certificationRequest->getProcessedBy());
        $this->assertCountMails(1, CertificationRequestRefusedMessage::class, $email);
    }

    public function testBlock(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email = 'carl999@example.fr');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $certificationRequest = $adherent->getCertificationRequests()->getPendingCertificationRequest();
        self::assertTrue($certificationRequest->isPending());
        self::assertNull($certificationRequest->getProcessedBy());

        $blockCommand = new CertificationRequestBlockCommand($certificationRequest, $administrator);
        $this->certificationAuthorityManager->block($blockCommand);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertFalse($adherent->isCertified());
        self::assertTrue($certificationRequest->isBlocked());
        self::assertSame($administrator, $certificationRequest->getProcessedBy());
        $this->assertCountMails(1, CertificationRequestBlockedMessage::class, $email);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->certificationAuthorityManager = $this->get(CertificationAuthorityManager::class);
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        $this->certificationAuthorityManager = null;

        parent::tearDown();
    }
}
