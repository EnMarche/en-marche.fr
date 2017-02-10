<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\NewsletterSubscription;

class ManagedUser
{
    const TYPE_ADHERENT = 'adherent';
    const TYPE_NEWSLETTER_SUBSCRIBER = 'newsletter_subscriber';

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $emailVisible;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string|null
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var \DateTime|null
     */
    private $birthdate;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var bool
     */
    private $referentsEmailsSubscription;

    public function __construct(
        string $type,
        int $id,
        string $email,
        string $postalCode,
        bool $emailVisible = false,
        string $firstName = null,
        string $lastName = null,
        \DateTime $birthdate = null,
        string $city = null,
        string $country = null,
        bool $referentsEmailsSubscription = true
    ) {
        if (!in_array($type, [self::TYPE_ADHERENT, self::TYPE_NEWSLETTER_SUBSCRIBER], true)) {
            throw new \InvalidArgumentException('Invalid ManagedUser type');
        }

        $this->type = $type;
        $this->id = $id;
        $this->email = $email;
        $this->emailVisible = $emailVisible;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->birthdate = $birthdate;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->country = $country;
        $this->referentsEmailsSubscription = $referentsEmailsSubscription;
    }

    public static function createFromAdherent(Adherent $adherent): ManagedUser
    {
        return new self(
            self::TYPE_ADHERENT,
            $adherent->getId(),
            $adherent->getEmailAddress(),
            $adherent->getPostalCode(),
            $adherent->isHost(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getBirthdate(),
            $adherent->getCityName(),
            $adherent->getCountry(),
            $adherent->hasSubscribedReferentsEmails()
        );
    }

    public static function createFromNewsletterSubscription(NewsletterSubscription $subscription): ManagedUser
    {
        return new self(
            self::TYPE_NEWSLETTER_SUBSCRIBER,
            $subscription->getId(),
            $subscription->getEmail(),
            $subscription->getPostalCode()
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isEmailVisible(): bool
    {
        return $this->emailVisible;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): ?string
    {
        if (!$this->firstName || !$this->lastName) {
            return null;
        }

        return $this->firstName.' '.$this->lastName;
    }

    public function getPartialName(): ?string
    {
        return $this->firstName.' '.$this->lastName[0];
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function getAge(): ?int
    {
        if (!$this->birthdate) {
            return null;
        }

        return $this->birthdate->diff(new \DateTime())->format('%y');
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function hasReferentsEmailsSubscription(): bool
    {
        return $this->referentsEmailsSubscription;
    }
}
