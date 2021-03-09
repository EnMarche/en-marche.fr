<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\VotingPlatform\Designation\BaseCandidacyInvitation;
use App\Validator\TerritorialCouncil\ValidTerritorialCouncilCandidacyInvitation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="territorial_council_candidacy_invitation")
 *
 * @ValidTerritorialCouncilCandidacyInvitation(groups={"Default", "invitation_edit"})
 */
class CandidacyInvitation extends BaseCandidacyInvitation
{
    /**
     * @var Candidacy
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\Candidacy", inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $candidacy;

    /**
     * @var TerritorialCouncilMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank(groups={"Default", "invitation_edit"})
     */
    protected $membership;

    public function getMembership(): ?TerritorialCouncilMembership
    {
        return $this->membership;
    }

    public function setMembership(?TerritorialCouncilMembership $membership): void
    {
        $this->membership = $membership;
    }
}
