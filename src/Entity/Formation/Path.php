<?php

namespace App\Entity\Formation;

use App\Entity\PositionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Formation\PathRepository")
 * @ORM\Table(name="formation_paths")
 *
 * @UniqueEntity(fields={"title"}, message="path.title.unique_entity")
 */
class Path
{
    use PositionTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="path.title.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=150,
     *     minMessage="path.title.min_length",
     *     minMessage="path.title.max_length"
     * )
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true)
     *
     * @Gedmo\Slug(fields={"title"})
     */
    protected $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="path.description.not_blank")
     * @Assert\Length(min=2, minMessage="path.description.min_length")
     */
    private $description;

    /**
     * @var Axe[]|null
     *
     * @ORM\OneToMany(targetEntity="Axe", mappedBy="path")
     * @ORM\OrderBy({"id": "ASC"})
     */
    private $axes;

    public function __toString()
    {
        return (string) $this->title;
    }

    public function __construct()
    {
        $this->axes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Collection|Axe[]
     */
    public function getAxes(): Collection
    {
        return $this->axes;
    }
}
