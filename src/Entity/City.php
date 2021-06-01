<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CityRepository")
 * @ORM\Table(name="cities")
 *
 * @UniqueEntity("inseeCode", message="city.insee_code.unique")
 */
class City
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
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="city.name.not_blank")
     * @Assert\Length(max="100", maxMessage="city.name.max_length")
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, unique=true)
     *
     * @Assert\NotBlank(message="city.insee_code.not_blank")
     * @Assert\Length(max="10", maxMessage="city.insee_code.max_length")
     */
    private $inseeCode;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array")
     *
     * @Assert\NotBlank(message="city.postal_code.not_blank")
     * @Assert\Count(min="1")
     */
    private $postalCodes;

    /**
     * @var Department|null
     *
     * @ORM\ManyToOne(targetEntity=Department::class, inversedBy="cities", fetch="EAGER")
     */
    private $department;

    public function __construct(
        Department $department = null,
        string $name = null,
        string $inseeCode = null,
        array $postalCodes = null
    ) {
        $this->department = $department;
        $this->name = $name;
        $this->inseeCode = $inseeCode ? self::normalizeCode($inseeCode) : null;
        $this->postalCodes = $postalCodes;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->name, $this->inseeCode);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = self::normalizeCode($inseeCode);
    }

    public function getPostalCodes(): ?array
    {
        return $this->postalCodes;
    }

    public function setPostalCodes(?array $postalCodes): void
    {
        $this->postalCodes = $postalCodes;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    public static function normalizeCode(string $inseeCode): string
    {
        return str_pad($inseeCode, 5, '0', \STR_PAD_LEFT);
    }

    public function equals(self $city): bool
    {
        return $this->id === $city->getId();
    }

    public function exportPostalCodes(): ?string
    {
        if (empty($this->postalCodes)) {
            return null;
        }

        return implode(', ', $this->postalCodes);
    }
}
