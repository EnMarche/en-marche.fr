<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ImageTrait;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseCandidacy implements CandidacyInterface, AlgoliaIndexedEntityInterface
{
    use EntityTimestampableTrait;
    use ImageTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $uuid;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $status = CandidacyInterface::STATUS_DRAFT;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $faithStatement;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $isPublicFaithStatement = false;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png"}
     * )
     */
    protected $image;

    private $removeImage = false;

    /**
     * @var CandidacyInvitationInterface[]|Collection
     */
    protected $invitations;

    /**
     * @var CandidacyInterface|null
     */
    protected $binome;

    /**
     * Helps to render two or single candidate
     *
     * @var bool
     */
    private $taken = false;

    public function __construct(string $gender = null, UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->gender = $gender;

        $this->invitations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getCivility(): string
    {
        return $this->isFemale() ? 'Mme.' : 'M.';
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): void
    {
        $this->biography = $biography;
    }

    public function getImagePath(): string
    {
        return $this->getImageName() ?
            sprintf('images/candidacies/profile/%s', $this->getImageName())
            : ''
        ;
    }

    public function isRemoveImage(): bool
    {
        return $this->removeImage;
    }

    public function setRemoveImage(bool $value): void
    {
        $this->removeImage = $value;
    }

    public function getFirstName(): string
    {
        return $this->getAdherent()->getFirstName();
    }

    public function getLastName(): string
    {
        return $this->getAdherent()->getLastName();
    }

    public function getQuality(): ?string
    {
        return null;
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isDraft(): bool
    {
        return CandidacyInterface::STATUS_DRAFT === $this->status;
    }

    public function isConfirmed(): bool
    {
        return CandidacyInterface::STATUS_CONFIRMED === $this->status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function confirm(): void
    {
        $this->status = CandidacyInterface::STATUS_CONFIRMED;
    }

    public function getFaithStatement(): ?string
    {
        return $this->faithStatement;
    }

    public function setFaithStatement(?string $faithStatement): void
    {
        $this->faithStatement = $faithStatement;
    }

    public function isPublicFaithStatement(): bool
    {
        return $this->isPublicFaithStatement;
    }

    public function setIsPublicFaithStatement(bool $isPublicFaithStatement): void
    {
        $this->isPublicFaithStatement = $isPublicFaithStatement;
    }

    public function hasInvitation(): bool
    {
        return !$this->invitations->isEmpty();
    }

    public function hasPendingInvitation(): bool
    {
        foreach ($this->invitations as $invitation) {
            if ($invitation->isPending()) {
                return true;
            }
        }

        return false;
    }

    public function getFirstInvitation(): ?CandidacyInvitationInterface
    {
        return !$this->invitations->isEmpty() ? $this->invitations->first() : null;
    }

    /**
     * @return CandidacyInvitationInterface[]
     */
    public function getInvitations(): array
    {
        return $this->invitations->toArray();
    }

    public function addInvitation(CandidacyInvitationInterface $invitation): void
    {
        if (!$this->invitations->contains($invitation)) {
            $invitation->setCandidacy($this);
            $this->invitations->add($invitation);
        }
    }

    public function removeInvitation(CandidacyInvitationInterface $invitation): void
    {
        $this->invitations->removeElement($invitation);
    }

    public function getBinome(): ?CandidacyInterface
    {
        return $this->binome;
    }

    public function setBinome(?CandidacyInterface $binome): void
    {
        $this->binome = $binome;
    }

    public function isOngoing(): bool
    {
        return $this->getElection()->isOngoing();
    }

    public function updateFromBinome(): void
    {
        if ($this->binome) {
            $this->faithStatement = $this->binome->getFaithStatement();
            $this->isPublicFaithStatement = $this->binome->isPublicFaithStatement();
        }
    }

    public function isTaken(): bool
    {
        return $this->taken;
    }

    public function take(): void
    {
        $this->taken = true;
    }
}
