<?php

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadClientData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

class CrmParisControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testExportAdherentsCsv(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_09_UUID,
            'cChiFrOxtYb4CgnKoYvV9evEcrOsk2hb9wvO73QLYyc=',
            'client_credentials',
            'crm_paris'
        );

        ob_start();
        $this->client->request(Request::METHOD_GET, '/api/crm-paris/adherents', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('none', $response->headers->get('Content-Encoding'));
        self::assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertRegExp(
            '/^attachment; filename=[\d]{14}-adherents.csv$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf;Referent75and77;Referent75and77;referent-75-77@en-marche-dev.fr;+336765204050;', $responseContent);
        $this->assertStringContainsString('29461c49-6316-5be1-9ac3-17816bf2d819;Lucie;Olivera;luciole1989@spambox.fr;+33727363643;"13 boulevard des Italiens";75009;"Paris 9e";9;female;17/09/1989;48.8713224;2.3353755;jeunesse;0', $responseContent);

        // Ensure adherents without subscription type 'candidate_email' isn't exported
        $this->assertStringNotContainsString('gisele-berthoux@caramail.com', $responseContent);
    }

    public function testAnonymousCanNotExportAdherentsCsv(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/crm-paris/adherents');
        $this->assertStatusCode(401, $this->client);

        $this->client->request(Request::METHOD_GET, '/api/crm-paris/adherents', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer 123abc456def',
        ]);
        $this->assertStatusCode(401, $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
