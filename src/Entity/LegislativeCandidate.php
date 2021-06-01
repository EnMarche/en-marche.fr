<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="legislative_candidates", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="legislative_candidates_slug_unique", columns="slug")
 * })
 * @ORM\Entity(repositoryClass="App\Repository\LegislativeCandidateRepository")
 *
 * @UniqueEntity(fields="slug", groups="Admin")
 */
class LegislativeCandidate implements EntityMediaInterface
{
    use EntityPersonNameTrait;
    use EntityMediaTrait;

    public const CAREERS = [
        'legislative_candidate.careers.1',
        'legislative_candidate.careers.2',
    ];

    public const STATUS_NONE = 'none';
    public const STATUS_LOST = 'lost';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_WON = 'won';

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     */
    private $position = 0;

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
     * @Gedmo\Slug(fields={"districtName"})
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
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $donationPageUrl;

    /**
     * @ORM\Column(nullable=true)
     * @Assert\Url(groups="Admin")
     */
    private $websiteUrl;

    /**
     * @ORM\Column(length=100)
     * @Assert\NotBlank(groups="Admin")
     */
    private $districtName;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank(groups="Admin")
     */
    private $districtNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $geojson;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LegislativeDistrictZone", fetch="EAGER")
     *
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Valid
     */
    private $districtZone;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Choice(callback="getCareerChoices", message="legislative_candidate.carreer.invalid", groups="Admin")
     */
    private $career;

    /**
     * @ORM\Column(length=20, options={"default": "none"})
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Choice(callback="getStatuses", groups="Admin")
     */
    private $status = self::STATUS_NONE;

    public static function getCareerChoices(): array
    {
        return self::CAREERS;
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NONE,
            self::STATUS_LOST,
            self::STATUS_QUALIFIED,
            self::STATUS_WON,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
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

    public function getDonationPageUrl(): ?string
    {
        return $this->donationPageUrl;
    }

    public function setDonationPageUrl(?string $donationPageUrl): void
    {
        $this->donationPageUrl = $donationPageUrl;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): void
    {
        $this->websiteUrl = $websiteUrl;
    }

    public function getDistrictName(): ?string
    {
        return $this->districtName;
    }

    public function setDistrictName(string $districtName): void
    {
        $this->districtName = $districtName;
    }

    public function getDistrictNumber(): ?string
    {
        return $this->districtNumber;
    }

    public function setDistrictNumber(string $districtNumber): void
    {
        $this->districtNumber = $districtNumber;
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

    public function getDistrictZone(): ?LegislativeDistrictZone
    {
        return $this->districtZone;
    }

    public function setDistrictZone(?LegislativeDistrictZone $districtZone): void
    {
        $this->districtZone = $districtZone;
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
        return $this->websiteUrl || $this->twitterPageUrl || $this->facebookPageUrl || $this->donationPageUrl;
    }

    public function getCareer(): ?string
    {
        return $this->career;
    }

    public function setCareer(string $career): void
    {
        $this->career = $career;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }
}
