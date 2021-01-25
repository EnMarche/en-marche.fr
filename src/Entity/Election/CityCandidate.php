<?php

namespace App\Entity\Election;

use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_city_candidate")
 */
class CityCandidate
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\Choice(choices=App\ValueObject\Genders::CHOICES, strict=true)
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $email;

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $profile;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $investitureType;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $politicalScheme;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $alliances;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $agreement;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    private $eligibleAdvisersCount;

    public function __construct(
        string $name = null,
        ?string $gender = null,
        ?string $email = null,
        ?PhoneNumber $phone = null,
        ?string $profile = null,
        ?string $investitureType = null,
        ?string $politicalScheme = null,
        ?string $alliances = null,
        ?bool $agreement = false,
        ?int $eligibleAdvisersCount = null
    ) {
        $this->name = $name;
        $this->gender = $gender;
        $this->email = $email;
        $this->phone = $phone;
        $this->profile = $profile;
        $this->investitureType = $investitureType;
        $this->politicalScheme = $politicalScheme;
        $this->alliances = $alliances;
        $this->agreement = $agreement;
        $this->eligibleAdvisersCount = $eligibleAdvisersCount;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(?string $profile): void
    {
        $this->profile = $profile;
    }

    public function getInvestitureType(): ?string
    {
        return $this->investitureType;
    }

    public function setInvestitureType(?string $investitureType): void
    {
        $this->investitureType = $investitureType;
    }

    public function getPoliticalScheme(): ?string
    {
        return $this->politicalScheme;
    }

    public function setPoliticalScheme(?string $politicalScheme): void
    {
        $this->politicalScheme = $politicalScheme;
    }

    public function getAlliances(): ?string
    {
        return $this->alliances;
    }

    public function setAlliances(?string $alliances): void
    {
        $this->alliances = $alliances;
    }

    public function hasAgreement(): bool
    {
        return $this->agreement;
    }

    public function setAgreement(bool $agreement): void
    {
        $this->agreement = $agreement;
    }

    public function getEligibleAdvisersCount(): ?int
    {
        return $this->eligibleAdvisersCount;
    }

    public function setEligibleAdvisersCount(?int $eligibleAdvisersCount): void
    {
        $this->eligibleAdvisersCount = $eligibleAdvisersCount;
    }

    public function isEmpty(): bool
    {
        return !$this->name && !$this->email;
    }
}
