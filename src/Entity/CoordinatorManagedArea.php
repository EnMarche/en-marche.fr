<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="coordinator_managed_areas")
 * @ORM\Entity
 */
class CoordinatorManagedArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The codes of the managed zones.
     *
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $codes;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $sector;

    public function __construct(array $codes = [], string $sector = '')
    {
        $this->codes = $codes;
        $this->sector = $sector;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodes(): array
    {
        return $this->codes;
    }

    public function setCodes(array $codes): void
    {
        $this->codes = $codes;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(?string $sector): void
    {
        $this->sector = $sector;
    }

    public function __toString(): string
    {
        return implode(', ', $this->codes);
    }
}
