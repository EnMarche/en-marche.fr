<?php

namespace Tests\App\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class FormationAdminTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider uriProvider
     */
    public function testSuperAdminCanAccessFormationAdmin(string $uri): void
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');
        $this->client->request(Request::METHOD_GET, $uri);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function uriProvider(): iterable
    {
        yield ['/admin/app/formation-path/list'];
        yield ['/admin/app/formation-path/create'];
        yield ['/admin/app/formation-axe/list'];
        yield ['/admin/app/formation-axe/create'];
        yield ['/admin/app/formation-module/list'];
        yield ['/admin/app/formation-module/create'];
    }
}
