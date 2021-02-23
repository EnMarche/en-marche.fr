<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityTimestampableTrait;
use App\Entity\FollowedInterface;
use App\Entity\FollowerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ApiResource(
 *     itemOperations={
 *         "follow": {
 *             "method": "POST",
 *             "path": "/v3/causes/{id}/follow",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "controller": "App\Controller\Api\Coalition\FollowController::follow",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "unfollow": {
 *             "method": "DELETE",
 *             "path": "/v3/causes/{id}/unfollow",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "controller": "App\Controller\Api\Coalition\FollowController::unfollow",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *     },
 * )
 *
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="cause_follower_unique", columns={"cause_id", "adherent_id"})
 * })
 *
 * @UniqueEntity(
 *     fields={"cause", "adherent"},
 *     errorPath="adherent"
 * )
 */
class CauseFollower implements FollowerInterface
{
    use EntityTimestampableTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Cause
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Cause", inversedBy="followers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $cause;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @ORM\Column(length=50)
     */
    private $firstName;

    public function __construct(Cause $cause, Adherent $adherent)
    {
        $this->cause = $cause;
        $this->adherent = $adherent;
        $this->firstName = $adherent->getFirstName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollowed(): FollowedInterface
    {
        return $this->cause;
    }

    public function getCause(): ?Cause
    {
        return $this->cause;
    }

    public function setCause(Cause $cause): void
    {
        $this->cause = $cause;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getFirstName(): string
    {
        return (string) $this->firstName;
    }
}
