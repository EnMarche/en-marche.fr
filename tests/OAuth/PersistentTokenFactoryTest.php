<?php

namespace Tests\App\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use App\OAuth\Model\AccessToken as InMemoryAccessToken;
use App\OAuth\Model\AuthorizationCode as InMemoryAuthorizationCode;
use App\OAuth\Model\Client as InMemoryClient;
use App\OAuth\Model\RefreshToken as InMemoryRefreshToken;
use App\OAuth\Model\Scope as InMemoryScope;
use App\OAuth\PersistentTokenFactory;
use App\Repository\AdherentRepository;
use App\Repository\DeviceRepository;
use App\Repository\OAuth\AccessTokenRepository;
use App\Repository\OAuth\ClientRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group time-sensitive
 */
class PersistentTokenFactoryTest extends TestCase
{
    private const ACCESS_TOKEN_IDENTIFIER = 'ccc1a6a661c35cce8a185a413e4b8c3dd7c9b655cf35e3400d4b571484a6c3f1ab0cd92140ea9978';
    private const REFRESH_TOKEN_IDENTIFIER = '7d30d9ff38dd9a164b7756742fedce37b4cbdadce1b40c49fa445e39d113040e9a5b4640229c6a64';
    private const AUTHORIZATION_CODE_IDENTIFIER = '3d444653008c04f7fa754f754c3029b7ba728345fece415ae9d2b9e67dd123b68b6c9fdd97ea0468';
    private const CLIENT_UUID = '07aba895-11b6-45f9-b95f-7fdb3d9b43c1';
    private const USER_UUID = '9410cccb-c91e-4f51-a31f-f2bcadc52f94';

    /**
     * @var PersistentTokenFactory
     */
    private $tokenFactory;
    private $accessTokenRepository;
    private $clientRepository;
    private $adherentRepository;
    private $deviceRepository;

    public function testCreateAuthorizationCode(): void
    {
        $token = $this->createAuthorizationCode();
        $token->setClient($this->createClient());
        $token->setUserIdentifier(self::USER_UUID);
        $token->setExpiryDateTime($expectedDate = new \DateTimeImmutable('+5 hours'));
        $token->addScope($this->createScope('read:users'));
        $token->addScope($this->createScope('write:users'));
        $token->setRedirectUri('https://app.foo-bar.com/oauth');

        $this
            ->adherentRepository
            ->expects($this->any())
            ->method('findByUuid')
            ->with(self::USER_UUID)
            ->willReturn($user = $this->createMock(Adherent::class))
        ;

        $this
            ->clientRepository
            ->expects($this->any())
            ->method('findClientByUuid')
            ->with(self::CLIENT_UUID)
            ->willReturn($client = $this->createMock(Client::class))
        ;

        $authCode = $this->tokenFactory->createAuthorizationCode($token);

        $this->assertEquals(
            Uuid::uuid5(Uuid::NAMESPACE_OID, self::AUTHORIZATION_CODE_IDENTIFIER),
            $authCode->getUuid()
        );
        $this->assertSame(self::AUTHORIZATION_CODE_IDENTIFIER, $authCode->getIdentifier());
        $this->assertSame($expectedDate->format('U'), $authCode->getExpiryDateTime()->format('U'));
        $this->assertSame($user, $authCode->getUser());
        $this->assertSame($client, $authCode->getClient());
        $this->assertSame('https://app.foo-bar.com/oauth', $authCode->getRedirectUri());
        $this->assertTrue($authCode->hasScope('read:users'));
        $this->assertTrue($authCode->hasScope('write:users'));
    }

    public function testCreateAuthorizationCodeRedirectUriFallbackToClient(): void
    {
        $token = $this->createAuthorizationCode();
        $token->setClient($this->createClient());
        $token->setUserIdentifier(self::USER_UUID);
        $token->setExpiryDateTime(new \DateTimeImmutable('+5 hours'));
        $token->addScope($this->createScope('read:users'));
        $token->addScope($this->createScope('write:users'));

        $this
            ->adherentRepository
            ->expects($this->any())
            ->method('findByUuid')
            ->with(self::USER_UUID)
            ->willReturn($this->createMock(Adherent::class))
        ;

        $this
            ->clientRepository
            ->expects($this->any())
            ->method('findClientByUuid')
            ->with(self::CLIENT_UUID)
            ->willReturn(new Client(null, 'client', 'description', 'secret', [], ['http://client.com/fallback']))
        ;

        $authCode = $this->tokenFactory->createAuthorizationCode($token);
        $this->assertSame('http://client.com/fallback', $authCode->getRedirectUri());
    }

    public function testCreateAuthorizationCodeThrowsExeeptionIfTokenRedirectUriIsEmptyAndClientHaveMoreThan1RedirectUris(): void
    {
        $token = $this->createAuthorizationCode();
        $token->setClient($this->createClient());
        $token->setUserIdentifier(self::USER_UUID);
        $token->setExpiryDateTime(new \DateTimeImmutable('+5 hours'));
        $token->addScope($this->createScope('read:users'));
        $token->addScope($this->createScope('write:users'));

        $this
            ->adherentRepository
            ->expects($this->any())
            ->method('findByUuid')
            ->with(self::USER_UUID)
            ->willReturn($this->createMock(Adherent::class))
        ;

        $this
            ->clientRepository
            ->expects($this->any())
            ->method('findClientByUuid')
            ->with(self::CLIENT_UUID)
            ->willReturn(new Client(null, 'client', 'description', 'secret', [], ['http://client.com/fallback', 'http://client2.com']))
        ;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot determined which redirect URI to use');
        $this->tokenFactory->createAuthorizationCode($token);
    }

    public function testCreateAccessToken(): void
    {
        $token = $this->createAccessToken();
        $token->setClient($this->createClient());
        $token->setUserIdentifier(self::USER_UUID);
        $token->setExpiryDateTime($expectedDate = \DateTimeImmutable::createFromFormat('U', time() + 5 * 60 * 60));
        $token->addScope($this->createScope('read:users'));
        $token->addScope($this->createScope('write:users'));

        $this
            ->adherentRepository
            ->expects($this->any())
            ->method('findByUuid')
            ->with(self::USER_UUID)
            ->willReturn($user = $this->createMock(Adherent::class))
        ;

        $this
            ->clientRepository
            ->expects($this->any())
            ->method('findClientByUuid')
            ->with(self::CLIENT_UUID)
            ->willReturn($client = $this->createMock(Client::class))
        ;

        $accessToken = $this->tokenFactory->createAccessToken($token);

        $this->assertEquals(
            Uuid::uuid5(Uuid::NAMESPACE_OID, self::ACCESS_TOKEN_IDENTIFIER),
            $accessToken->getUuid()
        );
        $this->assertSame(self::ACCESS_TOKEN_IDENTIFIER, $accessToken->getIdentifier());
        $this->assertSame($expectedDate->format('U'), $accessToken->getExpiryDateTime()->format('U'));
        $this->assertSame($user, $accessToken->getUser());
        $this->assertSame($client, $accessToken->getClient());
        $this->assertTrue($accessToken->hasScope('read:users'));
        $this->assertTrue($accessToken->hasScope('write:users'));
    }

    public function testTryCreateAccessTokenWithoutUserFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to find App\Entity\Adherent entity by its identifier "9410cccb-c91e-4f51-a31f-f2bcadc52f94".');

        $token = $this->createAccessToken();
        $token->setUserIdentifier(self::USER_UUID);

        $this
            ->adherentRepository
            ->expects($this->any())
            ->method('findByUuid')
            ->with(self::USER_UUID)
            ->willReturn(null)
        ;

        $this->tokenFactory->createAccessToken($token);
    }

    public function testTryCreateAccessTokenWithoutClientFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to find App\Entity\OAuth\Client entity by its identifier "07aba895-11b6-45f9-b95f-7fdb3d9b43c1".');
        $token = $this->createAccessToken();
        $token->setClient($this->createClient());
        $token->setUserIdentifier(self::USER_UUID);

        $this
            ->adherentRepository
            ->expects($this->any())
            ->method('findByUuid')
            ->with(self::USER_UUID)
            ->willReturn($this->createMock(Adherent::class))
        ;

        $this
            ->clientRepository
            ->expects($this->any())
            ->method('findClientByUuid')
            ->with(self::CLIENT_UUID)
            ->willReturn(null)
        ;

        $this->tokenFactory->createAccessToken($token);
    }

    public function testCreateRefreshToken(): void
    {
        $token = new InMemoryRefreshToken();
        $token->setIdentifier(self::REFRESH_TOKEN_IDENTIFIER);
        $token->setAccessToken($this->createAccessToken());
        $token->setExpiryDateTime($expirationDate = new \DateTimeImmutable('+6 hours'));

        $this
            ->accessTokenRepository
            ->expects($this->any())
            ->method('findAccessTokenByIdentifier')
            ->with(self::ACCESS_TOKEN_IDENTIFIER)
            ->willReturn($accessToken = $this->createMock(AccessToken::class))
        ;

        $refreshToken = $this->tokenFactory->createRefreshToken($token);

        $this->assertEquals(
            Uuid::uuid5(Uuid::NAMESPACE_OID, self::REFRESH_TOKEN_IDENTIFIER),
            $refreshToken->getUuid()
        );
        $this->assertSame(self::REFRESH_TOKEN_IDENTIFIER, $refreshToken->getIdentifier());
        $this->assertSame($expirationDate->format('U'), $refreshToken->getExpiryDateTime()->format('U'));
        $this->assertSame($accessToken, $refreshToken->getAccessToken());
    }

    private function createAuthorizationCode(): InMemoryAuthorizationCode
    {
        $token = new InMemoryAuthorizationCode();
        $token->setIdentifier(self::AUTHORIZATION_CODE_IDENTIFIER);

        return $token;
    }

    private function createAccessToken(): InMemoryAccessToken
    {
        $token = new InMemoryAccessToken();
        $token->setIdentifier(self::ACCESS_TOKEN_IDENTIFIER);

        return $token;
    }

    private function createClient(): InMemoryClient
    {
        return new InMemoryClient(self::CLIENT_UUID, []);
    }

    private function createScope(string $identifier): InMemoryScope
    {
        return new InMemoryScope($identifier);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->accessTokenRepository = $this->createMock(AccessTokenRepository::class);
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->adherentRepository = $this->createMock(AdherentRepository::class);
        $this->deviceRepository = $this->createMock(DeviceRepository::class);
        $this->tokenFactory = new PersistentTokenFactory(
            $this->accessTokenRepository,
            $this->clientRepository,
            $this->adherentRepository,
            $this->deviceRepository
        );
    }

    protected function tearDown(): void
    {
        $this->accessTokenRepository = null;
        $this->clientRepository = null;
        $this->adherentRepository = null;
        $this->deviceRepository = null;
        $this->tokenFactory = null;

        parent::tearDown();
    }
}
