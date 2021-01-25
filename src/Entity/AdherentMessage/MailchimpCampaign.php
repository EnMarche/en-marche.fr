<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageSynchronizedObjectInterface;
use App\Entity\MailchimpSegment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity
 */
class MailchimpCampaign implements AdherentMessageSynchronizedObjectInterface
{
    use TimestampableEntity;

    public const STATUS_SENT = 'sent';
    public const STATUS_ERROR = 'error';
    public const STATUS_DRAFT = 'draft';

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var MailchimpCampaignReport|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AdherentMessage\MailchimpCampaignReport", cascade={"all"})
     */
    private $report;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $externalId;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $synchronized = false;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recipientCount;

    /**
     * @var AdherentMessageInterface|AbstractAdherentMessage
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AdherentMessage\AbstractAdherentMessage", inversedBy="mailchimpCampaigns")
     */
    private $message;

    /**
     * @var int|null
     *
     * @ORM\Column(nullable=true)
     */
    private $staticSegmentId;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = self::STATUS_DRAFT;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $detail;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $city;

    /**
     * @var MailchimpSegment[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MailchimpSegment", cascade={"all"})
     */
    private $mailchimpSegments;

    public function __construct(AdherentMessageInterface $message)
    {
        $this->message = $message;
        $this->mailchimpSegments = new ArrayCollection();
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function getRecipientCount(): ?int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    public function getMessage(): AdherentMessageInterface
    {
        return $this->message;
    }

    public function getStaticSegmentId(): ?int
    {
        return $this->staticSegmentId;
    }

    public function setStaticSegmentId(?int $staticSegmentId): void
    {
        $this->staticSegmentId = $staticSegmentId;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return MailchimpSegment[]
     */
    public function getMailchimpSegments(): array
    {
        return $this->mailchimpSegments->toArray();
    }

    public function addMailchimpSegment(MailchimpSegment $mailchimpSegment): void
    {
        if (!$this->mailchimpSegments->contains($mailchimpSegment)) {
            $this->mailchimpSegments->add($mailchimpSegment);
        }
    }

    public function removeMailchimpSegment(MailchimpSegment $mailchimpSegment): void
    {
        $this->mailchimpSegments->removeElement($mailchimpSegment);
    }

    public function markAsSent(): void
    {
        $this->status = self::STATUS_SENT;
    }

    public function markAsError(string $errorMessage = null): void
    {
        $this->status = self::STATUS_ERROR;
        $this->detail = $errorMessage;
    }

    public function isError(): bool
    {
        return self::STATUS_ERROR === $this->status;
    }

    public function isDraft(): bool
    {
        return self::STATUS_DRAFT === $this->status;
    }

    public function isSent(): bool
    {
        return self::STATUS_SENT === $this->status;
    }

    public function reset(): void
    {
        $this->synchronized = false;

        $this->recipientCount =
        $this->label =
        $this->city =
        $this->staticSegmentId = null;
    }

    public function getReport(): ?MailchimpCampaignReport
    {
        return $this->report;
    }

    public function setReport(?MailchimpCampaignReport $report): void
    {
        $this->report = $report;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function resetFilter(): void
    {
        $this->staticSegmentId = $this->city = null;
        $this->mailchimpSegments = new ArrayCollection();
    }
}
