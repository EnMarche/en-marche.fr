<?php

namespace App\Entity\TerritorialCouncil;

use App\TerritorialCouncil\Exception\TerritorialCouncilQualityException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="territorial_council_quality")
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"territorialCouncilMembership", "name"})
 */
class TerritorialCouncilQuality
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var TerritorialCouncilMembership|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership", inversedBy="qualities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $territorialCouncilMembership;

    /**
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Serializer\Groups({"api_candidacy_read"})
     */
    private $name;

    /**
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $zone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotNull
     */
    private $joinedAt;

    public function __construct(string $name, string $zone, \DateTime $joinedAt = null)
    {
        $this->setName($name);
        $this->zone = $zone;
        $this->joinedAt = $joinedAt ?? new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTerritorialCouncilMembership(): TerritorialCouncilMembership
    {
        return $this->territorialCouncilMembership;
    }

    public function setTerritorialCouncilMembership(TerritorialCouncilMembership $territorialCouncilMembership): void
    {
        $this->territorialCouncilMembership = $territorialCouncilMembership;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        self::checkQuality($name);
        $this->name = $name;
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function setZone(string $zone): void
    {
        $this->zone = $zone;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    private static function checkQuality(string $quality): void
    {
        if (!TerritorialCouncilQualityEnum::isValid($quality)) {
            throw new TerritorialCouncilQualityException(sprintf('Invalid quality for TerritorialCouncil: "%s" given', $quality));
        }
    }
}
