<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionTypeRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"code"})})
 *
 * @UniqueEntity(fields={"code"})
 */
class SubscriptionType
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
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
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(length=64, unique=true, nullable=true)
     *
     * @Assert\Length(max=64)
     */
    private $externalId;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    private $position = 0;

    public function __construct(string $label = null, string $code = null, string $externalId = null)
    {
        $this->label = $label;
        $this->code = $code;
        $this->externalId = $externalId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}
