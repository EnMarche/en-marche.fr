<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Epci
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $surface;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $departmentCode;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $departmentName;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $regionCode;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $regionName;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $cityInsee;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $cityCode;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $cityName;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $cityFullName;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $cityDep;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $citySiren;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $codeArr;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $codeCant;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true}, nullable=true)
     */
    private $population;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $epciDep;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $epciSiren;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $insee;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $fiscal;

    public function __construct(
        string $status,
        float $surface,
        string $departmentCode,
        string $departmentName,
        string $regionCode,
        string $regionName,
        string $cityInsee,
        string $cityCode,
        string $cityName,
        string $cityFullName,
        string $cityDep,
        string $citySiren,
        string $codeArr,
        string $codeCant,
        ?int $population,
        string $epciDep,
        string $epciSiren,
        string $name,
        string $insee,
        string $fiscal
    ) {
        $this->status = $status;
        $this->surface = $surface;
        $this->departmentCode = $departmentCode;
        $this->departmentName = $departmentName;
        $this->regionCode = $regionCode;
        $this->regionName = $regionName;
        $this->cityInsee = $cityInsee;
        $this->cityCode = $cityCode;
        $this->cityName = $cityName;
        $this->cityFullName = $cityFullName;
        $this->cityDep = $cityDep;
        $this->citySiren = $citySiren;
        $this->codeArr = $codeArr;
        $this->codeCant = $codeCant;
        $this->population = $population;
        $this->epciDep = $epciDep;
        $this->epciSiren = $epciSiren;
        $this->name = $name;
        $this->insee = $insee;
        $this->fiscal = $fiscal;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSurface(): float
    {
        return $this->surface;
    }

    public function setSurface(float $surface): void
    {
        $this->surface = $surface;
    }

    public function getDepartmentCode(): string
    {
        return $this->departmentCode;
    }

    public function setDepartmentCode(string $departmentCode): void
    {
        $this->departmentCode = $departmentCode;
    }

    public function getDepartmentName(): string
    {
        return $this->departmentName;
    }

    public function setDepartmentName(string $departmentName): void
    {
        $this->departmentName = $departmentName;
    }

    public function getRegionCode(): string
    {
        return $this->regionCode;
    }

    public function setRegionCode(string $regionCode): void
    {
        $this->regionCode = $regionCode;
    }

    public function getRegionName(): string
    {
        return $this->regionName;
    }

    public function setRegionName(string $regionName): void
    {
        $this->regionName = $regionName;
    }

    public function getCityInsee(): string
    {
        return $this->cityInsee;
    }

    public function setCityInsee(string $cityInsee): void
    {
        $this->cityInsee = $cityInsee;
    }

    public function getCityCode(): string
    {
        return $this->cityCode;
    }

    public function setCityCode(string $cityCode): void
    {
        $this->cityCode = $cityCode;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getCityFullName(): string
    {
        return $this->cityFullName;
    }

    public function setCityFullName(string $cityFullName): void
    {
        $this->cityFullName = $cityFullName;
    }

    public function getCityDep(): string
    {
        return $this->cityDep;
    }

    public function setCityDep(string $cityDep): void
    {
        $this->cityDep = $cityDep;
    }

    public function getCitySiren(): string
    {
        return $this->citySiren;
    }

    public function setCitySiren(string $citySiren): void
    {
        $this->citySiren = $citySiren;
    }

    public function getCodeArr(): string
    {
        return $this->codeArr;
    }

    public function setCodeArr(string $codeArr): void
    {
        $this->codeArr = $codeArr;
    }

    public function getCodeCant(): string
    {
        return $this->codeCant;
    }

    public function setCodeCant(string $codeCant): void
    {
        $this->codeCant = $codeCant;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getEpciDep(): string
    {
        return $this->epciDep;
    }

    public function setEpciDep(string $epciDep): void
    {
        $this->epciDep = $epciDep;
    }

    public function getEpciSiren(): string
    {
        return $this->epciSiren;
    }

    public function setEpciSiren(string $epciSiren): void
    {
        $this->epciSiren = $epciSiren;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getInsee(): string
    {
        return $this->insee;
    }

    public function setInsee(string $insee): void
    {
        $this->insee = $insee;
    }

    public function getFiscal(): string
    {
        return $this->fiscal;
    }

    public function setFiscal(string $fiscal): void
    {
        $this->fiscal = $fiscal;
    }
}
