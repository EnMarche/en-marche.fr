<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AssessorRoleAssociation
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var VotePlace
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VotePlace")
     */
    private $votePlace;

    public function __construct(VotePlace $votePlace)
    {
        $this->votePlace = $votePlace;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotePlace(): VotePlace
    {
        return $this->votePlace;
    }
}
