<?php

namespace AppBundle\Entity\Timeline;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityMediaInterface;
use AppBundle\Entity\EntityMediaTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_themes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Timeline\ThemeRepository")
 *
 * @Algolia\Index(
 *     autoIndex=false,
 *     hitsPerPage=100,
 *     attributesForFaceting={"title", "profileIds"}
 * )
 */
class Theme implements EntityMediaInterface
{
    use EntityMediaTrait;
    use Translatable;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Algolia\Attribute
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Algolia\Attribute
     */
    private $featured = false;

    /**
     * @var Measure[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Timeline\Measure", mappedBy="themes")
     */
    private $measures;

    /**
     * @Assert\Valid
     */
    private $translations;

    public function __construct(
        bool $featured = false
    ) {
        $this->featured = $featured;
        $this->measures = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString()
    {
        if ($translation = $this->getTranslation('fr')) {
            return $translation->getTitle();
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

    /**
     * @Algolia\Attribute
     */
    public function image(): ?string
    {
        return $this->media ? $this->media->getPathWithDirectory() : null;
    }

    /**
     * @Algolia\Attribute
     */
    public function measureIds(): array
    {
        return array_map(function (Measure $measure) {
            return $measure->getId();
        }, $this->measures->toArray());
    }

    /**
     * @Algolia\Attribute
     */
    public function profileIds(): array
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

    /**
     * @Algolia\Attribute
     */
    public function titles(): array
    {
        foreach (['fr', 'en'] as $locale) {
            /* @var $translation ThemeTranslation */
            if ($translation = $this->getTranslation($locale)) {
                $titles[$locale] = $translation->getTitle();
            }
        }

        return $titles ?? [];
    }

    /**
     * @Algolia\Attribute
     */
    public function slugs(): array
    {
        foreach (['fr', 'en'] as $locale) {
            /* @var $translation ThemeTranslation */
            if ($translation = $this->getTranslation($locale)) {
                $slugs[$locale] = $translation->getSlug();
            }
        }

        return $slugs ?? [];
    }

    /**
     * @Algolia\Attribute
     */
    public function descriptions(): array
    {
        foreach (['fr', 'en'] as $locale) {
            /* @var $translation ThemeTranslation */
            if ($translation = $this->getTranslation($locale)) {
                $descriptions[$locale] = $translation->getDescription();
            }
        }

        return $descriptions ?? [];
    }

    private function getTranslation(string $locale): ?ThemeTranslation
    {
        $translation = $this->translations->filter(function (ThemeTranslation $translation) use ($locale) {
            return $locale === $translation->getLocale();
        })->first();

        return $translation ?: null;
    }
}
