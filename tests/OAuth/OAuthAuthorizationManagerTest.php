<?php

namespace Tests\App\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\UserAuthorization;
use App\OAuth\Model\Scope;
use App\OAuth\OAuthAuthorizationManager;
use App\Repository\OAuth\UserAuthorizationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OAuthAuthorizationManagerTest extends TestCase
{
    /** @var UserAuthorizationRepository|MockObject */
    private $userAuthorizationRepository;

    public function testIsAuthorizeReturnFalseWhenClientScopeIsSupported(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();

        $this->userAuthorizationRepository->expects($this->never())->method($this->anything());

        static::assertFalse(
            $userAuthorizationManager->isAuthorized($this->createUser(), $this->createClient(), [Scope::WRITE_USERS()])
        );
        static::assertFalse( // Test with Client allowed not to ask for user authorization
            $userAuthorizationManager->isAuthorized($this->createUser(), $this->createClient(false), [Scope::WRITE_USERS()])
        );
    }

    public function testIsAuthorizedReturnTrueWhenClientScopesAndUserAuthorizationScopesAreValid(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $scopes = [Scope::READ_USERS()];
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn($this->createUserAuthorization($user, $client))
        ;

        static::assertTrue(
            $userAuthorizationManager->isAuthorized($user, $client, $scopes)
        );
    }

    public function testIsAuthorizedReturnTrueForClientAllowedToByPassUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();

        $this->userAuthorizationRepository->expects($this->never())->method($this->anything());

        static::assertTrue(
            $userAuthorizationManager->isAuthorized($this->createUser(), $this->createClient(false), [Scope::READ_USERS()])
        );
    }

    public function testIsAuthorizedReturnFalseIfScopeIsNotAllowedByTheUser(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn($this->createUserAuthorization($user, $client, [Scope::WRITE_USERS()]))
        ;

        static::assertFalse(
            $userAuthorizationManager->isAuthorized($user, $client, [Scope::READ_USERS()])
        );
    }

    public function testRecordUpdateUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn($userAuthorization = $this->createUserAuthorization($user, $client, [Scope::READ_USERS()]))
        ;

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('save')
            ->with($userAuthorization)
        ;

        $scopes = [Scope::READ_USERS(), Scope::WRITE_USERS()];
        $userAuthorizationManager->record($user, $client, $scopes);
        static::assertTrue($userAuthorization->supportsScopes($scopes));
    }

    public function testRecordCreateUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn(null)
        ;

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('save')
        ;

        $userAuthorizationManager->record($user, $client, [Scope::READ_USERS()]);
    }

    public function testRecordDoesNotPersistUserAuthorizationWhenClientIsAllowedNotToAskUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();

        $this->userAuthorizationRepository
            ->expects($this->never())
            ->method('findByUserAndClient')
        ;

        $this->userAuthorizationRepository
            ->expects($this->never())
            ->method('save')
        ;

        $userAuthorizationManager->record($this->createUser(), $this->createClient(false), [Scope::WRITE_USERS()]);
    }

    private function createUser(): Adherent
    {
        return $this->createMock(Adherent::class);
    }

    private function createClient(bool $askForUserAuthorization = true): Client
    {
        $client = new Client();
        $client->addSupportedScope(Scope::READ_USERS);
        $client->setAskUserForAuthorization($askForUserAuthorization);

        return $client;
    }

    private function createOAuthAuthorizationManager(): OAuthAuthorizationManager
    {
        $this->userAuthorizationRepository = $this->createMock(UserAuthorizationRepository::class);

        return new OAuthAuthorizationManager($this->userAuthorizationRepository);
    }

    private function createUserAuthorization(Adherent $user, Client $client, array $scopes = []): UserAuthorization
    {
        return new UserAuthorization(null, $user, $client, $scopes ?: [Scope::READ_USERS()]);
    }
}
