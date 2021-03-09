<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ReferentTag;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\VotingPlatform\Designation\CreatePartialDesignationCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\DesignationRepository")
 */
class Designation
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    public const DENOMINATION_DESIGNATION = 'désignation';
    public const DENOMINATION_ELECTION = 'élection';

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $type;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $zones;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     */
    private $referentTags;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $candidacyStartDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    private $candidacyEndDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    private $voteStartDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    private $voteEndDate;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(0)
     */
    private $resultDisplayDelay = 14;

    /**
     * Duration of the additional round in day
     *
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     */
    private $additionalRoundDuration = 5;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(0)
     */
    private $lockPeriodThreshold = 3;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $limited = false;

    /**
     * @var string
     *
     * @ORM\Column(options={"default": self::DENOMINATION_DESIGNATION})
     */
    private $denomination = self::DENOMINATION_DESIGNATION;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $pools;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct(string $label = null, UuidInterface $uuid = null)
    {
        $this->label = $label;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->referentTags = new ArrayCollection();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getZones(): ?array
    {
        return $this->zones;
    }

    public function setZones(?array $zones): void
    {
        $this->zones = $zones;
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags->toArray();
    }

    public function addReferentTag(ReferentTag $tag): void
    {
        if (!$this->referentTags->contains($tag)) {
            $this->referentTags->add($tag);
        }
    }

    public function removeReferentTag(ReferentTag $tag): void
    {
        $this->referentTags->removeElement($tag);
    }

    public function setReferentTags(array $referentTags): void
    {
        $this->referentTags->clear();

        foreach ($referentTags as $tag) {
            $this->addReferentTag($tag);
        }
    }

    public function getCandidacyStartDate(): ?\DateTime
    {
        return $this->candidacyStartDate;
    }

    public function setCandidacyStartDate(?\DateTime $candidacyStartDate): void
    {
        $this->candidacyStartDate = $candidacyStartDate;
    }

    public function getCandidacyEndDate(): ?\DateTime
    {
        return $this->candidacyEndDate;
    }

    public function setCandidacyEndDate(?\DateTime $candidacyEndDate): void
    {
        $this->candidacyEndDate = $candidacyEndDate;
    }

    public function getVoteStartDate(): ?\DateTime
    {
        return $this->voteStartDate;
    }

    public function setVoteStartDate(?\DateTime $voteStartDate): void
    {
        $this->voteStartDate = $voteStartDate;
    }

    public function getVoteEndDate(): ?\DateTime
    {
        return $this->voteEndDate;
    }

    public function setVoteEndDate(?\DateTime $voteEndDate): void
    {
        $this->voteEndDate = $voteEndDate;
    }

    public function getResultDisplayDelay(): int
    {
        return $this->resultDisplayDelay;
    }

    public function setResultDisplayDelay(int $resultDisplayDelay): void
    {
        $this->resultDisplayDelay = $resultDisplayDelay;
    }

    public function getAdditionalRoundDuration(): int
    {
        return $this->additionalRoundDuration;
    }

    public function setAdditionalRoundDuration(int $additionalRoundDuration): void
    {
        $this->additionalRoundDuration = $additionalRoundDuration;
    }

    public function getDetailedType(): string
    {
        if ($this->pools) {
            $suffix = null;
            if (\in_array(ElectionPoolCodeEnum::FEMALE, $this->pools, true)) {
                $suffix = ElectionPoolCodeEnum::FEMALE;
            } elseif (\in_array(ElectionPoolCodeEnum::MALE, $this->pools, true)) {
                $suffix = ElectionPoolCodeEnum::MALE;
            }

            if ($suffix) {
                return sprintf('%s_%s', $this->type, strtolower($suffix));
            }
        }

        return $this->type;
    }

    public function getTitle(): string
    {
        return DesignationTypeEnum::TITLES[$this->getDetailedType()] ?? '';
    }

    public function getLockPeriodThreshold(): int
    {
        return $this->lockPeriodThreshold;
    }

    public function setLockPeriodThreshold(int $lockPeriodThreshold): void
    {
        $this->lockPeriodThreshold = $lockPeriodThreshold;
    }

    /**
     * @Assert\IsTrue(message="La combinaison des dates est invalide.")
     */
    public function hasValidDates(): bool
    {
        return !empty($this->candidacyStartDate)
            && (
                (!empty($this->candidacyEndDate) && !empty($this->voteStartDate) && !empty($this->voteEndDate))
                || (empty($this->candidacyEndDate) && empty($this->voteStartDate) && empty($this->voteEndDate))
            )
        ;
    }

    /**
     * @Assert\IsTrue(message="La configuration de la zone est invalide")
     */
    public function hasValidZone(): bool
    {
        // no need to have a zone for committee partial elections
        if ($this->isCommitteeType() && $this->isLimited()) {
            return true;
        }

        return
            ($this->isCommitteeType() && !empty($this->zones))
            || ($this->isCopolType() && !$this->referentTags->isEmpty())
        ;
    }

    public function isOngoing(): bool
    {
        $now = new \DateTime();

        return $this->getCandidacyStartDate() <= $now
            && (null === $this->getVoteEndDate() || $now < $this->getVoteEndDate())
        ;
    }

    public function isActive(): bool
    {
        $now = new \DateTime();

        return $this->getCandidacyStartDate() <= $now
            && (
                null === $this->getVoteEndDate()
                || $now < $this->getVoteEndDate()
                || $this->isResultPeriodActive()
            )
        ;
    }

    public function isResultPeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->getVoteEndDate()
            && $this->getVoteEndDate() <= $now
            && $now < $this->getResultEndDate()
        ;
    }

    public function getResultEndDate(): ?\DateTime
    {
        if (!$this->getVoteEndDate()) {
            return null;
        }

        return (clone $this->getVoteEndDate())->modify(sprintf('+%d days', $this->getResultDisplayDelay()));
    }

    public function markAsLimited(): void
    {
        $this->limited = true;
    }

    public function __clone()
    {
        $this->id = null;
        $this->uuid = Uuid::uuid4();
        $this->referentTags = new ArrayCollection();
    }

    public function getPoolTypes(): array
    {
        switch ($this->getType()) {
            case DesignationTypeEnum::COMMITTEE_ADHERENT:
                return ElectionPoolCodeEnum::COMMITTEE_ADHERENT;
            case DesignationTypeEnum::COPOL:
                return ElectionPoolCodeEnum::COPOL;
        }

        return [];
    }

    public function isCommitteeType(): bool
    {
        return \in_array($this->type, [DesignationTypeEnum::COMMITTEE_ADHERENT, DesignationTypeEnum::COMMITTEE_SUPERVISOR], true);
    }

    public function isCopolType(): bool
    {
        return \in_array($this->type, [DesignationTypeEnum::COPOL, DesignationTypeEnum::NATIONAL_COUNCIL], true);
    }

    public function isBinomeDesignation(): bool
    {
        return \in_array($this->type, [
            DesignationTypeEnum::COMMITTEE_SUPERVISOR,
            DesignationTypeEnum::COPOL,
        ], true);
    }

    public function isMajorityType(): bool
    {
        return \in_array($this->type, [DesignationTypeEnum::COMMITTEE_SUPERVISOR], true);
    }

    public function getDenomination(bool $withDeterminer = false): string
    {
        if ($withDeterminer) {
            if (self::DENOMINATION_ELECTION === $this->denomination) {
                return 'l\''.$this->denomination;
            }

            return 'la '.$this->denomination;
        }

        return $this->denomination;
    }

    public function setDenomination(string $denomination): void
    {
        $this->denomination = $denomination;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function getPools(): ?array
    {
        return $this->pools;
    }

    public function setPools(?array $pools): void
    {
        $this->pools = $pools;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isLimited(): bool
    {
        return $this->limited;
    }

    public static function createPartialFromCommand(CreatePartialDesignationCommand $command): self
    {
        $designation = new self();

        $designation->setType($command->getDesignationType());

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
            $designation->setDenomination(Designation::DENOMINATION_ELECTION);
        }

        if ($command->getPool()) {
            $designation->setPools([$command->getPool()]);
        }

        $designation->setCandidacyStartDate(new \DateTime());
        $designation->setCandidacyEndDate((clone $command->getVoteStartDate())->modify('-24 hours'));

        $designation->setVoteStartDate($command->getVoteStartDate());
        $designation->setVoteEndDate($command->getVoteEndDate());

        $designation->setLabel('[Partielle] '.$command->getCommittee()->getName());
        $designation->setDescription($command->getMessage());

        $designation->markAsLimited();

        return $designation;
    }
}
