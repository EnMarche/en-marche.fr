<?php

namespace App\Entity\ApplicationRequest;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="application_request_tag")
 * @ORM\Entity
 */
class ApplicationRequestTag
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): int
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
}
