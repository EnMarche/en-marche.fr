<?php

namespace App\Entity;

use App\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="municipal_chief_areas")
 * @ORM\Entity
 */
class MunicipalChiefManagedArea
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $inseeCode;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $jecouteAccess = false;

    public function hasJecouteAccess(): bool
    {
        return $this->jecouteAccess;
    }

    public function setJecouteAccess(bool $jecouteAccess): void
    {
        $this->jecouteAccess = $jecouteAccess;
    }

    public function setInseeCode(string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    /**
     * @Assert\NotNull(message="municipal_chief.invalid_city")
     */
    public function getCityName(): ?string
    {
        return FranceCitiesBundle::getCityNameFromInseeCode((string) $this->inseeCode)
            ?? FranceCitiesBundle::SPECIAL_CITY_ZONES[$this->inseeCode] ?? null;
    }

    public function getDepartmentalCode(): string
    {
        return substr($this->inseeCode, 0, 2);
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->getCityName(), $this->inseeCode);
    }
}
