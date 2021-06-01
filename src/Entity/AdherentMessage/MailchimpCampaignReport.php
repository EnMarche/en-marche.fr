<?php

namespace App\Entity\AdherentMessage;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity
 */
class MailchimpCampaignReport implements Timestampable
{
    use TimestampableEntity;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $openTotal = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $openUnique = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $openRate = 0;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastOpen;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $clickTotal = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $clickUnique = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $clickRate = 0;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastClick;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $emailSent = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $unsubscribed = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $unsubscribedRate = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenTotal(): int
    {
        return $this->openTotal;
    }

    public function setOpenTotal(int $openTotal): void
    {
        $this->openTotal = $openTotal;
    }

    public function getOpenUnique(): int
    {
        return $this->openUnique;
    }

    public function setOpenUnique(int $openUnique): void
    {
        $this->openUnique = $openUnique;
    }

    public function getOpenRate(): int
    {
        return $this->openRate;
    }

    public function setOpenRate(int $openRate): void
    {
        $this->openRate = $openRate;
    }

    public function getLastOpen(): ?\DateTime
    {
        return $this->lastOpen;
    }

    public function setLastOpen(?\DateTime $lastOpen): void
    {
        $this->lastOpen = $lastOpen;
    }

    public function getClickTotal(): int
    {
        return $this->clickTotal;
    }

    public function setClickTotal(int $clickTotal): void
    {
        $this->clickTotal = $clickTotal;
    }

    public function getClickUnique(): int
    {
        return $this->clickUnique;
    }

    public function setClickUnique(int $clickUnique): void
    {
        $this->clickUnique = $clickUnique;
    }

    public function getClickRate(): int
    {
        return $this->clickRate;
    }

    public function setClickRate(int $clickRate): void
    {
        $this->clickRate = $clickRate;
    }

    public function getLastClick(): ?\DateTime
    {
        return $this->lastClick;
    }

    public function setLastClick(?\DateTime $lastClick): void
    {
        $this->lastClick = $lastClick;
    }

    public function getEmailSent(): int
    {
        return $this->emailSent;
    }

    public function setEmailSent(int $emailSent): void
    {
        $this->emailSent = $emailSent;
    }

    public function getUnsubscribed(): int
    {
        return $this->unsubscribed;
    }

    public function setUnsubscribed(int $unsubscribed): void
    {
        $this->unsubscribed = $unsubscribed;
    }

    public function getUnsubscribedRate(): int
    {
        return $this->unsubscribedRate;
    }

    public function setUnsubscribedRate(int $unsubscribedRate): void
    {
        $this->unsubscribedRate = $unsubscribedRate;
    }
}
