<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentCommitmentRepository")
 * @ORM\Table(name="adherent_commitment")
 */
class AdherentCommitment
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
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity=Adherent::class, inversedBy="commitment")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $commitmentActions = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $debateAndProposeIdeasActions = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $actForTerritoryActions = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $progressivismActions = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $skills = [];

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $availability;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getCommitmentActions(): array
    {
        return $this->commitmentActions;
    }

    public function setCommitmentActions(array $commitmentActions): void
    {
        $this->commitmentActions = $commitmentActions;
    }

    public function getDebateAndProposeIdeasActions(): array
    {
        return $this->debateAndProposeIdeasActions;
    }

    public function setDebateAndProposeIdeasActions(array $debateAndProposeIdeasActions): void
    {
        $this->debateAndProposeIdeasActions = $debateAndProposeIdeasActions;
    }

    public function getActForTerritoryActions(): array
    {
        return $this->actForTerritoryActions;
    }

    public function setActForTerritoryActions(array $actForTerritoryActions): void
    {
        $this->actForTerritoryActions = $actForTerritoryActions;
    }

    public function getProgressivismActions(): array
    {
        return $this->progressivismActions;
    }

    public function setProgressivismActions(array $progressivismActions): void
    {
        $this->progressivismActions = $progressivismActions;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    public function setSkills(array $skills): void
    {
        $this->skills = $skills;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    public function setAvailability(?string $availability): void
    {
        $this->availability = $availability;
    }
}
