<?php

namespace Tests\App\Controller\OAuth;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Device;
use App\Entity\OAuth\AuthorizationCode;
use Defuse\Crypto\Crypto;
use League\OAuth2\Server\CryptKey;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class OAuthServerControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const AUTH_TOKEN_URI_REGEX = '#^http://client-oauth\.docker:8000/client/receive_authcode\?code=(?P<code>[a-f0-9]+)\&state=bds1775p6f3ks29h2vla20ng5n$#';
    private const ACCESS_TOKEN_RESPONSE_PAYLOAD_REGEX = '#\{"token_type":"Bearer","expires_in":(\d{4}),"access_token":"(?P<access_token>[A-Za-z0-9-_=]+\.[A-Za-z0-9-_=]+\.?[A-Za-z0-9-_.+/=]*)","refresh_token":"(?P<refresh_token>[a-f0-9]+)"\}#';
    private $encryptionKey;

    /** @var CryptKey */
    private $privateCryptKey;

    private $deviceRepository;

    public function testTryRequestSecuredResourceWithExpiredAccessToken(): void
    {
        $jwtAccessToken = $this->getExpiredJwtAccessToken();

        $this->client->request(Request::METHOD_GET, '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwtAccessToken),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();

        static::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-type'));
        static::assertSame('{"message":"The resource owner or authorization server denied the request."}', $response->getContent());
    }

    public function testTryRequestSecuredResourceWithRevokedAccessToken(): void
    {
        $jwtAccessToken = $this->getRevokedJwtAccessToken();

        $this->client->request(Request::METHOD_GET, '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwtAccessToken),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();

        static::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-type'));
        static::assertSame('{"message":"The resource owner or authorization server denied the request."}', $response->getContent());
    }

    public function testRequestAccessTokenWithDeviceId(): void
    {
        self::assertCount(2, $this->deviceRepository->findAll());

        // 1st request with refresh token must be successful and create a device in database
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => '1931b955-560b-41b2-9eb9-c232157f1471',
            'client_secret' => 'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            'grant_type' => 'client_credentials',
            'scope' => 'jemarche_app',
            'device_id' => 'dd4SOCS-4UlCtO-gZiQGDA',
        ]);

        $this->isSuccessful($this->client->getResponse());
        self::assertCount(3, $devices = $this->deviceRepository->findAll());

        $newDevice = $this->deviceRepository->findOneBy(['deviceUuid' => 'dd4SOCS-4UlCtO-gZiQGDA']);
        self::assertSame('dd4SOCS-4UlCtO-gZiQGDA', $newDevice->getIdentifier());

        // 2nd request must be successful and not create a second device in database
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => '1931b955-560b-41b2-9eb9-c232157f1471',
            'client_secret' => 'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            'grant_type' => 'client_credentials',
            'scope' => 'jemarche_app',
            'device_id' => 'dd4SOCS-4UlCtO-gZiQGDA',
        ]);

        $this->isSuccessful($this->client->getResponse());
        self::assertCount(3, $this->deviceRepository->findAll());
    }

    public function testRequestAccessTokenWithValidAndInvalidRefreshToken(): void
    {
        // Get one valid refresh_token
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => '661cc3b7-322d-4441-a510-ab04eda71737',
            'code' => $this->getEncryptedCode($this->findAuthorizationCode('0c33b1711015b5e3d930f65b5dc87c398bfb3b29401028ee119c882bdf87cf9dcbf9a562629535e5')),
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://client-oauth.docker:8000/client/receive_authcode',
        ]);

        // 1st request with refresh token must be successful
        $this->isSuccessful($response = $this->client->getResponse());
        $encryptedRefreshToken = json_decode($response->getContent(), true)['refresh_token'];
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => '661cc3b7-322d-4441-a510-ab04eda71737',
            'grant_type' => 'refresh_token',
            'refresh_token' => $encryptedRefreshToken,
        ]);

        $response = $this->client->getResponse();
        $this->isSuccessful($response);
        static::assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));
        static::assertMatchesRegularExpression(self::ACCESS_TOKEN_RESPONSE_PAYLOAD_REGEX, $response->getContent());
        $encryptedRefreshToken2 = json_decode($this->client->getResponse()->getContent(), true)['refresh_token'];

        // 2nd request with the same refresh token must fail because refresh token are valid one time only
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => '661cc3b7-322d-4441-a510-ab04eda71737',
            'grant_type' => 'refresh_token',
            'refresh_token' => $encryptedRefreshToken,
        ]);
        $response = $this->client->getResponse();

        static::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-type'));
        static::assertSame('{"error":"invalid_request","error_description":"The refresh token is invalid.","hint":"Token has been revoked","message":"The refresh token is invalid."}', $response->getContent());

        // Test with an expired refresh token
        $encryptedRefreshToken2 = $this->expireRefreshToken($encryptedRefreshToken2);
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => '661cc3b7-322d-4441-a510-ab04eda71737',
            'grant_type' => 'refresh_token',
            'refresh_token' => $encryptedRefreshToken2,
        ]);
        $response = $this->client->getResponse();

        static::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-type'));
        static::assertSame('{"error":"invalid_request","error_description":"The refresh token is invalid.","hint":"Token has expired","message":"The refresh token is invalid."}', $response->getContent());
    }

    public function testRequestAccessTokenWithRevokedAuthorizationCode(): void
    {
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19',
            'code' => $this->getEncryptedCode($this->findAuthorizationCode('aa56a0ab28aade7ef4a554adc02b297ebd4d5bfe587c6b847512b5f46c59cad26ce53766f8766248')),
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://client-oauth.docker:8000/client/receive_authcode',
        ]);

        $response = $this->client->getResponse();

        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-type'));
        static::assertSame('{"error":"invalid_request","error_description":"The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.","hint":"Authorization code has been revoked","message":"The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed."}', $response->getContent());
    }

    public function testRequestAccessTokenWithExpiredAuthorizationCode(): void
    {
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19',
            'code' => $this->getEncryptedCode($this->findAuthorizationCode('673b3b128a9b5237b25a47e319e27d8c7d40255520269b3c382ea96012606f00d743927cf3af49f7')),
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://client-oauth.docker:8000/client/receive_authcode',
        ]);

        $response = $this->client->getResponse();

        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-type'));
        static::assertSame('{"error":"invalid_request","error_description":"The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.","hint":"Authorization code has expired","message":"The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed."}', $response->getContent());
    }

    public function testSecondClientTriesToStealFirstClientAuthorizationCode(): void
    {
        $this->client->request('POST', '/oauth/v2/token', [
            // Client ID & Secret belong to the first client
            'client_id' => 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19',
            // Authorization code was issued for another client
            'code' => $this->getEncryptedCode($this->findAuthorizationCode('0c33b1711015b5e3d930f65b5dc87c398bfb3b29401028ee119c882bdf87cf9dcbf9a562629535e5')),
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://client-oauth.docker:8000/client/receive_authcode',
        ]);

        $response = $this->client->getResponse();

        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-type'));
        static::assertSame('{"error":"invalid_request","error_description":"The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.","hint":"Authorization code was not issued to this client","message":"The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed."}', $response->getContent());
    }

    public function testRequestAccessTokenWithUngrantedScope(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, $this->createAuthorizeUrl(['read:users']));
        static::assertSame(
            'Je me connecte à En-Marche ! avec mon compte En Marche.',
            trim($crawler->filter('#auth-client-notice')->text())
        );

        $this->client->submit($crawler->selectButton('Accepter')->form());
        $response = $this->client->getResponse();
        static::assertTrue($response->isRedirect());
        static::assertMatchesRegularExpression(self::AUTH_TOKEN_URI_REGEX, $location = $response->headers->get('Location'));
        if (!preg_match(self::AUTH_TOKEN_URI_REGEX, $location, $matches)) {
            throw new \RuntimeException('Unable to fetch the OAuth authorization token from the URI.');
        }

        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19',
            'code' => $matches['code'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://client-oauth.docker:8000/client/receive_authcode',
        ]);
        $response = $this->client->getResponse();
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertJsonStringEqualsJsonString('{"error":"invalid_scope","error_description":"The requested scope is invalid, unknown, or malformed","hint":"Check the `read:users` scope","message":"The requested scope is invalid, unknown, or malformed"}', $response->getContent());
    }

    public function testOAuthAuthenticationIsSuccessful(): void
    {
        // 1. Try to connect User with OAuth
        $this->client->request(Request::METHOD_GET, $this->createAuthorizeUrl());
        $response = $this->client->getResponse();

        // 2. But the user must authenticate 1st
        static::assertTrue($response->isRedirect('/connexion'));
        $crawler = $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'carl999@example.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));
        static::assertTrue($this->client->getResponse()->isRedirect($this->createAuthorizeUrl()));

        // 3. Ask the user if he grants access to this OAuth client
        $crawler = $this->client->followRedirect();
        static::assertSame(
            'Je me connecte à En-Marche ! avec mon compte En Marche.',
            trim($crawler->filter('#auth-client-notice')->text())
        );

        // 4. User accepts
        $this->client->submit($crawler->selectButton('Accepter')->form());
        $response = $this->client->getResponse();
        static::assertTrue($response->isRedirect());
        static::assertMatchesRegularExpression(self::AUTH_TOKEN_URI_REGEX, $location = $response->headers->get('Location'));
        if (!preg_match(self::AUTH_TOKEN_URI_REGEX, $location, $matches)) {
            throw new \RuntimeException('Unable to fetch the OAuth authorization token from the URI.');
        }

        // 5. Now the client is able to ask for an Access Token
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19',
            'code' => $matches['code'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://client-oauth.docker:8000/client/receive_authcode',
        ]);
        $response = $this->client->getResponse();
        $this->isSuccessful($response);
        static::assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));
        static::assertMatchesRegularExpression(self::ACCESS_TOKEN_RESPONSE_PAYLOAD_REGEX, $json = $response->getContent());

        // 6. /api/me is not accessible without the access token
        $this->client->request(Request::METHOD_GET, '/api/me');
        static::assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);
        $data = json_decode($json, true);

        // 7. /api/me access is granted with access token
        $this->client->request(Request::METHOD_GET, '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $data['access_token']),
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->isSuccessful($response = $this->client->getResponse());
        static::assertSame('application/json', $response->headers->get('Content-Type'));

        $data = \GuzzleHttp\json_decode($response->getContent(), true);

        $expected = [
            'uuid' => 'e6977a4d-2646-5f6c-9c82-88e58dca8458',
            'email_address' => 'carl999@example.fr',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'country' => 'FR',
            'postal_code' => '73100',
            'nickname' => 'pont',
            'use_nickname' => false,
            'comments_cgu_accepted' => false,
            'elected' => false,
            'larem' => false,
            'emailAddress' => 'carl999@example.fr',
            'firstName' => 'Carl',
            'lastName' => 'Mirabeau',
            'zipCode' => '73100',
        ];
        foreach ($data as $expectedKey => $expectedValue) {
            static::assertArrayHasKey($expectedKey, $data);
            static::assertSame($expectedValue, $data[$expectedKey]);
        }

        // 8. The OAuth server remembers OAuth client authorizations so that the user can reconnect without providing authorization again
        $this->client->request(Request::METHOD_GET, $this->createAuthorizeUrl());
        $response = $this->client->getResponse();
        static::assertTrue($response->isRedirect());
        static::assertMatchesRegularExpression(self::AUTH_TOKEN_URI_REGEX, $response->headers->get('Location'));
    }

    public function testOAuthAuthenticationFailedWithoutRedirectUriIfClientHasMoreThan1RedirectUri(): void
    {
        $this->client->request(Request::METHOD_GET, '/connexion');
        $this->client->submit($this->client->getCrawler()->selectButton('Connexion')->form([
            '_login_email' => 'carl999@example.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));
        $this->client->followRedirect();

        $urlWithoutRedirectUri = $this->createAuthorizeUrl([], 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19', null);

        $this->client->request(Request::METHOD_GET, $urlWithoutRedirectUri);
        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('{"error":"invalid_client","error_description":"Client authentication failed","message":"Client authentication failed"}', $response->getContent());
    }

    public function testOAuthAuthenticationIsSuccessfulWithoutAskingUserAuthorization(): void
    {
        // No redirect_uri is specified so it's gonna use the only one the client registered
        $authorizeUrl = $this->createAuthorizeUrl([], '661cc3b7-322d-4441-a510-ab04eda71737', null);
        $this->client->request(Request::METHOD_GET, $authorizeUrl);
        $response = $this->client->getResponse();
        static::assertTrue($response->isRedirect('/connexion'));

        $crawler = $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'carl999@example.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));
        static::assertTrue($this->client->getResponse()->isRedirect($authorizeUrl));

        $this->client->followRedirect();
        $response = $this->client->getResponse();
        static::assertTrue($response->isRedirect());
        static::assertMatchesRegularExpression(self::AUTH_TOKEN_URI_REGEX, $response->headers->get('Location'));
    }

    public function testOAuthAuthenticationIsFailedForClientWithRequestedRoles(): void
    {
        $authorizeUrl = $this->createAuthorizeUrl([], LoadClientData::CLIENT_12_UUID, null);
        $this->client->request(Request::METHOD_GET, $authorizeUrl);
        $response = $this->client->getResponse();
        static::assertTrue($response->isRedirect('/connexion'));

        $crawler = $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'carl999@example.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));
        static::assertTrue($this->client->getResponse()->isRedirect($authorizeUrl));

        $this->client->followRedirect();
        static::assertStatusCode(403, $this->client);
    }

    public function testOAuthAuthenticationIsSuccessfulForClientWithRequestedRoles(): void
    {
        $authorizeUrl = $this->createAuthorizeUrl([], LoadClientData::CLIENT_12_UUID, null);
        $this->client->request(Request::METHOD_GET, $authorizeUrl);
        $response = $this->client->getResponse();
        static::assertTrue($response->isRedirect('/connexion'));

        $crawler = $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'jacques.picard@en-marche.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));
        static::assertTrue($this->client->getResponse()->isRedirect($authorizeUrl));

        $this->client->followRedirect();
        static::assertTrue($this->client->getResponse()->isRedirect());
    }

    private function findAuthorizationCode(string $identifier): ?AuthorizationCode
    {
        return $this
            ->getContainer()
            ->get('doctrine')
            ->getRepository(AuthorizationCode::class)
            ->findAuthorizationCodeByIdentifier($identifier)
        ;
    }

    private function createAuthorizeUrl(
        $scopes = [],
        string $clientId = 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19',
        ?string $redirectUri = 'http://client-oauth.docker:8000/client/receive_authcode'
    ): string {
        $params = ['client_id' => $clientId];

        if ($redirectUri) {
            $params['redirect_uri'] = $redirectUri;
        }

        $params['response_type'] = 'code';

        if ($scopes) {
            $params['scope'] = implode(' ', $scopes);
        }

        $params['state'] = 'bds1775p6f3ks29h2vla20ng5n';

        return sprintf('http://'.$this->hosts['app'].'/oauth/v2/auth?%s', http_build_query($params));
    }

    private function getEncryptedCode(AuthorizationCode $authCode): string
    {
        $payload = json_encode([
            'client_id' => $authCode->getClientIdentifier(),
            'redirect_uri' => $authCode->getRedirectUri(),
            'auth_code_id' => $authCode->getIdentifier(),
            'scopes' => $authCode->getScopes(),
            'user_id' => $authCode->getUserIdentifier(),
            'expire_time' => (string) $authCode->getExpiryTimestamp(),
            'code_challenge' => null,
            'code_challenge_method' => null,
        ]);

        return $this->encrypt($payload);
    }

    private function encrypt(string $data): string
    {
        return Crypto::encryptWithPassword($data, $this->encryptionKey);
    }

    private function decrypt(string $data): string
    {
        return Crypto::decryptWithPassword($data, $this->encryptionKey);
    }

    private function expireRefreshToken(string $encryptedToken): string
    {
        // The expiration time of a refresh token is not done against the database so the JWT payload is directly updated
        $tokenArray = $this->decrypt($encryptedToken);
        $tokenArray = json_decode($tokenArray, true);
        $tokenArray['expire_time'] = time() - 1;

        return Crypto::encryptWithPassword(json_encode($tokenArray), $this->encryptionKey);
    }

    private function getExpiredJwtAccessToken(): string
    {
        return $this->getJwtAccessTokenByIdentifier('491f7926e9c092894c9589a6740ceb402bcd4d2f38973623981b43e8fdacfd6f27bfbe6026e5d853', $this->privateCryptKey);
    }

    private function getRevokedJwtAccessToken(): string
    {
        return $this->getJwtAccessTokenByIdentifier('4c843038f3d1ba017e6c835420efeefd03c024d9f413ecf96bc70acbdcb79e8ae0598a1579364190', $this->privateCryptKey);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->encryptionKey = $this->getContainer()->getParameter('ssl_encryption_key');
        $this->privateCryptKey = new CryptKey($this->getContainer()->getParameter('ssl_private_key'));
        $this->deviceRepository = $this->getRepository(Device::class);
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->privateCryptKey = null;
        $this->encryptionKey = null;
        $this->deviceRepository = null;

        parent::tearDown();
    }
}
