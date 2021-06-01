<?php

namespace App\Entity\Geo;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\GeoData;
use App\Entity\GeoPointTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait GeoTrait
{
    use GeoPointTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @SymfonySerializer\Groups({"autocomplete"})
     *
     * @ApiProperty(identifier=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     *
     * @SymfonySerializer\Groups({"department_read", "region_read", "survey_list"})
     *
     * @SymfonySerializer\Groups({"zone_read"})
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"department_read", "region_read", "survey_list"})
     *
     * @SymfonySerializer\Groups({"zone_read"})
     */
    private $name;

    /**
     * @var GeoData|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\GeoData")
     */
    private $geoData;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->code);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @SymfonySerializer\Groups({"autocomplete"})
     */
    public function getNameCode(): string
    {
        return sprintf('%s %s', $this->name, $this->code);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(bool $active = true): void
    {
        $this->active = $active;
    }

    public function getGeoData(): ?GeoData
    {
        return $this->geoData;
    }

    public function setGeoData(?GeoData $geoData): void
    {
        $this->geoData = $geoData;
    }
}
