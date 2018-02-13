<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\EventCancellationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class EventCancellationMessageTest extends AbstractEventMessageTest
{
    const SEARCH_EVENTS_URL = 'https://test.enmarche.code/evenements';

    public function testCreateEventCancellationMessage()
    {
        $recipients[] = $this->createRegistrationMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createRegistrationMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createRegistrationMock('ez@example.com', 'Éric', 'Zitrone');

        $message = EventCancellationMessage::create(
            $recipients,
            $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron'),
            $this->createEventMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386'),
            self::SEARCH_EVENTS_URL
        );

        $this->assertInstanceOf(EventCancellationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(2, $message->getVars());
        $this->assertSame(
            [
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SEARCH_EVENTS_URL,
            ],
            $message->getVars()
        );
        $this->assertCount(4, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jb@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jean Berenger', $recipient->getFullName());
        $this->assertSame(
            [
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SEARCH_EVENTS_URL,
                'target_firstname' => 'Jean',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(2);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SEARCH_EVENTS_URL,
                'target_firstname' => 'Éric',
            ],
            $recipient->getVars()
        );
    }
}
