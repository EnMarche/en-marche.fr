<?php

namespace App\Entity\Election;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_city_partner")
 */
class CityPartner
{
    public const CONSENSUS = 'consensus';
    public const DISSENSUS = 'dissensus';

    public const CONSENSUS_CHOICES = [
        self::CONSENSUS,
        self::DISSENSUS,
    ];

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Choice(choices=CityPartner::CONSENSUS_CHOICES)
     */
    private $consensus;

    /**
     * @var CityCard|null
     *
     * @ORM\ManyToOne(targetEntity=CityCard::class, inversedBy="partners")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $city;

    public function __construct(CityCard $city = null, string $label = null, string $consensus = null)
    {
        $this->city = $city;
        $this->label = $label;
        $this->consensus = $consensus;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getConsensus(): ?string
    {
        return $this->consensus;
    }

    public function setConsensus(?string $consensus): void
    {
        $this->consensus = $consensus;
    }

    public function getCity(): ?CityCard
    {
        return $this->city;
    }

    public function setCity(?CityCard $city): void
    {
        $this->city = $city;
    }
}
