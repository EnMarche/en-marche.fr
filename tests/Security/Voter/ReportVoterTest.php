<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Report\ReportPermissions;
use App\Security\Voter\ReportVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ReportVoterTest extends TestCase
{
    /**
     * @var ReportVoter
     */
    private $voter;

    /**
     * @var MockObject|AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->voter = new ReportVoter($this->authorizationChecker);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->voter = new ReportVoter($this->authorizationChecker);
    }

    public function provideUsers(): iterable
    {
        yield [null];
        yield [$this->createMock(Adherent::class)];
    }

    /**
     * @dataProvider provideUsers
     */
    public function testGrantedWhenAuthenticatedFully($user)
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with(['IS_AUTHENTICATED_REMEMBERED'])
            ->willReturn(true)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, null, [ReportPermissions::REPORT])
        );
    }

    /**
     * @dataProvider provideUsers
     */
    public function testNotGrantedWhenNotAuthenticatedFully($user)
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with(['IS_AUTHENTICATED_REMEMBERED'])
            ->willReturn(false)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, null, [ReportPermissions::REPORT])
        );
    }

    public function testNotGrantedWhenAdministrator()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(Administrator::class))
        ;

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted')
        ;

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, null, [ReportPermissions::REPORT])
        );
    }
}
