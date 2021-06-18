<?php

namespace Tests\App\Procuration;

use App\Entity\ProcurationProxy;
use App\Procuration\ProcurationReliabilityProcessor;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 * @group procuration
 */
class ProcurationReliabilityProcessorTest extends AbstractKernelTestCase
{
    /**
     * @var ProcurationReliabilityProcessor
     */
    private $procurationReliabilityProcessor;

    /**
     * @dataProvider provideReliabilities
     */
    public function testProcess(string $email, int $expectedReliability): void
    {
        $proxy = new ProcurationProxy();
        $proxy->setEmailAddress($email);

        $this->assertSame(1, $proxy->getReliability());

        $this->procurationReliabilityProcessor->process($proxy);

        $this->assertSame($expectedReliability, $proxy->getReliability());
    }

    public function provideReliabilities(): \Generator
    {
        yield ['unknown@test.com', 1];
        yield ['simple-user@example.ch', 1];
        yield ['michelle.dufour@example.ch', 4];
        yield ['michel.vasseur@example.ch', 4];
        yield ['coordinatrice-cp@en-marche-dev.fr', 4];
        yield ['lolodie.dutemps@hotnix.tld', 6];
        yield ['damien.schmidt@example.ch', 6];
        yield ['luciole1989@spambox.fr', 8];
        yield ['deputy-ch-li@en-marche-dev.fr', 8];
        yield ['coordinateur@en-marche-dev.fr', 8];
        yield ['referent@en-marche-dev.fr', 8];
        yield ['municipal-chief-3@en-marche-dev.fr', 8];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->procurationReliabilityProcessor = new ProcurationReliabilityProcessor($this->getAdherentRepository());
    }

    protected function tearDown(): void
    {
        $this->procurationReliabilityProcessor = null;

        parent::tearDown();
    }
}
