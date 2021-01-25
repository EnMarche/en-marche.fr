<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="referent_managed_areas")
 * @ORM\Entity
 */
class ReferentManagedArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     * @ORM\JoinTable(
     *     name="referent_managed_areas_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="referent_managed_area_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id")
     *     }
     * )
     */
    private $tags;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLatitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLongitude;

    public function __construct(array $tags = [], string $latitude = null, string $longitude = null)
    {
        $this->markerLatitude = $latitude;
        $this->markerLongitude = $longitude;
        $this->tags = new ArrayCollection($tags);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Zone[]|Collection
     */
    public function getZones(): Collection
    {
        $zones = new ArrayCollection();
        foreach ($this->getTags() as $tag) {
            $zones->add($tag->getZone());
        }

        return $zones;
    }

    /**
     * @return ReferentTag[]|Collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ReferentTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(ReferentTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getOnlyManagedCountryCodes($value): array
    {
        return array_values(array_filter(array_map(function (ReferentTag $tag) use ($value) {
            if (ctype_alpha($tag->getCode())
                && (!$value || ($value && 0 === stripos($tag->getName(), $value)))) {
                return [$tag->getCode() => $tag->getName()];
            }
        }, $this->tags->toArray())));
    }

    public function getMarkerLatitude(): ?string
    {
        return $this->markerLatitude;
    }

    public function setMarkerLatitude(?string $markerLatitude): void
    {
        if (!$markerLatitude) {
            $markerLatitude = null;
        }

        $this->markerLatitude = $markerLatitude;
    }

    public function getMarkerLongitude(): ?string
    {
        return $this->markerLongitude;
    }

    public function setMarkerLongitude(?string $markerLongitude): void
    {
        if (!$markerLongitude) {
            $markerLongitude = null;
        }

        $this->markerLongitude = $markerLongitude;
    }

    public function getReferentTagCodes(): array
    {
        return array_map(function (ReferentTag $tag) { return $tag->getCode(); }, $this->getTags()->toArray());
    }

    public function getReferentTagCodesAsString(): string
    {
        return !empty($this->getTags()) ? implode(', ', $this->getReferentTagCodes()) : '';
    }

    public function hasFranceTag(): bool
    {
        foreach ($this->tags as $tag) {
            if (preg_match('/^\d{2}|2[A|B]|CIRCO_\d/', $tag->getCode())) {
                return true;
            }
        }

        return false;
    }
}
