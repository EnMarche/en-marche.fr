<?php

namespace App\Entity;

use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="referent", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="referent_slug_unique", columns="slug")
 * })
 * @ORM\Entity(repositoryClass="App\Repository\ReferentRepository")
 *
 * @UniqueEntity(fields="slug", groups="Admin")
 */
class Referent implements EntityMediaInterface
{
    use EntityPersonNameTrait;
    use EntityMediaTrait;

    public const ENABLED = 'ENABLED';
    public const DISABLED = 'DISABLED';

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups="Admin"
     * )
     */
    private $gender;

    /**
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Email(groups="Admin")
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups="Admin")
     */
    private $emailAddress;

    /**
     * @ORM\Column(length=100)
     * @Gedmo\Slug(fields={"firstName", "lastName"})
     *
     * @Assert\Regex(pattern="/^[a-z0-9-]+$/", message="legislative_candidate.slug.invalid", groups="Admin")
     */
    private $slug;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="legislative_candidate.facebook_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $facebookPageUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="legislative_candidate.twitter_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $twitterPageUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $geojson;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank
     */
    private $areaLabel = '';

    /**
     * @var ReferentArea[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentArea", fetch="EAGER")
     * @ORM\JoinTable(
     *     name="referent_areas",
     *     joinColumns={
     *         @ORM\JoinColumn(name="referent_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="area_id", referencedColumnName="id")
     *     }
     * )
     *
     * @Assert\Count(min=1, groups={"Admin"})
     */
    private $areas;

    /**
     * @ORM\Column(length=10, options={"default": "DISABLED"})
     */
    private $status;

    /**
     * @var Collection|ReferentPersonLink[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ReferentOrganizationalChart\ReferentPersonLink", mappedBy="referent", cascade={"persist"})
     */
    private $referentPersonLinks;

    public function __construct()
    {
        $this->areas = new ArrayCollection();
        $this->referentPersonLinks = new ArrayCollection();
        $this->status = self::ENABLED;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getProfilePicture(): ?Media
    {
        return $this->media;
    }

    public function getFacebookPageUrl(): ?string
    {
        return $this->facebookPageUrl;
    }

    public function setFacebookPageUrl(?string $facebookPageUrl): void
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function getTwitterPageUrl(): ?string
    {
        return $this->twitterPageUrl;
    }

    public function setTwitterPageUrl(?string $twitterPageUrl): void
    {
        $this->twitterPageUrl = $twitterPageUrl;
    }

    public function getGeojson(): ?string
    {
        return $this->geojson;
    }

    public function setGeojson(?string $geojson): void
    {
        $this->geojson = $geojson;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function hasWebPages(): bool
    {
        return $this->twitterPageUrl || $this->facebookPageUrl;
    }

    public function getAreaLabel()
    {
        return $this->areaLabel;
    }

    public function setAreaLabel($areaLabel): void
    {
        $this->areaLabel = $areaLabel;
    }

    public function addArea(ReferentArea $referentArea): void
    {
        if (!$this->areas->contains($referentArea)) {
            $this->areas->add($referentArea);
        }
    }

    public function removeArea(ReferentArea $referentArea): void
    {
        $this->areas->removeElement($referentArea);
    }

    public function getAreas()
    {
        return $this->areas;
    }

    public function getAreasIdAsString(): string
    {
        if ($this->areas->isEmpty()) {
            return '';
        }

        $areasIds = [];

        foreach ($this->areas as $area) {
            $areasIds[] = $area->getId();
        }

        return implode(',', $areasIds);
    }

    public function getAreasToString(): string
    {
        if ($this->areas->isEmpty()) {
            return '';
        }

        $areasIds = [];

        foreach ($this->areas as $area) {
            $areasIds[] = (string) $area;
        }

        return implode(',', $areasIds);
    }

    public function isEnabled(): bool
    {
        return self::ENABLED === $this->status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Collection|ReferentPersonLink[]
     */
    public function getReferentPersonLinks(): Collection
    {
        return $this->referentPersonLinks;
    }

    /**
     * @return Collection|ReferentPersonLink[]
     */
    public function getReferentPersonLinksWithExistingAdherent(): Collection
    {
        return $this->referentPersonLinks->filter(function (ReferentPersonLink $personLink) {
            return null !== $personLink->getAdherent();
        });
    }

    public function setReferentPersonLinks(Collection $referentPersonLinks): void
    {
        $this->referentPersonLinks = $referentPersonLinks;
    }
}
