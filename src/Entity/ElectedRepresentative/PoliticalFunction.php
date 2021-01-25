<?php

namespace App\Entity\ElectedRepresentative;

use App\Exception\BadPoliticalFunctionNameException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="elected_representative_political_function")
 */
class PoliticalFunction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum", "toArray"})
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\Length(max="255")
     */
    private $clarification;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $onGoing = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\DateTime
     * @Assert\Expression(
     *     "value == null or value > this.getBeginAt()",
     *     message="La date de fin doit être postérieure à la date de début."
     * )
     * @Assert\Expression(
     *     "not (value !== null and this.isOnGoing())",
     *     message="La date de fin ne peut être saisie que dans le cas où la fonction n'est pas en cours."
     * )
     */
    private $finishAt;

    /**
     * @var ElectedRepresentative|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectedRepresentative\ElectedRepresentative", inversedBy="politicalFunctions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $electedRepresentative;

    /**
     * @var Mandate|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectedRepresentative\Mandate", inversedBy="politicalFunctions")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     */
    private $mandate;

    public function __construct(
        string $name = null,
        string $clarification = null,
        ElectedRepresentative $electedRepresentative = null,
        Mandate $mandate = null,
        bool $onGoing = true,
        \DateTime $beginAt = null,
        \DateTime $finishAt = null
    ) {
        $this->name = $name;
        $this->clarification = $clarification;
        $this->electedRepresentative = $electedRepresentative;
        $this->mandate = $mandate;
        $this->onGoing = $onGoing;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (!PoliticalFunctionNameEnum::isValid($name)) {
            throw new BadPoliticalFunctionNameException(sprintf('The political function name "%s" is invalid', $name));
        }

        $this->name = $name;
    }

    public function getClarification(): ?string
    {
        return $this->clarification;
    }

    public function setClarification(?string $clarification = null): void
    {
        $this->clarification = $clarification;
    }

    public function isOnGoing(): bool
    {
        return $this->onGoing;
    }

    public function setOnGoing(bool $onGoing): void
    {
        $this->onGoing = $onGoing;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTime
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTime $finishAt = null): void
    {
        $this->finishAt = $finishAt;
    }

    public function getElectedRepresentative(): ?ElectedRepresentative
    {
        return $this->electedRepresentative;
    }

    public function setElectedRepresentative(ElectedRepresentative $electedRepresentative): void
    {
        $this->electedRepresentative = $electedRepresentative;
    }

    public function getMandate(): ?Mandate
    {
        return $this->mandate;
    }

    public function setMandate(Mandate $mandate): void
    {
        $this->mandate = $mandate;
    }

    public function getMandateZoneName(): string
    {
        if (!$this->mandate || !($zone = $this->mandate->getGeoZone())) {
            return '';
        }

        return $zone->isCity() ? $zone->getNameCode() : $zone->getName();
    }

    public function __toString(): string
    {
        return (string) array_search($this->name, PoliticalFunctionNameEnum::CHOICES);
    }
}
