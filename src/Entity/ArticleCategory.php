<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="articles_categories")
 * @ORM\Entity(repositoryClass="App\Repository\ArticleCategoryRepository")
 */
class ArticleCategory
{
    public const DEFAULT_CATEGORY = 'tout';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=50)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(checkDNS="ANY")
     */
    private $ctaLink;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Length(max="100")
     */
    private $ctaLabel;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $display;

    public function __construct(
        string $name = '',
        string $slug = '',
        int $position = 1,
        string $ctaLink = null,
        string $ctaLabel = null,
        bool $display = true
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->position = $position;
        $this->ctaLink = $ctaLink;
        $this->ctaLabel = $ctaLabel;
        $this->display = $display;
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCtaLink(): ?string
    {
        return $this->ctaLink;
    }

    public function setCtaLink(?string $ctaLink): self
    {
        $this->ctaLink = $ctaLink;

        return $this;
    }

    public function getCtaLabel(): ?string
    {
        return $this->ctaLabel;
    }

    public function setCtaLabel(?string $ctaLabel): self
    {
        $this->ctaLabel = $ctaLabel;

        return $this;
    }

    public static function isDefault(string $category): bool
    {
        return self::DEFAULT_CATEGORY === $category;
    }

    public function isDisplay(): bool
    {
        return $this->display;
    }

    public function setDisplay(bool $display): void
    {
        $this->display = $display;
    }
}
