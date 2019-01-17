<?php

namespace AppBundle\Entity\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Filter\FilterDataObjectInterface;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="adherent_messages")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     AdherentMessageTypeEnum::REFERENT: "ReferentAdherentMessage",
 *     AdherentMessageTypeEnum::DEPUTY: "DeputyAdherentMessage"
 * })
 */
abstract class AbstractAdherentMessage implements AdherentMessageInterface
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = AdherentMessageStatusEnum::DRAFT;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $synchronized = false;

    /**
     * @var FilterDataObjectInterface|null
     *
     * @ORM\Column(type="object", nullable=true)
     */
    private $filter;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recipientCount;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    public function __construct(UuidInterface $uuid, Adherent $author)
    {
        $this->uuid = $uuid;
        $this->author = $author;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        return new static(Uuid::uuid4(), $adherent);
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function markAsSent(): void
    {
        $this->sentAt = new \DateTime();
        $this->status = AdherentMessageStatusEnum::SENT_SUCCESSFULLY;
    }

    public function isSent(): bool
    {
        return AdherentMessageStatusEnum::SENT_SUCCESSFULLY === $this->status;
    }

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function getFilter(): ?FilterDataObjectInterface
    {
        return $this->filter;
    }

    public function setFilter(FilterDataObjectInterface $filter): void
    {
        $this->filter = $filter;
    }

    public function resetFilter(): void
    {
        $this->filter = null;
    }

    public function getRecipientCount(): ?int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    public function getFromName(): ?string
    {
        return $this->author ? $this->author->getFullName() : null;
    }

    public function getReplyTo(): ?string
    {
        return null;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function updateFromDataObject(AdherentMessageDataObject $dataObject): self
    {
        if ($dataObject->getContent()) {
            $this->setContent($dataObject->getContent());
        }

        if ($dataObject->getLabel()) {
            $this->setLabel($dataObject->getLabel());
        }

        if ($dataObject->getSubject()) {
            $this->setSubject($dataObject->getSubject());
        }

        return $this;
    }
}
