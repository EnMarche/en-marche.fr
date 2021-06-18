<?php

namespace Tests\App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group time-sensitive
 * @group functional
 */
class InactiveAdminDisconnectionTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testLogoutInactiveAdmin()
    {
        $this->authenticateAsAdmin($this->client);

        $this->client->request(Request::METHOD_GET, '/admin/app/media/list');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // wait
        sleep(1900);

        // go to another page
        $this->client->request(Request::METHOD_GET, '/admin/dashboard');

        // should be redirected to logout
        $this->assertClientIsRedirectedTo('/deconnexion', $this->client);
    }

    public function testNoLogoutInactiveAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        // wait
        sleep(1900);

        // go to another page
        $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        // status code should be 200 OK, because there is no redirection to disconnect
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }
}
