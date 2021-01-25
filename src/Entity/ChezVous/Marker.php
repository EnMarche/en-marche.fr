<?php

namespace App\Entity\ChezVous;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="chez_vous_markers")
 */
class Marker
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $type;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point")
     *
     * @Assert\NotBlank
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point")
     *
     * @Assert\NotBlank
     */
    private $longitude;

    /**
     * @var City|null
     *
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="markers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $city;

    public function __construct(City $city = null, string $type = null, float $latitude = null, float $longitude = null)
    {
        $this->type = $type;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->city = $city;
    }

    public function __toString()
    {
        return sprintf('%s [%s, %s]', $this->type, $this->latitude, $this->longitude);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): void
    {
        $this->city = $city;
    }

    public function getCoordinates(): array
    {
        return [(float) $this->latitude, (float) $this->longitude];
    }
}
