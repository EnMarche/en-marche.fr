<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserListDefinitionRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_list_definition_type_code_unique", columns={"type", "code"})
 * })
 */
class UserListDefinition
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\UserListDefinitionEnum", "getTypes"})
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     */
    private $label;

    /**
     * @ORM\Column(length=7, nullable=true)
     */
    private $color;

    public function __construct(string $type = null, string $code = null, string $label = null, string $color = null)
    {
        $this->type = $type;
        $this->code = $code;
        $this->label = $label;
        $this->color = $color;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function __toString(): string
    {
        return $this->label ?? '';
    }
}
