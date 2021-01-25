<?php

namespace App\Entity\Formation;

use App\Entity\EntityMediaInterface;
use App\Entity\EntityMediaTrait;
use App\Entity\PositionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="formation_modules")
 * @ORM\Entity(repositoryClass="App\Repository\Formation\ModuleRepository")

 *
 * @UniqueEntity(fields={"title", "axe"}, message="module.title.unique_entity")
 * @UniqueEntity(fields={"slug", "axe"}, message="module.slug.unique_entity")
 */
class Module implements EntityMediaInterface
{
    use EntityMediaTrait;
    use PositionTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank(message="Veuillez renseigner un titre.")
     * @Assert\Length(min=2, minMessage="Le titre doit faire au moins 2 caractères.")
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true)
     *
     * @Gedmo\Slug(fields={"title"})
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="Veuillez renseigner une description.")
     * @Assert\Length(min=2, minMessage="La description doit faire au moins 2 caractères.")
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="Veuillez renseigner un contenu.")
     */
    private $content;

    /**
     * @var Axe|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Formation\Axe", inversedBy="modules")
     *
     * @Assert\NotBlank(message="Veuillez renseigner un axe.")
     */
    private $axe;

    /**
     * @var Collection|File[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Formation\File",
     *     cascade={"persist", "remove"},
     *     mappedBy="module",
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id": "ASC"})
     *
     * @Assert\Valid
     */
    private $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getAxe(): ?Axe
    {
        return $this->axe;
    }

    public function setAxe(Axe $axe): void
    {
        $this->axe = $axe;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): void
    {
        if (!$this->files->contains($file)) {
            $file->setModule($this);
            $this->files->add($file);
        }
    }

    public function removeFile(File $file): void
    {
        $this->files->removeElement($file);
    }
}
