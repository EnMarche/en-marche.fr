<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class SocialShareControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testList()
    {
        $this->client->request(Request::METHOD_GET, '/jepartage');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testShow()
    {
        $this->client->request(Request::METHOD_GET, '/jepartage/culture');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }
}
