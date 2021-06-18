<?php

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadInternalApiApplicationData;
use App\Event\EventTypeEnum;
use League\OAuth2\Server\CryptKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class InternalApiProxyControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    /** @var CryptKey */
    private $privateCryptKey;

    public function testGetCausesWithApiProxyReturnDeniedAccessIfNoToken(): void
    {
        $this->client->request(Request::METHOD_GET, sprintf('/api/v3/internal/%s/foo/bar', LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_03_UUID));

        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());
    }

    public function testGetEventsReturnValidJsonResponse(): void
    {
        $accessToken = $this->getJwtAccessTokenByIdentifier('l9efhked975s1z1og3z10anp8ydi6tkmha468906g1tu0hb5hltni7xvsuipg5n7tkslzqjttyfn69cd', $this->privateCryptKey);

        $url = sprintf('/api/v3/internal/%s/api/v3/events', LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_03_UUID);

        $this->client->request(Request::METHOD_GET, $url, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('items', $data);
        self::assertCount(2, $data['items']);
    }

    public function testAnAdherentCanCreateCoalitionEventWithProxy()
    {
        $accessToken = $this->getJwtAccessTokenByIdentifier('l9efhked975s1z1og3z10anp8ydi6tkmha468906g1tu0hb5hltni7xvsuipg5n7tkslzqjttyfn69cd', $this->privateCryptKey);

        $url = sprintf('/api/v3/internal/%s/api/v3/events', LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_03_UUID);

        $this->client->request(Request::METHOD_POST, $url, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'type' => EventTypeEnum::TYPE_COALITION,
            'name' => 'My event',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            'time_zone' => 'Europe/Paris',
            'begin_at' => '2021-01-29 16:30:30',
            'finish_at' => '2021-01-29 16:30:30',
            'visio_url' => 'https://en-marche.fr/reunions/123',
            'coalition' => 'fc7fd104-71e5-4399-a874-f8fe752f846b',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->privateCryptKey = new CryptKey($this->getParameter('ssl_private_key'));
    }

    protected function tearDown(): void
    {
        $this->privateCryptKey = null;

        parent::tearDown();
    }
}
