<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Messenger\Message\AsyncMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentCommandInterface extends AsyncMessageInterface
{
    public function getUuid(): UuidInterface;

    public function getEmailAddress(): string;

    public function getRemovedTags(): array;
}
