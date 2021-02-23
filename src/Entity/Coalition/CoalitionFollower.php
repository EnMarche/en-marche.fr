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
 * @ORM\Entity
 * @ORM\Table
 *
 * @UniqueEntity(
 *     fields={"coalition", "adherent"},
 *     errorPath="adherent"
 * )
 */
class CoalitionFollower implements FollowerInterface
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
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="followers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $coalition;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    public function __construct(Coalition $coalition, Adherent $adherent)
    {
        $this->coalition = $coalition;
        $this->adherent = $adherent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollowed(): FollowedInterface
    {
        return $this->coalition;
    }

    public function getCoalition(): ?Coalition
    {
        return $this->coalition;
    }

    public function setCoalition(Coalition $coalition): void
    {
        $this->coalition = $coalition;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
