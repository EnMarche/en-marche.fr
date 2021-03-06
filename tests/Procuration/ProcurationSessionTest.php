<?php

namespace Tests\App\Procuration;

use App\Entity\ProcurationRequest;
use App\Procuration\ElectionContext;
use App\Procuration\Exception\InvalidProcurationFlowException;
use App\Procuration\ProcurationSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * @group procuration
 */
class ProcurationSessionTest extends TestCase
{
    /** @var SessionInterface|MockObject */
    private $session;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
    }

    protected function tearDown(): void
    {
        $this->session = null;
    }

    public function testStartRequest()
    {
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(true)
        ;
        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('app_procuration_model', $this->isInstanceOf(ProcurationRequest::class))
        ;

        $procuration->startRequest();
    }

    public function testStartRequestRequiresElectionContext()
    {
        $this->expectException(InvalidProcurationFlowException::class);
        $this->expectExceptionMessage('An election context is required to start the flow.');
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(false)
        ;
        $this->session
            ->expects($this->never())
            ->method('set')
        ;

        $procuration->startRequest();
    }

    public function testEndRequest()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('app_procuration_model', true);
        $session->set('app_procuration_election_context', true);

        $this->assertTrue($session->has('app_procuration_model'));
        $this->assertTrue($session->has('app_procuration_election_context'));

        $procuration = new ProcurationSession($session);
        $procuration->endRequest();

        $this->assertFalse($session->has('app_procuration_model'));
        $this->assertFalse($session->has('app_procuration_election_context'));
    }

    public function testGetCurrentRequest()
    {
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_model')
            ->willReturn(true)
        ;
        $this->session
            ->expects($this->never())
            ->method('set')
        ;
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('app_procuration_model')
            ->willReturn(new ProcurationRequest())
        ;

        $this->assertInstanceOf(ProcurationRequest::class, $procuration->getCurrentRequest());
    }

    public function testGetCurrentRequestStartSessionIfNotStartedYet()
    {
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->at(0))
            ->method('has')
            ->with('app_procuration_model')
            ->willReturn(false)
        ;
        $this->session
            ->expects($this->at(1))
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(true)
        ;
        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('app_procuration_model', $this->isInstanceOf(ProcurationRequest::class))
        ;
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('app_procuration_model')
            ->willReturn(new ProcurationRequest())
        ;

        $this->assertInstanceOf(ProcurationRequest::class, $procuration->getCurrentRequest());
    }

    public function testGetElectionContext()
    {
        $context = new ElectionContext();
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(true)
        ;
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('app_procuration_election_context')
            ->willReturn(serialize($context))
        ;

        $this->assertEquals($context, $procuration->getElectionContext());
    }

    public function testGetElectionContextRequiresContext()
    {
        $this->expectException(InvalidProcurationFlowException::class);
        $this->expectExceptionMessage('No election context.');
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(false)
        ;
        $this->session
            ->expects($this->never())
            ->method('get')
        ;

        $procuration->getElectionContext();
    }

    public function testSetElectionContext()
    {
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->exactly(2))
            ->method('remove')
            ->withConsecutive(['app_procuration_model'], ['app_procuration_election_context'])
        ;
        // Setting the context should reset the procuration flow
        $this->session
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['app_procuration_election_context', $this->matchesRegularExpression('~C:\d{2}:"Mock_ElectionContext_.{8}":4:{test}~')],
                ['app_procuration_model', $this->isInstanceOf(ProcurationRequest::class)]
            )
        ;
        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(true)
        ;

        $context = $this->createMock(ElectionContext::class);
        $context
            ->expects($this->once())
            ->method('serialize')
            ->willReturn('test')
        ;

        $procuration->setElectionContext($context);
    }
}
