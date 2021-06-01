<?php

namespace App\Entity\AdherentMessage\Filter;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="adherent_message_filters")
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
abstract class AbstractAdherentMessageFilter implements AdherentMessageFilterInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var AdherentMessageInterface
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AdherentMessage\AbstractAdherentMessage", mappedBy="filter")
     */
    private $message;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $synchronized = false;

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function getExternalId(): ?string
    {
        return $this->message->getExternalId();
    }

    public function getMessage(): AdherentMessageInterface
    {
        return $this->message;
    }

    public function setMessage(AdherentMessageInterface $message): void
    {
        $this->message = $message;
    }
}
