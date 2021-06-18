<?php

namespace Tests\App\Command;

use App\Command\ProcurationSendReminderCommand;
use App\Mailer\Message\ProcurationProxyReminderMessage;
use Tests\App\AbstractCommandCaseTest;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group procuration
 */
class ProcurationSendReminderCommandTest extends AbstractCommandCaseTest
{
    use ControllerTestTrait;

    public function testCommand()
    {
        $output = $this->runCommand(ProcurationSendReminderCommand::COMMAND_NAME);
        $output = $output->getDisplay();
        $this->assertStringContainsString('1 reminders sent', $output);
        $this->assertCountMails(1, ProcurationProxyReminderMessage::class);
        $this->assertCount(1, $this->getProcurationRequestRepository()->findBy(['reminded' => 1]), 'The command should add a reminder.');
    }
}
