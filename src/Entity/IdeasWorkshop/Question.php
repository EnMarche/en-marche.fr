<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EnabledInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get": {"path": "/ideas-workshop/questions/{id}"}},
 *     itemOperations={"get": {"path": "/ideas-workshop/questions/{id}"}},
 * )
 *
 * @ORM\Table(name="ideas_workshop_question")
 * @ORM\Entity
 */
class Question implements EnabledInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @SymfonySerializer\Groups({"idea_guideline_read", "idea_read", "idea_with_answers"})
     */
    private $id;

    /**
     * @var Guideline
     *
     * @ORM\ManyToOne(targetEntity="Guideline", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $guideline;

    /**
     * @ORM\Column
     *
     * @SymfonySerializer\Groups("idea_guideline_read")
     */
    private $placeholder;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\GreaterThanOrEqual(1)
     *
     * @Gedmo\SortablePosition
     *
     * @SymfonySerializer\Groups("idea_guideline_read")
     */
    private $position;

    /**
     * @ORM\Column
     *
     * @SymfonySerializer\Groups("idea_guideline_read")
     */
    private $category;

    /**
     * @ORM\Column
     *
     * @SymfonySerializer\Groups("idea_guideline_read")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     *
     * @SymfonySerializer\Groups("idea_guideline_read")
     */
    private $required;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    public function __construct(
        string $category = '',
        string $name = '',
        string $placeholder = '',
        int $position = 1,
        bool $required = false,
        bool $enabled = true
    ) {
        $this->category = $category;
        $this->name = $name;
        $this->position = $position;
        $this->placeholder = $placeholder;
        $this->enabled = $enabled;
        $this->required = $required;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGuideline(): ?Guideline
    {
        return $this->guideline;
    }

    public function setGuideline(Guideline $guideline): void
    {
        $this->guideline = $guideline;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }
}
