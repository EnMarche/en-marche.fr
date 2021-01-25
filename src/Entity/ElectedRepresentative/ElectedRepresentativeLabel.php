<?php

namespace App\Entity\ElectedRepresentative;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="elected_representative_label")
 */
class ElectedRepresentativeLabel
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=50)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $onGoing = true;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\Choice(callback="getYears")
     */
    private $beginYear;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\Expression("value == null or value > this.getBeginYear()", message="La date de fin de l'étiquette doit être postérieure à la date de début.")
     * @Assert\Choice(callback="getYears")
     */
    private $finishYear;

    /**
     * @var ElectedRepresentative
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectedRepresentative\ElectedRepresentative", inversedBy="labels")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $electedRepresentative;

    public function __construct(
        string $name = null,
        ElectedRepresentative $electedRepresentative = null,
        bool $onGoing = true,
        int $beginYear = null,
        int $finishYear = null
    ) {
        $this->name = $name;
        $this->onGoing = $onGoing;
        $this->beginYear = $beginYear;
        $this->finishYear = $finishYear;
        $this->electedRepresentative = $electedRepresentative;
    }

    public static function getYears(): array
    {
        return range(date('Y'), 2007);
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
        $this->name = $name;
    }

    public function isOnGoing(): bool
    {
        return $this->onGoing;
    }

    public function setOnGoing(bool $onGoing): void
    {
        $this->onGoing = $onGoing;
    }

    public function getBeginYear(): ?int
    {
        return $this->beginYear;
    }

    public function setBeginYear(?int $beginYear): void
    {
        $this->beginYear = $beginYear;
    }

    public function getFinishYear(): ?int
    {
        return $this->finishYear;
    }

    public function setFinishYear(?int $finishYear = null): void
    {
        $this->finishYear = $finishYear;
    }

    public function getElectedRepresentative(): ?ElectedRepresentative
    {
        return $this->electedRepresentative;
    }

    public function setElectedRepresentative(ElectedRepresentative $electedRepresentative): void
    {
        $this->electedRepresentative = $electedRepresentative;
    }

    public function __toString(): string
    {
        return $this->getName().($this->beginYear ? (' ('.$this->beginYear.($this->finishYear ? (' à '.$this->finishYear) : '').')') : '');
    }
}
