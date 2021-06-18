<?php

namespace Tests\App\Command;

use Tests\App\AbstractCommandCaseTest;

/**
 * @group command
 */
class ApiScheduleEventCreationCommandTest extends AbstractCommandCaseTest
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:events');

        $output = $output->getDisplay();

        $this->assertStringContainsString('Starting synchronization.', $output);
        $this->assertStringContainsString('22/22', $output);
        $this->assertStringContainsString('Successfully scheduled for synchronization!', $output);
    }
}
