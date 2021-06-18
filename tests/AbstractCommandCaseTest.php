<?php

namespace Tests\App;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandCaseTest extends AbstractKernelTestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application(self::$kernel);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->application = null;
    }

    protected function runCommand(string $commandName, array $input = [], array $options = []): CommandTester
    {
        $command = $this->application->find($commandName);
        $tester = new CommandTester($command);
        $tester->execute($input, $options);

        return $tester;
    }
}
