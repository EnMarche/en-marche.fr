<?php

namespace App\Entity\VotingPlatform;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\VoteRepository")
 *
 * @ORM\Table(name="voting_platform_vote", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_vote", columns={"voter_id", "election_round_id"}),
 * })
 */
class Vote
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
     * @var Voter
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Voter", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $voter;

    /**
     * @var ElectionRound
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\ElectionRound")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $electionRound;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $votedAt;

    public function __construct(Voter $voter, ElectionRound $electionRound)
    {
        $this->voter = $voter;
        $this->electionRound = $electionRound;
        $this->votedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotedAt(): \DateTime
    {
        return $this->votedAt;
    }

    public function getVoter(): Voter
    {
        return $this->voter;
    }

    public function getElectionRound(): ElectionRound
    {
        return $this->electionRound;
    }

    public function getElection(): Election
    {
        return $this->electionRound->getElection();
    }
}
