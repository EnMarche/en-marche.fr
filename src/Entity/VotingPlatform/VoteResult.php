<?php

namespace App\Entity\VotingPlatform;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\VoteResultRepository")
 *
 * @ORM\Table(name="voting_platform_vote_result", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_vote_result", columns={"voter_key", "election_round_id"}),
 * })
 */
class VoteResult
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
     * @var string
     *
     * @ORM\Column
     */
    private $voterKey;

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

    /**
     * @var VoteChoice[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\VoteChoice", mappedBy="voteResult", cascade={"all"})
     */
    private $voteChoices;

    public function __construct(ElectionRound $electionRound, string $voterKey)
    {
        $this->electionRound = $electionRound;
        $this->voterKey = $voterKey;
        $this->votedAt = new \DateTime();

        $this->voteChoices = new ArrayCollection();
    }

    public function addVoteChoice(VoteChoice $choice): void
    {
        if (!$this->voteChoices->contains($choice)) {
            $choice->setVoteResult($this);
            $this->voteChoices->add($choice);
        }
    }

    /**
     * @return VoteChoice[]
     */
    public function getVoteChoices(): array
    {
        return $this->voteChoices->toArray();
    }

    public function getVoterKey(): string
    {
        return $this->voterKey;
    }

    public static function generateVoterKey(): string
    {
        $matches = [];
        preg_match('/([[:alnum:]]{3})([[:alnum:]]{4})([[:alnum:]]{3})/i', bin2hex(random_bytes(5)), $matches);
        array_shift($matches);

        return implode('-', $matches);
    }

    public function findVoteChoiceForCandidateGroup(CandidateGroup $candidateGroup): ?VoteChoice
    {
        foreach ($this->voteChoices as $voteChoice) {
            if ($voteChoice->getCandidateGroup() === $candidateGroup) {
                return $voteChoice;
            }
        }

        return null;
    }
}
