<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="home_blocks")
 * @ORM\Entity(repositoryClass="App\Repository\HomeBlockRepository")
 *
 * @UniqueEntity(fields={"position"})
 */
class HomeBlock
{
    const TYPE_ARTICLE = 'article';
    const TYPE_VIDEO = 'video';
    const TYPE_BANNER = 'banner';

    const HEADER_BANNER = 11;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", unique=true)
     */
    private $position;

    /**
     * @var string|null
     *
     * @ORM\Column(length=30, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=30)
     */
    private $positionName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=70)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=70)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Length(max=100)
     */
    private $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank
     * @Assert\Choice({"video", "article", "banner"})
     */
    private $type = self::TYPE_ARTICLE;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Media")
     *
     * @Assert\NotBlank
     */
    private $media;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     */
    private $link;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $displayFilter = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $displayTitles = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $displayBlock = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $videoControls = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $videoAutoplayLoop = true;

    /**
     * @var string|null
     *
     * @ORM\Column(length=70, nullable=true)
     *
     * @Assert\Length(max=70)
     */
    private $titleCta;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\Choice(strict=true, callback={"\App\Admin\Color", "all"})
     */
    private $colorCta;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\Choice(strict=true, callback={"\App\Admin\Color", "all"})
     */
    private $bgColor;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __toString()
    {
        return $this->positionName ?: '';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPositionName(): ?string
    {
        return $this->positionName;
    }

    public function setPositionName(?string $positionName): void
    {
        $this->positionName = $positionName;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function hasDisplayFilter(): bool
    {
        return $this->displayFilter;
    }

    public function setDisplayFilter(bool $displayFilter): void
    {
        $this->displayFilter = $displayFilter;
    }

    public function hasDisplayTitles(): bool
    {
        return $this->displayTitles;
    }

    public function setDisplayTitles(bool $displayTitles): void
    {
        $this->displayTitles = $displayTitles;
    }

    public function isDisplayBlock(): bool
    {
        return $this->displayBlock;
    }

    public function setDisplayBlock(bool $displayBlock): void
    {
        $this->displayBlock = $displayBlock;
    }

    public function hasVideoControls(): bool
    {
        return $this->videoControls;
    }

    public function setVideoControls(bool $videoControls): void
    {
        $this->videoControls = $videoControls;
    }

    public function hasVideoAutoplayLoop(): bool
    {
        return $this->videoAutoplayLoop;
    }

    public function setVideoAutoplayLoop(bool $videoAutoplayLoop): void
    {
        $this->videoAutoplayLoop = $videoAutoplayLoop;
    }

    public function getTitleCta(): ?string
    {
        return $this->titleCta;
    }

    public function setTitleCta(?string $titleCta): void
    {
        $this->titleCta = $titleCta;
    }

    public function getColorCta(): ?string
    {
        return $this->colorCta;
    }

    public function setColorCta(?string $colorCta): void
    {
        $this->colorCta = $colorCta;
    }

    public function getBgColor(): ?string
    {
        return $this->bgColor;
    }

    public function setBgColor(?string $bgColor): void
    {
        $this->bgColor = $bgColor;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
