<?php

namespace App\Entity;

use App\Intl\FranceCitiesBundle;
use App\Utils\AreaUtils;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_requests")
 * @ORM\Entity(repositoryClass="App\Repository\ProcurationRequestRepository")
 */
class ProcurationRequest
{
    use EntityTimestampableTrait;
    use ElectionRoundsCollectionTrait;

    public const REASON_PROFESSIONAL = 'profesionnal';
    public const REASON_HANDICAP = 'handicap';
    public const REASON_HEALTH = 'health';
    public const REASON_HELP = 'help';
    public const REASON_TRAINING = 'training';
    public const REASON_HOLIDAYS = 'holidays';
    public const REASON_RESIDENCY = 'residency';

    public const STEP_VOTE = 'vote';
    public const STEP_PROFILE = 'profile';
    public const STEP_ELECTION_ROUNDS = 'election_rounds';
    public const STEP_THANKS = 'thanks';

    public const STEP_URI_VOTE = 'mon-lieu-de-vote';
    public const STEP_URI_PROFILE = 'mes-coordonnees';
    public const STEP_URI_ELECTION_ROUNDS = 'ma-procuration';
    public const STEP_URI_THANKS = 'merci';

    public const STEPS = [
        self::STEP_URI_VOTE => self::STEP_VOTE,
        self::STEP_URI_PROFILE => self::STEP_PROFILE,
        self::STEP_URI_ELECTION_ROUNDS => self::STEP_ELECTION_ROUNDS,
    ];

    public const STEP_URIS = [
        1 => self::STEP_URI_VOTE,
        2 => self::STEP_URI_PROFILE,
        3 => self::STEP_URI_ELECTION_ROUNDS,
        4 => self::STEP_URI_THANKS,
    ];

    public const ACTION_PROCESS = 'traiter';
    public const ACTION_UNPROCESS = 'detraiter';
    public const ACTIONS_URI_REGEX = self::ACTION_PROCESS.'|'.self::ACTION_UNPROCESS;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The associated found proxy.
     *
     * @var ProcurationProxy
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationProxy", inversedBy="foundRequests")
     */
    private $foundProxy;

    /**
     * The user who associated the found proxy.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(name="procuration_request_found_by_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $foundBy;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"profile"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"profile"}
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="procuration.last_name.not_blank", groups={"profile"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length",
     *     groups={"profile"}
     * )
     */
    private $lastName = '';

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(groups={"profile"})
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="procuration.first_names.min_length",
     *     maxMessage="procuration.first_names.max_length",
     *     groups={"profile"}
     * )
     */
    private $firstNames = '';

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required", groups={"profile"})
     * @Assert\Length(max=150, maxMessage="common.address.max_length", groups={"profile"})
     */
    private $address = '';

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"profile"})
     * @Assert\Expression(
     *     "(this.getCountry() == constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value != null) or (this.getCountry() != constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value == null)",
     *     message="procuration.postal_code.not_empty",
     *     groups={"profile"}
     * )
     */
    private $postalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     *
     * @Assert\Length(max=15, groups={"profile"})
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"profile"})
     */
    private $cityName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"profile"})
     * @Assert\Expression(
     *     "not (this.getCountry() == constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value != null)",
     *     message="procuration.state.not_empty",
     *     groups={"profile"}
     * )
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(groups={"profile"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"profile"})
     */
    private $country = AreaUtils::CODE_FRANCE;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @Assert\NotBlank(message="common.phone_number.required", groups={"profile"})
     * @AssertPhoneNumber(defaultRegion="FR", groups={"profile"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"profile"})
     * @Assert\Email(message="common.email.invalid", groups={"profile"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"profile"})
     */
    private $emailAddress = '';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\NotBlank(message="procuration.birthdate.not_blank", groups={"profile"})
     * @Assert\Range(
     *     min="-120 years",
     *     max="-17 years",
     *     minMessage="procuration.birthdate.maximum_required_age",
     *     maxMessage="procuration.birthdate.minimum_required_age",
     *     groups={"profile"}
     * )
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"vote"})
     * @Assert\Expression(
     *     "(this.getVoteCountry() == constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value != null) or (this.getVoteCountry() != constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value == null)",
     *     message="procuration.postal_code.not_empty",
     *     groups={"vote"}
     * )
     */
    private $votePostalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="vote_city_insee")
     *
     * @Assert\Length(max=15, groups={"vote"})
     */
    private $voteCity;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"vote"})
     */
    private $voteCityName;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(groups={"vote"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"vote"})
     */
    private $voteCountry = AreaUtils::CODE_FRANCE;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(groups={"vote"})
     * @Assert\Length(max=50, groups={"vote"})
     */
    private $voteOffice = '';

    /**
     * @var ElectionRound[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ElectionRound")
     * @ORM\JoinTable(name="procuration_requests_to_election_rounds")
     *
     * @Assert\Count(min=1, minMessage="procuration.election_rounds.min_count", groups={"election_rounds"})
     */
    private $electionRounds;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"election_rounds"})
     * @Assert\Choice(
     *     callback={"App\Entity\ProcurationRequest", "getReasons"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"election_rounds"}
     * )
     */
    private $reason = self::REASON_RESIDENCY;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $processed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedAt;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $reminded = 0;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message", groups={"elections"})
     * @AssertRecaptcha(groups={"elections"})
     */
    public $recaptcha = '';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $requestFromFrance = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public $reachable = false;

    public function __construct()
    {
        $this->phone = static::createPhoneNumber();
        $this->electionRounds = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'Demande de procuration de '.$this->lastName.' '.$this->firstNames;
    }

    public function importAdherentData(Adherent $adherent): void
    {
        $this->gender = $adherent->getGender();
        $this->firstNames = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->emailAddress = $adherent->getEmailAddress();
        $this->address = $adherent->getAddress();
        $this->postalCode = $adherent->getPostalCode();
        $this->setCity($adherent->getCity());
        $this->cityName = $adherent->getCityName();
        $this->country = $adherent->getCountry();
        $this->phone = $adherent->getPhone();
        $this->birthdate = $adherent->getBirthdate();
    }

    public function process(ProcurationProxy $procurationProxy = null, Adherent $procurationFoundBy = null): void
    {
        $this->foundBy = $procurationFoundBy;
        $this->processed = true;
        $this->processedAt = new \DateTimeImmutable();

        if ($procurationProxy) {
            $procurationProxy->process($this);
        }
    }

    public function unprocess(): void
    {
        if ($this->foundProxy instanceof ProcurationProxy) {
            $this->foundProxy->unprocess($this);
        }

        $this->foundBy = null;
        $this->processed = false;
        $this->processedAt = null;
    }

    public function generatePrivateToken(): ?string
    {
        if (!$this->processed || !$this->foundProxy) {
            return null;
        }

        $token = Uuid::uuid5(Uuid::NAMESPACE_OID, $this->processedAt->format('Y-m-d H:i:s').$this->foundProxy->getId());

        return $token->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstNames(): ?string
    {
        return $this->firstNames;
    }

    public function setFirstNames(?string $firstNames): void
    {
        $this->firstNames = $firstNames;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $cityCode): void
    {
        $this->city = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->cityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        if ($cityName) {
            $this->cityName = $cityName;
        }
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getVotePostalCode(): ?string
    {
        return $this->votePostalCode;
    }

    public function setVotePostalCode(?string $votePostalCode)
    {
        $this->votePostalCode = $votePostalCode;
    }

    public function getVoteCity(): ?string
    {
        return $this->voteCity;
    }

    public function setVoteCity(?string $cityCode): void
    {
        $this->voteCity = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->voteCityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getVoteCityName(): ?string
    {
        return $this->voteCityName;
    }

    public function setVoteCityName(?string $voteCityName): void
    {
        if ($voteCityName) {
            $this->voteCityName = $voteCityName;
        }
    }

    public function getVoteCountry(): ?string
    {
        return $this->voteCountry;
    }

    public function setVoteCountry(?string $voteCountry): void
    {
        $this->voteCountry = $voteCountry;
    }

    public function getVoteOffice(): ?string
    {
        return $this->voteOffice;
    }

    public function setVoteOffice(string $voteOffice): void
    {
        $this->voteOffice = $voteOffice;
    }

    public function getElectionRoundsCount(): int
    {
        return $this->electionRounds->count();
    }

    public function getElectionRoundLabels(): array
    {
        return array_map(function (ElectionRound $round) {
            return $round->getLabel();
        }, $this->electionRounds->toArray());
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    public function hasFoundProxy(): bool
    {
        return $this->foundProxy instanceof ProcurationProxy;
    }

    public function getFoundProxy(): ?ProcurationProxy
    {
        return $this->foundProxy;
    }

    public function setFoundProxy(?ProcurationProxy $procurationProxy): void
    {
        $this->foundProxy = $procurationProxy;
    }

    public function getFoundBy(): ?Adherent
    {
        return $this->foundBy;
    }

    public function setFoundBy(?Adherent $foundBy): void
    {
        $this->foundBy = $foundBy;
    }

    public function getReminded(): int
    {
        return $this->reminded;
    }

    public function isReminded(): bool
    {
        return $this->reminded > 0;
    }

    public function setReminded(int $reminder): void
    {
        $this->reminded = $reminder;
    }

    public function remind(): void
    {
        ++$this->reminded;
    }

    public static function getStepForUri(string $stepUri): string
    {
        if (!isset(self::STEPS[$stepUri])) {
            throw new \InvalidArgumentException(sprintf('Invalid step uri "%s". Valid step uris are: "%s".', $stepUri, implode('", "', self::STEP_URIS)));
        }

        return self::STEPS[$stepUri];
    }

    public static function getNextStepUri(string $currentStepUri): ?string
    {
        if (false === $step = array_search($currentStepUri, self::STEP_URIS, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid step "%s". Valid steps are: "%s".', $currentStepUri, implode('", "', self::STEP_URIS)));
        }

        return self::STEP_URIS[++$step] ?? null;
    }

    public static function isFinalStepUri(string $currentStepUri): bool
    {
        $stepUris = array_keys(self::STEPS);

        return $currentStepUri === end($stepUris);
    }

    public static function getReasons(): array
    {
        return [
            self::REASON_HANDICAP,
            self::REASON_HEALTH,
            self::REASON_HELP,
            self::REASON_HOLIDAYS,
            self::REASON_PROFESSIONAL,
            self::REASON_RESIDENCY,
            self::REASON_TRAINING,
        ];
    }

    private static function createPhoneNumber(int $countryCode = 33, string $number = null): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($countryCode);

        if ($number) {
            $phone->setNationalNumber($number);
        }

        return $phone;
    }

    public function isRequestFromFrance(): bool
    {
        return $this->requestFromFrance;
    }

    public function setRequestFromFrance(bool $requestFromFrance): void
    {
        $this->requestFromFrance = $requestFromFrance;
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }

    public function setReachable(bool $reachable): void
    {
        $this->reachable = $reachable;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }
}
