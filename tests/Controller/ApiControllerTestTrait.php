<?php

namespace Tests\App\Controller;

use App\Entity\OAuth\AccessToken;
use App\OAuth\Model\Client;
use App\OAuth\Model\Scope;
use League\OAuth2\Server\CryptKey;

trait ApiControllerTestTrait
{
    protected function getAccessToken(
        string $clientUuid,
        string $clientSecret,
        string $grantType,
        ?string $scope,
        string $username = null,
        string $userPassword = null
    ): ?string {
        $params = [
            'client_id' => $clientUuid,
            'client_secret' => $clientSecret,
            'grant_type' => $grantType,
            'scope' => $scope,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
            $params['password'] = $userPassword;
        }

        $this->client->request('POST', '/oauth/v2/token', $params);

        return json_decode($this->client->getResponse()->getContent(), true)['access_token'] ?? null;
    }

    protected function assertEachJsonItemContainsKey($key, $json, array $excluding = [])
    {
        $data = \GuzzleHttp\json_decode($json, true);

        foreach ($data as $k => $item) {
            if (\in_array($k, $excluding, true)) {
                continue;
            }
            $this->assertArrayHasKey($key, $item, 'Item '.$k.' of JSON payload does not have '.$key.' key');
        }
    }

    protected function getJwtAccessTokenByIdentifier(string $identifier, CryptKey $privateKey): string
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository(AccessToken::class)
            ->findAccessTokenByIdentifier($identifier)
        ;

        $client = new Client($accessToken->getClientIdentifier(), []);

        $token = new \App\OAuth\Model\AccessToken();
        $token->setClient($client);
        $token->setIdentifier($identifier);
        $token->setExpiryDateTime($accessToken->getExpiryDateTime());
        $token->setUserIdentifier($accessToken->getUserIdentifier());
        $token->setPrivateKey($privateKey);

        foreach ($accessToken->getScopes() as $scope) {
            $token->addScope(new Scope($scope));
        }

        return (string) $token;
    }
}
