<?php

namespace App\Entity\Timeline;

use App\Entity\AbstractTranslatableEntity;
use App\Entity\AlgoliaIndexedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_measures")
 * @ORM\Entity(repositoryClass="App\Repository\Timeline\MeasureRepository")
 */
class Measure extends AbstractTranslatableEntity implements AlgoliaIndexedEntityInterface
{
    use Translatable;

    public const TITLE_MAX_LENGTH = 100;

    public const STATUS_UPCOMING = 'UPCOMING';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_DONE = 'DONE';
    public const STATUS_DEFERRED = 'DEFERRED';

    public const STATUSES = [
        'À venir' => self::STATUS_UPCOMING,
        'En cours' => self::STATUS_IN_PROGRESS,
        'Fait' => self::STATUS_DONE,
        'Reporté' => self::STATUS_DEFERRED,
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     */
    private $link;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices=Measure::STATUSES,
     *     strict=true
     * )
     */
    private $status;

    /**
     * @var \DateTime|null
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $major;

    /**
     * @var Profile[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Timeline\Profile")
     * @ORM\JoinTable(
     *     name="timeline_measures_profiles",
     *     joinColumns={
     *         @ORM\JoinColumn(name="measure_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *     }
     * )
     */
    private $profiles;

    /**
     * @var Theme[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Timeline\Theme", inversedBy="measures")
     * @ORM\JoinTable(
     *     name="timeline_themes_measures",
     *     joinColumns={
     *         @ORM\JoinColumn(name="measure_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="theme_id", referencedColumnName="id")
     *     }
     * )
     */
    private $themes;

    /**
     * @var Manifesto
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Timeline\Manifesto")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manifesto;

    private $savedThemes;

    /**
     * @param Profile[] $profiles
     * @param Theme[]   $themes
     */
    public function __construct(
        string $status = null,
        array $profiles = [],
        array $themes = [],
        Manifesto $manifesto = null,
        string $link = null,
        bool $isMajor = false
    ) {
        $this->status = $status;
        $this->link = $link;
        $this->major = $isMajor;
        $this->profiles = new ArrayCollection($profiles);
        $this->themes = new ArrayCollection($themes);
        $this->manifesto = $manifesto;
        $this->savedThemes = new ArrayCollection();
    }

    public function __toString(): string
    {
        /** @var MeasureTranslation $translation */
        if ($translation = $this->translate()) {
            return (string) $translation->getTitle();
        }

        return '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function update(): void
    {
        $this->updatedAt = new \DateTime('now');
    }

    public function isMajor(): bool
    {
        return $this->major;
    }

    public function setMajor(bool $major): void
    {
        $this->major = $major;
    }

    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profile $profile): void
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles->add($profile);
        }
    }

    public function removeProfile(Profile $profile): void
    {
        $this->profiles->removeElement($profile);
    }

    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): void
    {
        $savedThemes = $this->getSavedThemes();

        if ($savedThemes->contains($theme)) {
            $savedThemes->removeElement($theme);
        }

        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }
    }

    public function removeTheme(Theme $theme): void
    {
        $savedThemes = $this->getSavedThemes();

        if (!$savedThemes->contains($theme)) {
            $savedThemes->add($theme);
        }

        $this->themes->removeElement($theme);
    }

    public function getManifesto(): ?Manifesto
    {
        return $this->manifesto;
    }

    public function setManifesto(?Manifesto $manifesto): void
    {
        $this->manifesto = $manifesto;
    }

    public function isUpcoming(): bool
    {
        return self::STATUS_UPCOMING === $this->status;
    }

    public function isInProgress(): bool
    {
        return self::STATUS_IN_PROGRESS === $this->status;
    }

    public function isDone(): bool
    {
        return self::STATUS_DONE === $this->status;
    }

    public function isDeferred(): bool
    {
        return self::STATUS_DEFERRED === $this->status;
    }

    private function getSavedThemes(): Collection
    {
        if (!$this->savedThemes) {
            $this->savedThemes = new ArrayCollection();
        }

        return $this->savedThemes;
    }

    public function getThemesToIndex(): ArrayCollection
    {
        $themes = new ArrayCollection();

        foreach (array_merge($this->getSavedThemes()->toArray(), $this->themes->toArray()) as $theme) {
            if (!$themes->contains($theme)) {
                $themes->add($theme);
            }
        }

        return $themes;
    }

    public function getTitles(): array
    {
        return $this->getFieldTranslations('title');
    }

    public function exportTitles(): string
    {
        return implode(', ', $this->getTitles());
    }

    public function exportThemes(): string
    {
        return implode(', ', $this->themes->toArray());
    }

    public function exportProfiles(): string
    {
        return implode(', ', $this->profiles->toArray());
    }

    public function exportManifesto(): string
    {
        return $this->manifesto->exportTitles();
    }

    public function getIndexOptions(): array
    {
        return [];
    }
}
