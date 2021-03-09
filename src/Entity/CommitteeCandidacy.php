<?php

namespace App\Entity;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeCandidacyRepository")
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 */
class CommitteeCandidacy extends BaseCandidacy
{
    /**
     * @var CommitteeElection
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeElection", inversedBy="candidacies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committeeElection;

    /**
     * @var CommitteeMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeMembership", inversedBy="committeeCandidacies")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $committeeMembership;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $type;

    /**
     * @var CommitteeCandidacyInvitation[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeCandidacyInvitation", mappedBy="candidacy", cascade={"all"})
     *
     * @Assert\Valid(groups={"invitation_edit"})
     */
    protected $invitations;

    /**
     * @var Candidacy|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\CommitteeCandidacy", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $binome;

    public function __construct(CommitteeElection $election, string $gender = null, UuidInterface $uuid = null)
    {
        parent::__construct($gender, $uuid);

        $this->type = $election->getDesignationType();

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $this->type) {
            $this->status = CandidacyInterface::STATUS_CONFIRMED;
        }

        $this->committeeElection = $election;
    }

    public function getCommitteeElection(): CommitteeElection
    {
        return $this->committeeElection;
    }

    public function setCommitteeElection(CommitteeElection $committeeElection): void
    {
        $this->committeeElection = $committeeElection;
    }

    public function getElection(): ElectionEntityInterface
    {
        return $this->committeeElection;
    }

    public function getCommitteeMembership(): ?CommitteeMembership
    {
        return $this->committeeMembership;
    }

    public function getMembership(): ?CommitteeMembership
    {
        return $this->committeeMembership;
    }

    public function setCommitteeMembership(CommitteeMembership $committeeMembership): void
    {
        $this->committeeMembership = $committeeMembership;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAdherent(): Adherent
    {
        return $this->committeeMembership->getAdherent();
    }

    /**
     * @Assert\IsTrue(groups={"accept_invitation"})
     */
    public function isValidForConfirmation(): bool
    {
        return DesignationTypeEnum::COMMITTEE_SUPERVISOR !== $this->type
            || ($this->binome && $this->binome->hasInvitation() && $this->binome->isDraft())
        ;
    }

    /**
     * @Assert\IsTrue(groups={"committee_supervisor_candidacy", "accept_invitation"}, message="Photo est obligatoire")
     */
    public function isValid(): bool
    {
        return $this->hasImageName() && !$this->isRemoveImage()
            || $this->getImage()
        ;
    }
}
