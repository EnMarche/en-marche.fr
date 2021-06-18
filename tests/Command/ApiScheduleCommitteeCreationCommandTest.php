<?php

namespace Tests\App\Command;

use Tests\App\AbstractCommandCaseTest;

/**
 * @group command
 */
class ApiScheduleCommitteeCreationCommandTest extends AbstractCommandCaseTest
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:committees');

        $output = $output->getDisplay();

        $this->assertStringContainsString('Starting synchronization.', $output);
        $this->assertStringContainsString('16/16', $output);
        $this->assertStringContainsString('Successfully scheduled for synchronization!', $output);
    }
}
