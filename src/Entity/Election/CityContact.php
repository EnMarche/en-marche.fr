<?php

namespace App\Entity\Election;

use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_city_contact")
 */
class CityContact
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
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $function;

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
    private $caller;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $done;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var CityCard|null
     *
     * @ORM\ManyToOne(targetEntity=CityCard::class, inversedBy="contacts")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $city;

    public function __construct(
        CityCard $city = null,
        string $name = null,
        ?string $function = null,
        ?PhoneNumber $phone = null,
        ?string $caller = null,
        ?bool $done = false,
        ?string $comment = null
    ) {
        $this->city = $city;
        $this->name = $name;
        $this->function = $function;
        $this->phone = $phone;
        $this->caller = $caller;
        $this->done = $done;
        $this->comment = $comment;

        if (!$this->phone) {
            $this->phone = new PhoneNumber();
            $this->phone->setCountryCode(33);
        }
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

    public function getFunction(): ?string
    {
        return $this->function;
    }

    public function setFunction(?string $function): void
    {
        $this->function = $function;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getCaller(): ?string
    {
        return $this->caller;
    }

    public function setCaller(?string $caller): void
    {
        $this->caller = $caller;
    }

    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): void
    {
        $this->done = $done;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCity(): ?CityCard
    {
        return $this->city;
    }

    public function setCity(?CityCard $city): void
    {
        $this->city = $city;
    }
}
