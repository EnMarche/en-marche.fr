<?php

namespace App\Entity\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\EntityUserListDefinitionTrait;
use App\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ElectedRepresentative\ElectedRepresentativeRepository")
 *
 * @UniqueEntity(fields={"adherent"}, message="elected_representative.invalid_adherent")
 */
class ElectedRepresentative
{
    use EntityUserListDefinitionTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="50")
     *
     * @SymfonySerializer\Groups({"elected_representative_change_diff"})
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="50")
     *
     * @SymfonySerializer\Groups({"elected_representative_change_diff"})
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(length=10, nullable=true)
     *
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     *
     * @SymfonySerializer\Groups({"elected_representative_change_diff"})
     */
    private $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups({"elected_representative_change_diff"})
     */
    private $birthDate;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\Length(max="255")
     */
    private $birthPlace;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $contactEmail;

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $contactPhone;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $hasFollowedTraining = false;

    /**
     * Mailchimp unsubscribed date
     *
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailUnsubscribedAt;

    /**
     * Mailchimp unsubscribed status
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $emailUnsubscribed = false;

    /**
     * @var Adherent|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @var SocialNetworkLink[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\SocialNetworkLink",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     *
     * @Assert\Valid
     */
    private $socialNetworkLinks;

    /**
     * @var Mandate[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\Mandate",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @Assert\Valid
     */
    private $mandates;

    /**
     * PoliticalFunction[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\PoliticalFunction",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     *
     * @Assert\Valid
     */
    private $politicalFunctions;

    /**
     * @var ElectedRepresentativeLabel[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\ElectedRepresentativeLabel",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     *
     * @Assert\Valid
     */
    private $labels;

    /**
     * Sponsorship[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\Sponsorship",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     *
     * @Assert\Valid
     */
    private $sponsorships;

    public function __construct()
    {
        $this->socialNetworkLinks = new ArrayCollection();
        $this->mandates = new ArrayCollection();
        $this->politicalFunctions = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->sponsorships = new ArrayCollection();
        $this->userListDefinitions = new ArrayCollection();

        $this->initializeSponsorships();
    }

    public static function create(
        string $firstName,
        string $lastName,
        \DateTime $birthDate,
        string $gender = null,
        UuidInterface $uuid = null
    ): self {
        $electedRepresentative = new self();

        $electedRepresentative->uuid = $uuid ?: Uuid::uuid4();
        $electedRepresentative->firstName = $firstName;
        $electedRepresentative->lastName = $lastName;
        $electedRepresentative->gender = $gender;
        $electedRepresentative->birthDate = $birthDate;

        return $electedRepresentative;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender = null): void
    {
        $this->gender = $gender;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(string $birthPlace = null): void
    {
        $this->birthPlace = $birthPlace;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail = null): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getContactPhone(): ?PhoneNumber
    {
        return $this->contactPhone;
    }

    public function setContactPhone(PhoneNumber $contactPhone = null): void
    {
        $this->contactPhone = $contactPhone;
    }

    public function hasFollowedTraining(): ?bool
    {
        return $this->hasFollowedTraining;
    }

    public function setHasFollowedTraining(?bool $hasFollowedTraining = null): void
    {
        $this->hasFollowedTraining = $hasFollowedTraining;
    }

    /**
     * @SymfonySerializer\Groups({"elected_representative_change_diff"})
     */
    public function isAdherent(): bool
    {
        return $this->adherent instanceof Adherent;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent = null): void
    {
        $this->adherent = $adherent;
    }

    public function removeAdherent(): void
    {
        $this->adherent = null;
    }

    public function getSocialNetworkLinks(): Collection
    {
        return $this->socialNetworkLinks;
    }

    public function addSocialNetworkLink(SocialNetworkLink $link): void
    {
        if (!$this->socialNetworkLinks->contains($link)) {
            $link->setElectedRepresentative($this);
            $this->socialNetworkLinks->add($link);
        }
    }

    public function removeSocialNetworkLink(SocialNetworkLink $link): void
    {
        $this->socialNetworkLinks->removeElement($link);
    }

    public function getMandates(): Collection
    {
        return $this->mandates;
    }

    /**
     * @return Mandate[]|Collection
     */
    public function getCurrentMandates(): Collection
    {
        return $this->mandates->filter(function (Mandate $mandate) {
            return $mandate->isElected() && $mandate->isOnGoing() && null === $mandate->getFinishAt();
        });
    }

    /**
     * @return Mandate[]|Collection
     */
    public function getFinishedMandates(): Collection
    {
        return $this->mandates->filter(function (Mandate $mandate) {
            return $mandate->isElected() && !$mandate->isOnGoing();
        });
    }

    public function getElectedMandates(): Collection
    {
        return $this->mandates->filter(function (Mandate $mandate) {
            return $mandate->isElected();
        });
    }

    public function getNotElectedMandates(): Collection
    {
        return $this->mandates->filter(function (Mandate $mandate) {
            return !$mandate->isElected();
        });
    }

    public function addMandate(Mandate $mandate): void
    {
        if (!$this->mandates->contains($mandate)) {
            $mandate->setElectedRepresentative($this);
            $this->mandates->add($mandate);
        }
    }

    public function removeMandate(Mandate $mandate): void
    {
        $this->mandates->removeElement($mandate);
    }

    public function getPoliticalFunctions(): Collection
    {
        return $this->politicalFunctions;
    }

    /**
     * @return PoliticalFunction[]|Collection
     */
    public function getCurrentPoliticalFunctions(): Collection
    {
        return $this->politicalFunctions->filter(function (PoliticalFunction $politicalFunction) {
            return $politicalFunction->isOnGoing() && null === $politicalFunction->getFinishAt();
        });
    }

    public function addPoliticalFunction(PoliticalFunction $politicalFunction): void
    {
        if (!$this->politicalFunctions->contains($politicalFunction)) {
            $politicalFunction->setElectedRepresentative($this);
            $this->politicalFunctions->add($politicalFunction);
        }
    }

    public function removePoliticalFunction(PoliticalFunction $politicalFunction): void
    {
        $this->politicalFunctions->removeElement($politicalFunction);
    }

    public function exportIsAdherent(): string
    {
        return $this->isAdherent() ? 'oui' : 'non';
    }

    public function exportMandates(): string
    {
        return implode(', ', $this->getCurrentMandates()->toArray());
    }

    public function exportPoliticalFunctions(): string
    {
        return implode(', ', $this->getCurrentPoliticalFunctions()->toArray());
    }

    /**
     * @return ElectedRepresentativeLabel[]|Collection
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    /**
     * @return ElectedRepresentativeLabel[]|Collection
     */
    public function getCurrentLabels(): Collection
    {
        return $this->labels->filter(function (ElectedRepresentativeLabel $label) {
            return $label->isOnGoing();
        });
    }

    public function getSortedLabels(): Collection
    {
        $iterator = $this->labels->getIterator();
        $iterator->uasort(function (ElectedRepresentativeLabel $labelA, ElectedRepresentativeLabel $labelB) {
            return $labelB->isOnGoing() <=> $labelA->isOnGoing();
        });

        return new ArrayCollection($iterator->getArrayCopy());
    }

    public function getLabel(string $name): ?ElectedRepresentativeLabel
    {
        foreach ($this->labels as $label) {
            if ($name === $label->getName()) {
                return $label;
            }
        }

        return null;
    }

    public function addLabel(ElectedRepresentativeLabel $label): void
    {
        if (!$this->labels->contains($label)) {
            $label->setElectedRepresentative($this);
            $this->labels->add($label);
        }
    }

    public function removeLabel(ElectedRepresentativeLabel $label): void
    {
        $this->labels->removeElement($label);
    }

    public function getSponsorships(): Collection
    {
        if (0 === $this->sponsorships->count()) {
            $this->initializeSponsorships();
        }

        return $this->sponsorships;
    }

    public function addSponsorship(Sponsorship $sponsorship): void
    {
        if (!$this->sponsorships->contains($sponsorship)) {
            $sponsorship->setElectedRepresentative($this);
            $this->sponsorships->add($sponsorship);
        }
    }

    public function removeSponsorship(Sponsorship $sponsorship): void
    {
        $this->sponsorships->removeElement($sponsorship);
    }

    private function initializeSponsorships(): void
    {
        foreach (Sponsorship::getYears() as $year) {
            $sponsorship = new Sponsorship($year, null, $this);
            $this->sponsorships->add($sponsorship);
        }
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function getAge(): ?int
    {
        return $this->birthDate ? $this->birthDate->diff(new \DateTime())->y : null;
    }

    /**
     * @SymfonySerializer\Groups({"elected_representative_change_diff"})
     */
    public function getEmailAddress(): ?string
    {
        if ($this->adherent) {
            return $this->adherent->getEmailAddress();
        }

        if ($this->contactEmail) {
            return $this->contactEmail;
        }

        return null;
    }

    public function subscribeEmails(): void
    {
        $this->emailUnsubscribed = false;
    }

    public function unsubscribeEmails(): void
    {
        $this->emailUnsubscribed = true;
        $this->emailUnsubscribedAt = new \DateTime();
    }

    public function isEmailUnsubscribed(): bool
    {
        return $this->emailUnsubscribed;
    }

    public function __toString(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    /**
     * @return ReferentTag[]|ArrayCollection
     */
    public function getActiveReferentTags(): ArrayCollection
    {
        $activeTags = new ArrayCollection();

        foreach ($this->getCurrentMandates() as $mandate) {
            if (!$zone = $mandate->getZone()) {
                continue;
            }

            foreach ($zone->getReferentTags() as $referentTag) {
                if (!$activeTags->contains($referentTag)) {
                    $activeTags->add($referentTag);
                }
            }
        }

        return $activeTags;
    }

    public function getActiveReferentTagCodes(): array
    {
        $tags = [];

        foreach ($this->getActiveReferentTags() as $referentTag) {
            $tags[] = $referentTag->getCode();
        }

        return array_unique($tags);
    }
}
