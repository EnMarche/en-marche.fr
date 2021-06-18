<?php

namespace Tests\App\Controller\EnMarche;

use App\Adherent\Certification\CertificationRequestProcessCommand;
use App\Entity\CertificationRequest;
use App\Mailer\Message\CertificationRequestPendingMessage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group certification
 */
class CertificationRequestControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    private $certificationRequestRepository;

    public function testApprovedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Votre profil est certifié', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsDisabled();
    }

    public function testPendingCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Demande de certification en attente', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsDisabled();
    }

    public function testRefusedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, $email = 'luciole1989@spambox.fr');
        $this->assertEquals(2, $this->countCertificationRequests($email));

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Demande de certification refusée', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsSuccessful($email);
        $this->assertEquals(3, $this->countCertificationRequests($email));
    }

    public function testBlockedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Demande de certification bloquée', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsDisabled();
    }

    public function testNoCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, $email = 'kiroule.p@blabla.tld');
        $this->assertEquals(0, $this->countCertificationRequests($email));

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Votre profil n\'est pas encore certifié.', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsSuccessful($email);
        $this->assertEquals(1, $this->countCertificationRequests($email));
    }

    private function assertCertificationRequestIsDisabled(): void
    {
        $this->client->request('GET', '/espace-adherent/mon-compte/certification/demande');
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/certification', $this->client);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(200, $this->client->getResponse());
    }

    private function assertCertificationRequestIsSuccessful(string $email): void
    {
        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification/demande');
        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $form = $crawler->filter('form[name="certification_request"]')->form();
        $this->client->request('POST', '/espace-adherent/mon-compte/certification/demande', [
            'certification_request' => [
                'cgu' => true,
                '_token' => $form['certification_request[_token]']->getValue(),
            ],
        ], [
            'certification_request' => [
                'document' => new UploadedFile(
                    __DIR__.'/../../../app/data/static/application-request-sharer.jpg',
                    'cv.pdf',
                    'application/pdf'
                ),
            ],
        ]);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/certification', $this->client);

        $this->assertCountMails(1, CertificationRequestPendingMessage::class, $email);
        $this->assertMessageIsDispatched(CertificationRequestProcessCommand::class);

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Votre demande de certification a bien été enregistrée.', $crawler->filter('.flash')->text());
        $this->assertStringContainsString('Demande de certification en attente', $crawler->filter('.certification-status')->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->certificationRequestRepository = $this->getRepository(CertificationRequest::class);
    }

    protected function tearDown(): void
    {
        $this->certificationRequestRepository = null;

        parent::tearDown();
    }

    private function countCertificationRequests(string $email): int
    {
        return $this
            ->certificationRequestRepository
            ->createQueryBuilder('cr')
            ->select('COUNT(cr)')
            ->innerJoin('cr.adherent', 'adherent')
            ->where('adherent.emailAddress = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
