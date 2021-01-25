<?php

namespace App\Entity\ReferentOrganizationalChart;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Referent;
use App\Validator\ValidAdherentCoReferent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository")
 *
 * @ValidAdherentCoReferent
 */
class ReferentPersonLink
{
    public const CO_REFERENT = 'co_referent';
    public const LIMITED_CO_REFERENT = 'limited_co_referent';
    public const CO_REFERENT_TYPES = [
        self::CO_REFERENT,
        self::LIMITED_CO_REFERENT,
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @Assert\Email
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $postalAddress;

    /**
     * @var PersonOrganizationalChartItem
     *
     * @ORM\ManyToOne(targetEntity="PersonOrganizationalChartItem", inversedBy="referentPersonLinks", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $personOrganizationalChartItem;

    /**
     * @var Referent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Referent", inversedBy="referentPersonLinks", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $referent;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $coReferent;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isJecouteManager = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isMunicipalManagerSupervisor = false;

    /**
     * @var Committee[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Committee")
     */
    private $restrictedCommittees;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $restrictedCities = [];

    public function __construct(PersonOrganizationalChartItem $personOrganizationalChartItem, Referent $referent)
    {
        $this->personOrganizationalChartItem = $personOrganizationalChartItem;
        $this->referent = $referent;
        $this->restrictedCommittees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPostalAddress(): ?string
    {
        return $this->postalAddress;
    }

    public function setPostalAddress(?string $postalAddress): void
    {
        $this->postalAddress = $postalAddress;
    }

    public function getReferent(): ?Referent
    {
        return $this->referent;
    }

    public function setReferent(?Referent $referent): void
    {
        $this->referent = $referent;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function detachAdherent(): void
    {
        $this->adherent = null;
    }

    public function getCoReferent(): ?string
    {
        return $this->coReferent;
    }

    public function setCoReferent(?string $coReferent = null): void
    {
        if (null !== $coReferent && !\in_array($coReferent, self::CO_REFERENT_TYPES)) {
            throw new \InvalidArgumentException(sprintf('Invalid co-referent type "%s". It must be one of %s.', $coReferent, implode(', ', self::CO_REFERENT_TYPES)));
        }

        $this->coReferent = $coReferent;
    }

    public function isCoReferent(): bool
    {
        return null !== $this->coReferent;
    }

    public function isLimitedCoReferent(): bool
    {
        return self::LIMITED_CO_REFERENT === $this->coReferent;
    }

    public function isJecouteManager(): bool
    {
        return $this->isJecouteManager;
    }

    public function setIsJecouteManager(bool $isJecouteManager): void
    {
        $this->isJecouteManager = $isJecouteManager;
    }

    public function isMunicipalManagerSupervisor(): bool
    {
        return $this->isMunicipalManagerSupervisor;
    }

    public function setIsMunicipalManagerSupervisor(bool $isMunicipalManagerSupervisor): void
    {
        $this->isMunicipalManagerSupervisor = $isMunicipalManagerSupervisor;
    }

    public function getPersonOrganizationalChartItem(): ?PersonOrganizationalChartItem
    {
        return $this->personOrganizationalChartItem;
    }

    public function setPersonOrganizationalChartItem(
        ?PersonOrganizationalChartItem $personOrganizationalChartItem
    ): void {
        $this->personOrganizationalChartItem = $personOrganizationalChartItem;
    }

    /**
     * @return Committee[]
     */
    public function getRestrictedCommittees(): array
    {
        return $this->restrictedCommittees->toArray();
    }

    public function addRestrictedCommittee(Committee $restrictedCommittee): void
    {
        if (!$this->restrictedCommittees->contains($restrictedCommittee)) {
            $this->restrictedCommittees->add($restrictedCommittee);
        }
    }

    public function removeRestrictedCommittee(Committee $restrictedCommittee): void
    {
        $this->restrictedCommittees->removeElement($restrictedCommittee);
    }

    public function setRestrictedCommittees(?array $restrictedCommittees): void
    {
        $this->restrictedCommittees = new ArrayCollection($restrictedCommittees ?? []);
    }

    public function getRestrictedCities(): ?array
    {
        return $this->restrictedCities;
    }

    public function setRestrictedCities(?array $restrictedCities): void
    {
        $this->restrictedCities = $restrictedCities;
    }

    public function emptyRestrictions(): void
    {
        $this->restrictedCommittees = new ArrayCollection();
        $this->restrictedCities = [];
    }

    public function getAdminDisplay(): string
    {
        return sprintf('(%s) %s %s', $this->personOrganizationalChartItem->getLabel(), $this->firstName, $this->lastName);
    }
}
