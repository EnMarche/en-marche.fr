<?php

namespace App\Entity;

use App\Validator\UniqueTurnkeyProjectPinned;
use App\Validator\WysiwygLength as AssertWysiwygLength;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This entity represents a turnkey project.
 *
 * @ORM\Table(
 *     name="turnkey_projects",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="turnkey_project_canonical_name_unique", columns="canonical_name"),
 *         @ORM\UniqueConstraint(name="turnkey_project_slug_unique", columns="slug")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TurnkeyProjectRepository")
 *
 * @UniqueTurnkeyProjectPinned
 */
class TurnkeyProject
{
    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=60)
     *
     * @JMS\SerializedName("title"),
     * @JMS\Groups({"turnkey_project_read", "turnkey_project_list"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $canonicalName;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"canonicalName"})
     *
     * @JMS\Groups({"turnkey_project_read", "turnkey_project_list"})
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=80)
     *
     * @JMS\Groups({"turnkey_project_read", "turnkey_project_list"})
     */
    private $subtitle;

    /**
     * @var CitizenProjectCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CitizenProjectCategory")
     *
     * @Assert\NotNull
     */
    private $category;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=500)
     *
     * @JMS\SerializedName("description"),
     * @JMS\Groups({"turnkey_project_read"})
     */
    private $problemDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank
     * @AssertWysiwygLength(max=800)
     *
     * @JMS\SerializedName("solution"),
     * @JMS\Groups({"turnkey_project_read", "turnkey_project_list"})
     */
    private $proposedSolution;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png"},
     *     minWidth="1200",
     *     minHeight="675",
     *     minRatio=1.77,
     * )
     */
    private $image;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     */
    private $imageName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=11, nullable=true)
     *
     * @Assert\Regex(pattern="/^[A-Za-z0-9_-]+$/", message="mooc.youtubeid_syntax")
     * @Assert\Length(min=2, max=11)
     *
     * @JMS\SerializedName("video_id"),
     * @JMS\Groups({"turnkey_project_read"})
     */
    private $youtubeId;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $isPinned = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     *
     * @JMS\Groups({"turnkey_project_read"})
     */
    private $isFavorite = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $isApproved;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", options={"default": 1})
     *
     * @Assert\NotBlank
     * @Assert\Length(min=1)
     * @Gedmo\SortablePosition
     */
    private $position = 1;

    /**
     * @var Collection|TurnkeyProjectFile[]
     *
     * @ORM\ManyToMany(targetEntity="TurnkeyProjectFile", cascade={"persist"}, orphanRemoval=true)
     *
     * @Assert\Valid
     * @Assert\Count(min=1, minMessage="turnkey_project.files.min_count")
     */
    protected $files;

    public function __construct(
        string $name = '',
        string $subtitle = '',
        CitizenProjectCategory $category = null,
        string $problemDescription = '',
        string $proposedSolution = '',
        bool $isPinned = false,
        bool $isFavorite = false,
        bool $isApproved = true,
        int $position = 1,
        string $youtubeId = null,
        string $slug = null
    ) {
        $this->setName($name);
        $this->slug = $slug;
        $this->subtitle = $subtitle;
        $this->category = $category;
        $this->problemDescription = $problemDescription;
        $this->proposedSolution = $proposedSolution;
        $this->isPinned = $isPinned;
        $this->isFavorite = $isFavorite;
        $this->isApproved = $isApproved;
        $this->position = $position;
        $this->youtubeId = $youtubeId;
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->canonicalName = static::canonicalize($name);
    }

    public static function canonicalize(string $name): string
    {
        return mb_strtolower($name);
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setCategory(CitizenProjectCategory $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): ?CitizenProjectCategory
    {
        return $this->category;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("category")
     * @JMS\Groups({"turnkey_project_read", "turnkey_project_list"})
     */
    public function getCategoryName(): string
    {
        return $this->category->getName();
    }

    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setProblemDescription(?string $problemDescription): void
    {
        $this->problemDescription = $problemDescription;
    }

    public function getProblemDescription(): string
    {
        return $this->problemDescription;
    }

    public function setProposedSolution(?string $proposedSolution): void
    {
        $this->proposedSolution = $proposedSolution;
    }

    public function getProposedSolution(): string
    {
        return $this->proposedSolution;
    }

    public function getImagePath(): string
    {
        return sprintf('images/turnkey_projects/%s', $this->getImageName());
    }

    public function getAssetImagePath(): string
    {
        return sprintf('%s/%s', 'assets', $this->getImagePath());
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function setImage(?UploadedFile $image): void
    {
        $this->image = $image;
    }

    public function setImageName(?UploadedFile $image): void
    {
        $this->imageName = null === $image ? null :
            sprintf('%s.%s',
                md5(sprintf('%s@%s', $this->getId(), $image->getClientOriginalName())),
                $image->getClientOriginalExtension()
            )
        ;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): void
    {
        $this->youtubeId = $youtubeId;
    }

    public function isPinned(): bool
    {
        return $this->isPinned;
    }

    public function setIsPinned(bool $isPinned): void
    {
        $this->isPinned = $isPinned;
    }

    public function isFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): void
    {
        $this->isFavorite = $isFavorite;
    }

    public function isApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): void
    {
        $this->isApproved = $isApproved;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return Collection|TurnkeyProjectFile[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(TurnkeyProjectFile $file): void
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
        }
    }

    public function removeFile(TurnkeyProjectFile $file): void
    {
        $this->files->removeElement($file);
    }

    public function update(
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        string $problemDescription,
        string $proposedSolution,
        bool $isPinned,
        bool $isFavorite,
        bool $isApproved,
        int $position,
        ?UploadedFile $image
    ): void {
        $this->setName($name);
        $this->setSubtitle($subtitle);
        $this->setCategory($category);
        $this->setProblemDescription($problemDescription);
        $this->setProposedSolution($proposedSolution);
        $this->setIsPinned($isPinned);
        $this->setIsFavorite($isFavorite);
        $this->setIsApproved($isApproved);
        $this->setPosition($position);

        if ($image) {
            $this->setImage($image);
        }
    }

    public function __toString()
    {
        return $this->name ?: '';
    }
}
