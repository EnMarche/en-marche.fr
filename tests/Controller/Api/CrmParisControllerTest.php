<?php

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadClientData;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
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
        self::assertMatchesRegularExpression(
            '/^attachment; filename=[\d]{14}-adherents.csv$/',
            $response->headers->get('Content-Disposition')
        );

        $regex = <<<CONTENT
uuid;first_name;last_name;email_address;phone;address;postal_code;city_name;district;gender;birthdate;latitude;longitude;interests;sms_mms
a046adbe-9c7b-56a9-a676-6151a6785dda;Jacques;Picard;jacques.picard@en-marche.fr;+33187264236;"36 rue de la Paix";75008;"Paris 8e";8;male;03/04/1953;48.869946;2.329719;;0
29461c49-6316-5be1-9ac3-17816bf2d819;Lucie;Olivera;luciole1989@spambox.fr;+33727363643;"13 boulevard des Italiens";75009;"Paris 9e";9;female;17/09/1989;48.871323;2.335376;jeunesse;0
CONTENT;

        $this->assertMatchesRegularExpression(sprintf('#%s#', preg_quote($regex)), $responseContent);

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
}
