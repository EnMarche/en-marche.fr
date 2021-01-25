<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConsularDistrictRepository")
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(name="consular_district_code_unique", columns="code")}
 * )
 */
class ConsularDistrict
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
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $countries;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $cities;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $number;

    /**
     * @var
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $points;

    public function __construct(array $countries, array $cities, string $code, int $number)
    {
        $this->countries = $countries;
        $this->cities = $cities;
        $this->code = $code;
        $this->number = $number;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountries(): array
    {
        return $this->countries;
    }

    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function setCities(array $cities): void
    {
        $this->cities = $cities;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPoints(): ?array
    {
        return $this->points;
    }

    public function update(self $district): void
    {
        $this->countries = $district->getCountries();
        $this->cities = $district->getCities();
        $this->code = $district->getCode();
        $this->points = $district->getPoints();
    }

    public function clearPoints(): void
    {
        $this->points = [];
    }

    public function addPoint(float $latitude, float $longitude, string $label = null): void
    {
        $this->points[] = [
            $latitude,
            $longitude,
            $label,
        ];
    }
}
