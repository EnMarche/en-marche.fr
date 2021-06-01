<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LegislativeDistrictZoneRepository")
 * @ORM\Table(name="legislative_district_zones", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="legislative_district_zones_area_code_unique", columns="area_code")
 * })
 * @UniqueEntity(fields="areaCode", message="legislative_district_zone.area_code.unique", groups="Admin")
 */
class LegislativeDistrictZone
{
    private const TYPE_DEPARTMENT = 'departement';
    private const TYPE_REGION = 'region';

    public const ZONE_FRANCE = 'Département';
    public const ZONE_DOM_TOM = 'Outre-Mer';
    public const ZONE_FOREIGN = 'Étranger';

    public const TYPE_CHOICES = [
        'Département (Outre-Mer inclus)' => self::TYPE_DEPARTMENT,
        'Autre région du monde' => self::TYPE_REGION,
    ];

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(length=4)
     *
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Regex(
     *     pattern="/^([0-1]\d{3}|002[A-B])$/",
     *     message="legislative_district_zone.area_code.invalid",
     *     groups="Admin"
     * )
     */
    private $areaCode;

    /**
     * @ORM\Column(length=20)
     *
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Choice(
     *     callback="getAreaTypeChoices",
     *     message="legislative_district_zone.area_type.invalid",
     *     strict=true,
     *     groups="Admin"
     * )
     */
    private $areaType = self::TYPE_DEPARTMENT;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $rank;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Length(min=2, max=100, groups="Admin")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $keywords;

    public static function createDepartmentZone(string $areaCode, string $name, array $keywords = []): self
    {
        return self::create($areaCode, self::TYPE_DEPARTMENT, $name, $keywords);
    }

    public static function createRegionZone(string $areaCode, string $name, array $keywords = []): self
    {
        return self::create($areaCode, self::TYPE_REGION, $name, $keywords);
    }

    private static function create(string $areaCode, string $areaType, string $name, array $keywords = []): self
    {
        $zone = new self();
        $zone->setAreaCode($areaCode);
        $zone->setAreaType($areaType);
        $zone->setKeywords($keywords);
        $zone->setName($name);

        return $zone;
    }

    public static function getAreaTypeChoices(): array
    {
        return array_values(self::TYPE_CHOICES);
    }

    public static function normalizeAreaCode(string $code): string
    {
        return sprintf('%04s', $code);
    }

    public function __toString(): string
    {
        if (!$this->areaCode) {
            return 'n/a';
        }

        if (self::isCorsica($this->areaCode)) {
            return sprintf('%s - %s', ltrim($this->areaCode, '0'), $this->name);
        }

        $areaCode = (int) $this->areaCode;

        return sprintf(
            '%s - %s',
            $areaCode < 10 ? substr($this->areaCode, 2) : ltrim($this->areaCode, '0'),
            $this->name
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAreaType(string $type): void
    {
        if (!\in_array($type = strtolower($type), $types = [self::TYPE_DEPARTMENT, self::TYPE_REGION])) {
            throw new \InvalidArgumentException(sprintf('Invalid district zone type "%s". It must be one of %s.', $type, implode(', ', $types)));
        }

        $this->areaType = $type;
    }

    public function getAreaType(): ?string
    {
        return $this->areaType;
    }

    public function getAreaCode(): ?string
    {
        return $this->areaCode;
    }

    public function setAreaCode(string $code): void
    {
        $this->areaCode = $code = self::normalizeAreaCode($code);

        if (null === $this->rank) {
            $this->rank = $this->guessRankFromAreaCode($code);
        }
    }

    private function guessRankFromAreaCode(string $areaCode): int
    {
        // Corsica is split into 2 smaller department chunks (2A and 2B).
        // Instead of having only rank 20 for Corsica, we have two:
        //
        // Rank 20 stands for South Corsica.
        // Rank 21 stands for North Corsica.
        if (self::isCorsica($areaCode)) {
            return 'A' === substr($areaCode, -1) ? 20 : 21;
        }

        $rank = (int) ltrim($areaCode, '0');
        if ($rank < 20 || $rank > 95) {
            return $rank;
        }

        // Due to the Corsica exception, every department after Corsica has a
        // position that doesn't exactly match its department number. This why
        // there is a delta.
        //
        // Rank 20 stands for South Corsica department (20).
        // Rank 21 stands for North Corsica department (20).
        // Rank 22 stands for Côte d'Or department (21).
        // etc.
        return $rank + 1;
    }

    private static function isCorsica(string $areaCode): bool
    {
        $char = substr($areaCode, -1);

        return 'A' === $char || 'B' === $char;
    }

    public function getZoneNumber(): string
    {
        return ltrim($this->areaCode, '0');
    }

    final public function getAreaTypeLabel(): string
    {
        if (self::isCorsica($this->areaCode)) {
            return self::ZONE_FRANCE;
        }

        $areaCode = (int) $this->areaCode;

        if ($areaCode <= 95) {
            return self::ZONE_FRANCE;
        }

        if ($areaCode >= 971 && $areaCode <= 989) {
            return self::ZONE_DOM_TOM;
        }

        if ($areaCode >= 1000) {
            return self::ZONE_FOREIGN;
        }

        throw new \RuntimeException(sprintf('Unexpected code "%s" for zone "%s"', $areaCode, $this->name));
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->addKeyword($name);
    }

    public function addKeyword(string $keyword): void
    {
        $keywords = $this->getKeywords();
        $keywords[] = $keyword;

        if ($keyword !== $lowercase = mb_strtolower($keyword, 'UTF-8')) {
            $keywords[] = $lowercase;
        }

        $this->setKeywords($keywords);
    }

    public function removeKeyword(string $keyword): void
    {
        $keywords = $this->getKeywords();

        if (false !== $key = array_search($keyword, $keywords, true)) {
            unset($keywords[$key]);
        }

        if (false !== $key = array_search(mb_strtolower($keyword, 'UTF-8'), $keywords, true)) {
            unset($keywords[$key]);
        }

        $this->setKeywords($keywords);
    }

    /**
     * @Assert\Count(min=1, groups="Admin")
     */
    public function getKeywords(): array
    {
        if (empty($this->keywords)) {
            return [];
        }

        return explode("\n", $this->keywords);
    }

    public function setKeywords(array $keywords): void
    {
        $this->keywords = implode("\n", array_unique($keywords));
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }
}
