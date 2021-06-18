<?php

namespace Tests\App\Command;

use Tests\App\AbstractCommandCaseTest;

/**
 * @group command
 */
class DonationUpdateReferenceCommandTest extends AbstractCommandCaseTest
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:donations:update-reference');

        $output = $output->getDisplay();

        $this->assertStringContainsString('Starting Donations reference update.', $output);
        $this->assertStringContainsString('Updated 7 Donations reference.', $output);
        $this->assertStringContainsString('Donations reference updated successfully!', $output);
    }
}
