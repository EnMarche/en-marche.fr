<?php

namespace App\Entity\Geo;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_client_items_per_page": true,
 *         "order": {"name": "ASC"},
 *         "normalization_context": {
 *             "groups": {"zone_read"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/zones",
 *         },
 *     },
 *     itemOperations={},
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "word_start",
 *     "type": "exact"
 * })
 *
 * @ORM\Entity(repositoryClass="App\Repository\Geo\ZoneRepository")
 * @ORM\Table(
 *     name="geo_zone",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="geo_zone_code_type_unique", columns={"code", "type"})
 *     },
 *     indexes={
 *         @ORM\Index(name="geo_zone_type_idx", columns="type")
 *     }
 * )
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="code", column=@ORM\Column(unique=false))
 * })
 */
class Zone implements GeoInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    public const CUSTOM = 'custom';
    public const COUNTRY = 'country';
    public const REGION = 'region';
    public const DEPARTMENT = 'department';
    public const DISTRICT = 'district';
    public const CITY = 'city';
    public const BOROUGH = 'borough';
    public const CITY_COMMUNITY = 'city_community';
    public const CANTON = 'canton';
    public const FOREIGN_DISTRICT = 'foreign_district';
    public const CONSULAR_DISTRICT = 'consular_district';

    public const CANDIDATE_TYPES = [
        self::CANTON,
        self::DEPARTMENT,
        self::REGION,
    ];

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", nullable=true)
     *
     * @SymfonySerializer\Groups({"zone_read"})
     *
     * @ApiProperty(
     *     identifier=true,
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "format": "uuid",
     *             "example": "b4219d47-3138-5efd-9762-2ef9f9495084"
     *         }
     *     }
     * )
     */
    protected $uuid;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"zone_read"})
     */
    private $type;

    /**
     * @var ?string
     *
     * @ORM\Column(length=6, nullable=true)
     */
    private $teamCode;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", inversedBy="children")
     * @ORM\JoinTable(
     *     name="geo_zone_parent",
     *     joinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")}
     * )
     */
    private $parents;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", mappedBy="parents")
     */
    private $children;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @SymfonySerializer\Groups({"zone_read"})
     */
    private $postalCode;

    public function __construct(string $type, string $code, string $name, UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->type = $type;
        $this->code = $code;
        $this->name = $name;
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTeamCode(): ?string
    {
        return $this->teamCode;
    }

    public function setTeamCode(?string $teamCode): void
    {
        $this->teamCode = $teamCode;
    }

    public function isCountry(): bool
    {
        return Zone::COUNTRY === $this->type;
    }

    public function isCity(): bool
    {
        return Zone::CITY === $this->type;
    }

    public function isBorough(): bool
    {
        return Zone::BOROUGH === $this->type;
    }

    public function isRegion(): bool
    {
        return Zone::REGION === $this->type;
    }

    public function isCityGrouper(): bool
    {
        return \in_array($this->type, [
            self::CANTON,
            self::DISTRICT,
        ], true);
    }

    /**
     * @return self[]
     */
    public function getParents(): array
    {
        return $this->parents->toArray();
    }

    /**
     * @return self[]
     */
    public function getParentsOfType(string $type): array
    {
        return array_filter($this->parents->toArray(), function (Zone $zone) use ($type) {
            return $type === $zone->getType();
        });
    }

    public function hasChild(Zone $child): bool
    {
        return $this->children->filter(function (Zone $zone) use ($child) {
            return $zone->getId() === $child->getId();
        })->count() > 0;
    }

    public function hasParent(Zone $parent): bool
    {
        return $this->parents->filter(function (Zone $zone) use ($parent) {
            return $zone->getId() === $parent->getId();
        })->count() > 0;
    }

    public function addParent(self $zone): void
    {
        $this->parents->contains($zone) || $this->parents->add($zone);
    }

    public function clearParents(): void
    {
        $this->parents->clear();
    }

    /**
     * @return self[]
     */
    public function getChildren(): array
    {
        return $this->children->toArray();
    }

    public function isInFrance(): bool
    {
        return
            !\in_array($this->type, [self::COUNTRY, self::FOREIGN_DISTRICT, self::CONSULAR_DISTRICT]) ||
            (self::COUNTRY === $this->type && 'FR' === $this->code)
        ;
    }

    /**
     * @return self[]
     */
    public function getWithParents(array $types = []): array
    {
        $parents = $this->parents->toArray();

        return array_merge(
            [$this],
            empty($types)
                ? $parents
                : array_filter($parents, function (Zone $zone) use ($types) {
                    return \in_array($zone->getType(), $types);
                })
        );
    }

    /**
     * @return string[]
     */
    public function getPostalCode(): ?array
    {
        return $this->postalCode;
    }

    public function getPostalCodeAsString(): string
    {
        return implode(', ', $this->getPostalCode());
    }

    /**
     * @param string[] $postalCode
     */
    public function setPostalCode(?array $postalCode): void
    {
        $this->postalCode = $postalCode;
    }
}
