<?php

namespace App\Entity\TerritorialCouncil\ElectionPoll;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\ElectionPoll\VoteRepository")
 * @ORM\Table(name="territorial_council_election_poll_vote")
 */
class Vote
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var PollChoice
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\ElectionPoll\PollChoice", inversedBy="votes")
     */
    private $choice;

    /**
     * @var TerritorialCouncilMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $membership;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(PollChoice $choice, TerritorialCouncilMembership $membership)
    {
        $this->choice = $choice;
        $this->membership = $membership;
        $this->createdAt = new \DateTime();
    }

    public function getChoice(): PollChoice
    {
        return $this->choice;
    }
}
