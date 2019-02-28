<?php

namespace AppBundle\Entity\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageSynchronizedObjectInterface;
use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AuthoredInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentMessageInterface extends AuthoredInterface, AdherentMessageSynchronizedObjectInterface
{
    public function getLabel(): ?string;

    public function setExternalId(string $id): void;

    public function setRecipientCount(?int $recipientCount): void;

    public function getUuid(): UuidInterface;

    public function getType(): string;

    public function getSubject(): ?string;

    public function getContent(): ?string;

    public function getFilter(): ?AdherentMessageFilterInterface;

    public function setFilter(AdherentMessageFilterInterface $filter): void;

    public function getFromName(): ?string;

    public function getReplyTo(): ?string;

    public function hasReadOnlyFilter(): bool;
}
