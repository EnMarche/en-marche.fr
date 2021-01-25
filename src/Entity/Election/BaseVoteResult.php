<?php

namespace App\Entity\Election;

use App\Entity\Adherent;
use App\Entity\ElectionRound;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseVoteResult
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ElectionRound
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectionRound")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $electionRound;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $registered = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $abstentions = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $participated = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $expressed = 0;

    /**
     * @var Adherent|null
     *
     * @Gedmo\Blameable(on="create")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @var Adherent|null
     *
     * @Gedmo\Blameable(on="update")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $updatedBy;

    public function __construct(ElectionRound $electionRound)
    {
        $this->electionRound = $electionRound;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getElectionRound(): ?ElectionRound
    {
        return $this->electionRound;
    }

    public function setElectionRound(ElectionRound $electionRound): void
    {
        $this->electionRound = $electionRound;
    }

    public function getRegistered(): ?int
    {
        return $this->registered;
    }

    public function setRegistered(int $registered): void
    {
        $this->registered = $registered;
    }

    public function getAbstentions(): ?int
    {
        return $this->abstentions;
    }

    public function setAbstentions(int $abstentions): void
    {
        $this->abstentions = $abstentions;
    }

    public function getParticipated(): ?int
    {
        return $this->participated;
    }

    public function setParticipated(int $participated): void
    {
        $this->participated = $participated;
    }

    public function getExpressed(): ?int
    {
        return $this->expressed;
    }

    public function setExpressed(int $expressed): void
    {
        $this->expressed = $expressed;
    }

    public function getAbstentionsPercentage(): ?float
    {
        if (0 === $this->registered) {
            return null;
        }

        return ($this->abstentions / $this->registered) * 100;
    }

    public function getExpressedPercentage(): ?float
    {
        if (0 === $this->registered) {
            return null;
        }

        return ($this->expressed / $this->registered) * 100;
    }

    public function getParticipatedPercentage(): ?float
    {
        if (0 === $this->registered) {
            return null;
        }

        return ($this->participated / $this->registered) * 100;
    }

    public function isComplete(): bool
    {
        return $this->registered && $this->abstentions && $this->expressed && $this->participated;
    }

    public function isPartial(): bool
    {
        return $this->registered || $this->abstentions || $this->expressed || $this->participated;
    }

    public function getCreatedBy(): ?Adherent
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Adherent $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedBy(): ?Adherent
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?Adherent $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }
}
