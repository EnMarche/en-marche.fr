<?php

namespace App\Event\EventListener;

use App\Event\EventEvent;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddFirstEventRegistrationSubscriber implements EventSubscriberInterface
{
    private $handler;

    public function __construct(EventRegistrationCommandHandler $handler)
    {
        $this->handler = $handler;
    }

    public function onEventCreated(EventEvent $event): void
    {
        if ($author = $event->getAuthor()) {
            $this->handler->handle(new EventRegistrationCommand($event->getEvent(), $author), false);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_CREATED => 'onEventCreated',
        ];
    }
}
