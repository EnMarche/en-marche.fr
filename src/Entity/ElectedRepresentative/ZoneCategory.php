<?php

namespace App\Entity\ElectedRepresentative;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="elected_representative_zone_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="elected_representative_zone_category_name_unique", columns={"name"})
 *     })
 * )
 */
class ZoneCategory
{
    public const CITY = 'Ville';
    public const EPCI = 'EPCI';
    public const DEPARTMENT = 'Département';
    public const REGION = 'Région';
    public const DISTRICT = 'Circonscription';
    public const CORSICA = 'Corse';
    public const FOF = 'FDE';
    public const CONSULAR_DISTRICT = 'Consular District';
    public const BOROUGH = 'Arrondissement';

    public const ALL = [
        self::CITY,
        self::EPCI,
        self::DEPARTMENT,
        self::REGION,
        self::DISTRICT,
        self::CORSICA,
        self::FOF,
        self::BOROUGH,
    ];

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
