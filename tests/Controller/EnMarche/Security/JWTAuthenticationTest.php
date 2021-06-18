<?php

namespace Tests\App\Controller\EnMarche\Security;

use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group security
 */
class JWTAuthenticationTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testJWTAuthenticationSuccess(): void
    {
        $this->client->request('GET', '/api/users/me');
        $this->assertStatusCode('401', $this->client);

        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], \GuzzleHttp\json_encode([
            'username' => 'carl999@example.fr',
            'password' => 'secret!12345',
        ]));
        $response = $this->client->getResponse();
        $this->isSuccessful($response);
        $this->assertJson($content = $response->getContent());

        $data = \GuzzleHttp\json_decode($content, true);
        $this->assertArrayHasKey('token', $data);

        $this->client->request('GET', '/api/users/me', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $data['token']),
        ]);
        $response = $this->client->getResponse();
        $this->isSuccessful($response);
        $this->assertJson($response->getContent());
    }

    public function testJWTTokenIsExpired(): void
    {
        $expiredJWTToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1NDg3NjIyNDAsImV4cCI6MTU0ODc2NTg0MCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9BREhFUkVOVCIsIlJPTEVfQk9BUkRfTUVNQkVSIl0sInVzZXJuYW1lIjoiY2FybDk5OUBleGFtcGxlLmZyIn0.iAe-UASkeBI1czzq8oEmdRbb5Su8QhXtKWUEFM5lvpZdOKFcxVf0S1y4Ly5U6TFlE1g5vr9m_aRdYMjCHGAd6g76CNzKYFpSLJN4uenhFMXmh2ukIktJ1UxYy6eapwdkpyiIePx2r0um6Wm7SxgOhZ4hgOPVIo42psVI9H2UGTw';

        $this->client->request('GET', '/api/users/me', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $expiredJWTToken),
        ]);
        $this->assertStatusCode(401, $this->client);
        $this->assertSame('{"code":401,"message":"Expired JWT Token"}', $this->client->getResponse()->getContent());
    }
}
