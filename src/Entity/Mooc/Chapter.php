<?php

namespace App\Entity\Mooc;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="mooc_chapter",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="mooc_chapter_slug", columns="slug")}
 * )
 *
 * @UniqueEntity(fields={"slug", "mooc"})
 */
class Chapter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $title;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"}, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $published;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $publishedAt;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    private $position;

    /**
     * @var Mooc
     *
     * @ORM\ManyToOne(targetEntity="Mooc", inversedBy="chapters")
     * @Gedmo\SortableGroup
     */
    private $mooc;

    /**
     * @var BaseMoocElement[]|Collection
     *
     * @ORM\OneToMany(targetEntity="BaseMoocElement", mappedBy="chapter", cascade={"all"})
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid
     */
    private $elements;

    public function __construct(string $title = null, bool $published = false, \DateTime $publishedAt = null)
    {
        $this->title = $title;
        $this->published = $published;
        $this->publishedAt = $publishedAt;
        $this->elements = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getMooc(): ?Mooc
    {
        return $this->mooc;
    }

    public function setMooc(Mooc $mooc): void
    {
        $this->mooc = $mooc;
    }

    public function detachMooc(): void
    {
        $this->mooc = null;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return BaseMoocElement[]|Collection
     */
    public function getElements(): Collection
    {
        return $this->elements;
    }

    public function addElement(BaseMoocElement $element): void
    {
        if (!$this->elements->contains($element)) {
            $element->setChapter($this);
            $this->elements->add($element);
        }
    }

    public function removeElement(BaseMoocElement $element): void
    {
        $element->detachChapter();
        $this->elements->removeElement($element);
    }
}
