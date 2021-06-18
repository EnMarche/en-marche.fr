<?php

namespace Tests\App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Helper\PHPUnitHelper;

/**
 * @group functional
 * @group api
 */
class StatsControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/api/stats');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        $data = \GuzzleHttp\json_decode($content, true);

        PHPUnitHelper::assertArraySubset([
            'userCount' => 63,
            'eventCount' => 20,
            'committeeCount' => 13,
        ], $data);
    }
}
