<?php

namespace Tests\App\Test\Mailer\Message;

use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class DummyMessage extends Message
{
    public static function create(): DummyMessage
    {
        return new self(
            Uuid::fromString('99999999-9999-9999-9999-999999999999'),
            'dummy@example.tld',
            'Dummy User',
            'Dummy Message',
            ['dummy' => 'ymmud'],
            [],
            null,
            '66666'
        );
    }
}
