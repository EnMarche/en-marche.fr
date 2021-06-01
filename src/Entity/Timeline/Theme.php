<?php

namespace App\Entity\Timeline;

use App\Entity\AbstractTranslatableEntity;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\EntityMediaInterface;
use App\Entity\EntityMediaTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;

/**
 * @ORM\Table(name="timeline_themes")
 * @ORM\Entity(repositoryClass="App\Repository\Timeline\ThemeRepository")
 */
class Theme extends AbstractTranslatableEntity implements EntityMediaInterface, AlgoliaIndexedEntityInterface
{
    use EntityMediaTrait;
    use Translatable;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $featured;

    /**
     * @var Measure[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Timeline\Measure", mappedBy="themes")
     */
    private $measures;

    public function __construct(bool $featured = false)
    {
        $this->featured = $featured;
        $this->measures = new ArrayCollection();
    }

    public function __toString(): string
    {
        /** @var ThemeTranslation $translation */
        if ($translation = $this->translate()) {
            return (string) $translation->getTitle();
        }

        return '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): void
    {
        $this->featured = $featured;
    }

    /**
     * @return Measure[]|Collection
     */
    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function addMeasure(Measure $measure): void
    {
        if (!$this->measures->contains($measure)) {
            $this->measures->add($measure);
        }
    }

    public function removeMeasure(Measure $measure): void
    {
        $this->measures->removeElement($measure);
    }

    public function getImage(): ?string
    {
        return $this->media ? $this->media->getPathWithDirectory() : null;
    }

    public function getMeasureTitles(): array
    {
        $titles = [];
        foreach ($this->measures as $measure) {
            /** @var MeasureTranslation $translation */
            foreach ($measure->getTranslations() as $translation) {
                $titles[] = $translation->getTitle();
            }
        }

        return array_unique($titles);
    }

    public function getProfileIds(): array
    {
        $profiles = new ArrayCollection();

        foreach ($this->measures as $measure) {
            foreach ($measure->getProfiles() as $profile) {
                if (!$profiles->contains($profile)) {
                    $profiles->add($profile);
                }
            }
        }

        return array_map(function (Profile $profile) {
            return $profile->getId();
        }, $profiles->toArray());
    }

    public function getManifestoIds(): array
    {
        return array_values(array_unique(
            array_map(function (Measure $measure) {
                return $measure->getManifesto()->getId();
            }, $this->measures->toArray())
        ));
    }

    public function getTitles(): array
    {
        return $this->getFieldTranslations('title');
    }

    public function getSlugs(): array
    {
        return $this->getFieldTranslations('slug');
    }

    public function getDescriptions(): array
    {
        return $this->getFieldTranslations('description');
    }

    public function exportTitles(): string
    {
        return implode(', ', $this->getTitles());
    }

    public function exportSlugs(): string
    {
        return implode(', ', $this->getSlugs());
    }

    public function exportDescriptions(): string
    {
        return implode(', ', $this->getDescriptions());
    }

    public function getIndexOptions(): array
    {
        return [
            'hitsPerPage' => 100,
            'attributesForFaceting' => [
                'titles.fr',
                'titles.en',
                'profileIds',
                'manifestoIds',
            ],
        ];
    }
}
