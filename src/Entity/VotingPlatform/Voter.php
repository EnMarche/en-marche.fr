<?php

namespace App\Entity\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\VoterRepository")
 *
 * @ORM\Table(name="voting_platform_voter")
 */
class Voter
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Adherent|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var VotersList[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\VotingPlatform\VotersList", mappedBy="voters")
     */
    private $votersLists;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isGhost = false;

    public function __construct(Adherent $adherent = null)
    {
        $this->adherent = $adherent;
        $this->createdAt = new \DateTime();

        $this->votersLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    /**
     * @return VotersList[]
     */
    public function getVotersListsForDesignation(Designation $designation): array
    {
        return $this->votersLists
            ->filter(function (VotersList $list) use ($designation) {
                return $list->getElection()->getDesignation()->equals($designation);
            })
            ->toArray()
        ;
    }

    public function setIsGhost(bool $isGhost): void
    {
        $this->isGhost = $isGhost;
    }
}
