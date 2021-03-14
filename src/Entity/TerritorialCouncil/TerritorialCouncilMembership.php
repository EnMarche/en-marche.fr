<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\UuidEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository")
 *
 * @UniqueEntity(fields={"adherent", "territorialCouncil"})
 */
class TerritorialCouncilMembership implements UuidEntityInterface
{
    use EntityIdentityTrait;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent", inversedBy="territorialCouncilMembership")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE", unique=true)
     *
     * @Groups({"api_candidacy_read"})
     */
    private $adherent;

    /**
     * @var TerritorialCouncil|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil", inversedBy="memberships", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $territorialCouncil;

    /**
     * @var Collection|TerritorialCouncilQuality[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilQuality",
     *     cascade={"all"},
     *     mappedBy="territorialCouncilMembership",
     *     orphanRemoval=true
     * )
     *
     * @Groups({"api_candidacy_read"})
     */
    private $qualities;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotNull
     */
    private $joinedAt;

    /**
     * @var Candidacy[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TerritorialCouncil\Candidacy", mappedBy="membership")
     */
    protected $candidacies;

    public function __construct(
        TerritorialCouncil $territorialCouncil = null,
        Adherent $adherent = null,
        \DateTime $joinedAt = null,
        UuidInterface $uuid = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->territorialCouncil = $territorialCouncil;
        $this->adherent = $adherent;
        $this->joinedAt = $joinedAt ?? new \DateTime('now');

        $this->qualities = new ArrayCollection();
        $this->candidacies = new ArrayCollection();
    }

    public function __clone()
    {
        $this->qualities = new ArrayCollection($this->qualities->toArray());
    }

    /**
     * @Groups({"api_candidacy_read"})
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    /**
     * @return Collection|TerritorialCouncilQuality[]
     */
    public function getQualities(): Collection
    {
        return $this->qualities;
    }

    /**
     * Add quality if it is not present yet.
     * Check of quality presence made by quality's name.
     */
    public function addQuality(TerritorialCouncilQuality $quality): void
    {
        /** @var TerritorialCouncilQuality $existingQuality */
        $existingQuality = $this->getQuality($quality->getName());
        if (!$existingQuality) {
            $quality->setTerritorialCouncilMembership($this);
            $this->qualities->add($quality);
        } elseif ($quality->getZone() !== $existingQuality->getZone()) {
            $existingQuality->setZone($quality->getZone());
        }
    }

    public function removeQuality(TerritorialCouncilQuality $quality): void
    {
        $this->qualities->removeElement($quality);
    }

    public function removeQualityWithName(string $name): void
    {
        $key = null;
        foreach ($this->getQualities() as $k => $quality) {
            if ($quality->getName() === $name) {
                $key = $k;

                break;
            }
        }

        $this->qualities->remove($key);
    }

    public function clearQualities(): void
    {
        $this->qualities->clear();
    }

    public function getQuality(string $name): ?TerritorialCouncilQuality
    {
        foreach ($this->qualities as $quality) {
            if ($quality->getName() === $name) {
                return $quality;
            }
        }

        return null;
    }

    public function hasQuality(string $name): bool
    {
        return $this->getQualities()->filter(function (TerritorialCouncilQuality $quality) use ($name) {
            return $quality->getName() === $name;
        })->count() > 0;
    }

    public function isPresident(): bool
    {
        return $this->hasQuality(TerritorialCouncilQualityEnum::REFERENT);
    }

    public function containsQualities(array $names): bool
    {
        foreach ($this->qualities as $quality) {
            if (\in_array($quality->getName(), $names, true)) {
                return true;
            }
        }

        return false;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function revoke(): void
    {
        $this->adherent = null;
    }

    public function getCandidacyForElection(Election $election): ?Candidacy
    {
        foreach ($this->candidacies as $candidacy) {
            if ($candidacy->getElection() === $election) {
                return $candidacy;
            }
        }

        return null;
    }

    public function getActiveCandidacy(): ?Candidacy
    {
        foreach ($this->candidacies as $candidacy) {
            if ($candidacy->isConfirmed() && $candidacy->isOngoing()) {
                return $candidacy;
            }
        }

        return null;
    }

    public function hasCandidacies(): bool
    {
        return !$this->candidacies->isEmpty();
    }

    public function getQualityNames(): array
    {
        return array_map(function (TerritorialCouncilQuality $quality) {
            return $quality->getName();
        }, $this->qualities->toArray());
    }

    public function getQualitiesWithZones(): array
    {
        if ($this->qualities->isEmpty()) {
            return [];
        }

        return array_merge(...array_map(function (TerritorialCouncilQuality $quality) {
            return [$quality->getName() => $quality->getZone()];
        }, $this->qualities->toArray()));
    }

    public function getQualityZonesAsString(): string
    {
        return implode(', ', \array_map(function (TerritorialCouncilQuality $quality) {
            return $quality->getZone();
        }, $this->qualities->toArray()));
    }

    public function getManagedInAdminQualityNames(): array
    {
        return array_map(function (TerritorialCouncilQuality $quality) {
            if (\in_array($quality->getName(), TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_MANAGED_IN_ADMIN_MEMBERS)) {
                return $quality->getName();
            }
        }, $this->qualities->toArray());
    }

    public function hasForbiddenForCandidacyQuality(): bool
    {
        return !empty(array_intersect(TerritorialCouncilQualityEnum::FORBIDDEN_TO_CANDIDATE, $this->getQualityNames()));
    }

    public function getAvailableForCandidacyQualityNames(): array
    {
        $qualities = $this->getQualityNames();

        $qualities = array_filter($qualities, function (string $quality) {
            return \in_array($quality, TerritorialCouncilQualityEnum::ABLE_TO_CANDIDATE, true);
        });

        if (false !== ($index = array_search(TerritorialCouncilQualityEnum::MAYOR, $qualities, true))) {
            unset($qualities[$index]);
        }

        if (
            false !== ($index = array_search(TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, $qualities, true))
            && \count($qualities) > 1
        ) {
            unset($qualities[$index]);
        }

        return $qualities;
    }

    public function getHighestQualityPriority(): int
    {
        $priorities = array_intersect_key(
            TerritorialCouncilQualityEnum::QUALITY_PRIORITIES,
            array_fill_keys($this->getQualityNames(), true)
        );

        return \count($priorities) > 0 ? min($priorities) : 1000;
    }

    public function getFullName(): string
    {
        return $this->adherent->getFullName();
    }

    public function hasConfirmedCandidacy(Election $election = null): bool
    {
        if (!$election) {
            $election = $this->getTerritorialCouncil()->getCurrentElection();
        }

        if (!$election) {
            return false;
        }

        return ($candidacy = $this->getCandidacyForElection($election)) && $candidacy->isConfirmed();
    }
}
