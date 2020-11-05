<?php

namespace App\Entity\Geo;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait GeoTrait
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     *
     * @SymfonySerializer\Groups({"autocomplete"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->code);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @SymfonySerializer\Groups({"autocomplete"})
     */
    public function getNameCode(): string
    {
        return \sprintf('%s (%s)', $this->name, $this->code);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(bool $active = true): void
    {
        $this->active = $active;
    }
}
