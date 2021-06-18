<?php

namespace Tests\App\Test\Mailer;

use App\Mailer\EmailClientInterface;
use App\Mailer\EmailTemplateInterface;
use Psr\Log\LoggerInterface;

class NullEmailClient implements EmailClientInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function sendEmail(string $email): string
    {
        if ($this->logger) {
            $this->logger->info('[mailer] sending email with Mailer.', ['email' => $email]);
        }

        return 'Delivered using NULL client';
    }

    public function renderEmail(EmailTemplateInterface $email): string
    {
        return
            '<!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Email template</title>
                </head>
                <body style="background:#fff;text-align:center">
                    <div>
                        <h1>Email content</h1>
                        <small>With ❤️ from NULL client</small>
                    </div>
                </body>
            </html>';
    }
}
