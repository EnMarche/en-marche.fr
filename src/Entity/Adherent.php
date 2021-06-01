<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentProfile\AdherentProfile;
use App\Collection\AdherentCharterCollection;
use App\Collection\CertificationRequestCollection;
use App\Collection\CitizenProjectMembershipCollection;
use App\Collection\CommitteeMembershipCollection;
use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentMandate\AbstractAdherentMandate;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\BoardMember\BoardMember;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CoalitionModeratorRoleAssociation;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Entity\Geo\Zone;
use App\Entity\ManagedArea\CandidateManagedArea;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\DelegatedAccessEnum;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Exception\AdherentAlreadyEnabledException;
use App\Exception\AdherentException;
use App\Exception\AdherentTokenException;
use App\Geocoder\GeoPointInterface;
use App\Membership\ActivityPositions;
use App\Membership\MembershipInterface;
use App\Membership\MembershipRequest;
use App\Membership\MembershipSourceEnum;
use App\OAuth\Model\User as InMemoryOAuthUser;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\AreaUtils;
use App\Validator\TerritorialCouncil\UniqueTerritorialCouncilMember;
use App\Validator\UniqueMembership;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use League\OAuth2\Server\Entities\UserEntityInterface;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={
 *         "get": {
 *             "normalization_context": {"groups": {"idea_vote_read"}},
 *             "method": "GET",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "summary": "Retrieves an Adherent resource by UUID.",
 *                 "description": "Retrieves an Adherent resource by UUID.",
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Adherent resource.",
 *                         "example": "b4219d47-3138-5efd-9762-2ef9f9495084",
 *                     }
 *                 }
 *             }
 *         }
 *     }
 * )
 *
 * @ORM\Table(name="adherents", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="adherents_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="adherents_email_address_unique", columns="email_address")
 * })
 * @ORM\Entity(repositoryClass="App\Repository\AdherentRepository")
 * @ORM\EntityListeners({"App\EntityListener\RevokeReferentTeamMemberRolesListener", "App\EntityListener\RevokeDelegatedAccessListener"})
 *
 * @UniqueEntity(fields={"nickname"}, groups={"anonymize"})
 * @UniqueMembership(groups={"Admin"})
 *
 * @UniqueTerritorialCouncilMember(qualities={"referent", "lre_manager", "referent_jam"})
 */
class Adherent implements UserInterface, UserEntityInterface, GeoPointInterface, EncoderAwareInterface, MembershipInterface, ReferentTaggableEntity, ZoneableEntity, \Serializable, EntityMediaInterface, EquatableInterface
{
    use EntityCrudTrait;
    use EntityIdentityTrait;
    use EntityPersonNameTrait;
    use EntityPostAddressTrait;
    use LazyCollectionTrait;
    use EntityReferentTagTrait;
    use EntityZoneTrait;

    public const ENABLED = 'ENABLED';
    public const TO_DELETE = 'TO_DELETE';
    public const DISABLED = 'DISABLED';

    /**
     * @ORM\Column(length=25, unique=true, nullable=true)
     *
     * @Assert\Length(min=3, max=25, groups={"Default", "anonymize"})
     * @Assert\Regex(pattern="/^[a-z0-9 _-]+$/i", message="adherent.nickname.invalid_syntax", groups={"anonymize"})
     * @Assert\Regex(pattern="/^[a-zÀ-ÿ0-9 .!_-]+$/i", message="adherent.nickname.invalid_extended_syntax")
     *
     * @SymfonySerializer\Groups({"user_profile", "idea_list_read", "idea_read", "idea_thread_list_read", "idea_thread_comment_read", "idea_vote_read"})
     */
    private $nickname;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $nicknameUsed;

    /**
     * @ORM\Column(nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(nullable=true)
     */
    private $oldPassword;

    /**
     * @ORM\Column(length=6, nullable=true)
     *
     * @JMS\Groups({"adherent_change_diff"})
     * @SymfonySerializer\Groups({"api_candidacy_read", "profile_read"})
     */
    private $gender;

    /**
     * @ORM\Column(length=80, nullable=true)
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $customGender;

    /**
     * @ORM\Column
     *
     * @JMS\Groups({"adherent_change_diff", "public"})
     * @JMS\SerializedName("emailAddress")
     *
     * @SymfonySerializer\Groups({"user_profile", "profile_read"})
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $phone;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @JMS\Groups({"adherent_change_diff"})
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $birthdate;

    /**
     * @ORM\Column(nullable=true)
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $position;

    /**
     * @ORM\Column(length=10, options={"default": "DISABLED"})
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registeredAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $membershipRemindedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoggedAt;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $interests = [];

    /**
     * @var SubscriptionType[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="SubscriptionType", cascade={"persist"})
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $subscriptionTypes;

    /**
     * @var District|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\District", cascade={"persist"})
     */
    private $legislativeCandidateManagedDistrict;

    /**
     * @var ReferentManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ReferentManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $managedArea;

    /**
     * Defines to which referent team the adherent belongs.
     *
     * @var ReferentTeamMember|null
     *
     * @ORM\OneToOne(targetEntity="ReferentTeamMember", mappedBy="member", cascade={"all"}, orphanRemoval=true)
     */
    private $referentTeamMember;

    /**
     * @var CoordinatorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\CoordinatorManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $coordinatorCitizenProjectArea;

    /**
     * @var CoordinatorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\CoordinatorManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $coordinatorCommitteeArea;

    /**
     * @var ProcurationManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ProcurationManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $procurationManagedArea;

    /**
     * @var AssessorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AssessorManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $assessorManagedArea;

    /**
     * @var AssessorRoleAssociation|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AssessorRoleAssociation", cascade={"all"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $assessorRole;

    /**
     * @var MunicipalManagerRoleAssociation|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\MunicipalManagerRoleAssociation", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $municipalManagerRole;

    /**
     * @var MunicipalManagerSupervisorRole|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\MunicipalManagerSupervisorRole", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $municipalManagerSupervisorRole;

    /**
     * @var CoalitionModeratorRoleAssociation|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Coalition\CoalitionModeratorRoleAssociation", cascade={"all"}, orphanRemoval=true)
     */
    private $coalitionModeratorRole;

    /**
     * @var BoardMember|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\BoardMember\BoardMember", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     */
    private $boardMember;

    /**
     * @var JecouteManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\JecouteManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $jecouteManagedArea;

    /**
     * @var TerritorialCouncilMembership|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $territorialCouncilMembership;

    /**
     * @var PoliticalCommitteeMembership|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommitteeMembership", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     */
    private $politicalCommitteeMembership;

    /**
     * @var CommitteeMembership[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CommitteeMembership", mappedBy="adherent", cascade={"remove"})
     */
    private $memberships;

    /**
     * @var CitizenProjectMembership[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CitizenProjectMembership", mappedBy="adherent", cascade={"remove"})
     */
    private $citizenProjectMemberships;

    /**
     * @var CommitteeFeedItem[]|Collection|iterable
     *
     * @ORM\OneToMany(targetEntity="CommitteeFeedItem", mappedBy="author", cascade={"remove"})
     */
    private $committeeFeedItems;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\AdherentTag")
     */
    private $tags;

    /**
     * @var District|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\District", cascade={"persist"})
     */
    private $managedDistrict;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $adherent = false;

    /**
     * @var InMemoryOAuthUser|null
     */
    private $oAuthUser;

    /**
     * @var string[]
     */
    private $roles = [];

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public $localHostEmailsSubscription = false;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    public $emailsSubscriptions;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    public $comMobile;

    /**
     * Activation token was already sent after 48h of registration
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $remindSent = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @SymfonySerializer\Groups({"user_profile"})
     */
    private $commentsCguAccepted = false;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $mandates;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IdeasWorkshop\Idea", mappedBy="author", fetch="EXTRA_LAZY")
     */
    private $ideas;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true)
     */
    private $media;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $displayMedia = true;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="legislative_candidate.facebook_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $facebookPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="legislative_candidate.twitter_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $twitterPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?linkedin.com\/#", message="legislative_candidate.linkedin_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $linkedinPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $telegramPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $job;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $activityArea;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $nationality;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $canaryTester = false;

    /**
     * Mailchimp unsubscribed status
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $emailUnsubscribed = false;

    /**
     * Mailchimp unsubscribed date
     *
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailUnsubscribedAt;

    /**
     * @var MunicipalChiefManagedArea|null
     *
     * @Assert\Valid
     * @ORM\OneToOne(targetEntity="App\Entity\MunicipalChiefManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $municipalChiefManagedArea;

    /**
     * @var SenatorialCandidateManagedArea|null
     *
     * @Assert\Valid
     * @ORM\OneToOne(targetEntity="App\Entity\SenatorialCandidateManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $senatorialCandidateManagedArea;

    /**
     * @var LreArea|null
     *
     * @ORM\OneToOne(targetEntity="LreArea", cascade={"all"}, orphanRemoval=true)
     * @Assert\Valid
     */
    private $lreArea;

    /**
     * @var CandidateManagedArea|null
     *
     * @Assert\Valid
     * @ORM\OneToOne(targetEntity="App\Entity\ManagedArea\CandidateManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $candidateManagedArea;

    /**
     * Access to external services regarding printing
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $printPrivilege = false;

    /**
     * @var Collection|AdherentCharterInterface[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AdherentCharter\AbstractAdherentCharter", mappedBy="adherent", cascade={"all"})
     */
    private $charters;

    /**
     * @var SenatorArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\SenatorArea", cascade={"all"}, orphanRemoval=true)
     */
    private $senatorArea;

    /**
     * @var ConsularManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ConsularManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $consularManagedArea;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $electionResultsReporter = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $certifiedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $source;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     *
     * @JMS\Groups({"adherent_change_diff"})
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $coalitionSubscription = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     *
     * @JMS\Groups({"adherent_change_diff"})
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $causeSubscription = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    private $coalitionsCguAccepted = false;

    /**
     * @var CertificationRequest[]|Collection
     *
     * @ORM\OneToMany(targetEntity=CertificationRequest::class, mappedBy="adherent", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"createdAt": "ASC"})
     */
    private $certificationRequests;

    /**
     * @var DelegatedAccess[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MyTeam\DelegatedAccess", mappedBy="delegated", cascade={"all"})
     */
    private $receivedDelegatedAccesses;

    /**
     * @var AdherentCommitment
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AdherentCommitment", mappedBy="adherent", cascade={"all"})
     */
    private $commitment;

    /**
     * @var ThematicCommunity[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ThematicCommunity\ThematicCommunity")
     */
    private $handledThematicCommunities;

    /**
     * @var AbstractAdherentMandate[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AdherentMandate\AbstractAdherentMandate", mappedBy="adherent", fetch="EXTRA_LAZY")
     */
    private $adherentMandates;

    /**
     * @var Cause[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Coalition\Cause", mappedBy="author", fetch="EXTRA_LAZY")
     */
    private $causes;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $notifiedForElection = false;

    /**
     * @var ProvisionalSupervisor[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProvisionalSupervisor", mappedBy="adherent", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $provisionalSupervisors;

    /**
     * @ORM\Embedded(class="App\Entity\PostAddress", columnPrefix="address_")
     *
     * @var PostAddress
     *
     * @SymfonySerializer\Groups({"profile_read"})
     */
    protected $postAddress;

    public function __construct()
    {
        $this->memberships = new ArrayCollection();
        $this->citizenProjectMemberships = new ArrayCollection();
        $this->subscriptionTypes = new ArrayCollection();
        $this->ideas = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->zones = new ArrayCollection();
        $this->charters = new AdherentCharterCollection();
        $this->certificationRequests = new ArrayCollection();
        $this->receivedDelegatedAccesses = new ArrayCollection();
        $this->handledThematicCommunities = new ArrayCollection();
        $this->adherentMandates = new ArrayCollection();
        $this->provisionalSupervisors = new ArrayCollection();
        $this->causes = new ArrayCollection();
    }

    public static function createLight(
        UuidInterface $uuid,
        string $emailAddress,
        string $firstName,
        PostAddress $postAddress,
        string $password,
        string $status = self::DISABLED,
        ?string $source = null,
        bool $coalitionSubscription = false,
        bool $causeSubscription = false
    ): self {
        $adherent = new self();

        $adherent->uuid = $uuid;
        $adherent->firstName = $firstName;
        $adherent->postAddress = $postAddress;
        $adherent->emailAddress = $emailAddress;
        $adherent->password = $password;
        $adherent->referentTags = new ArrayCollection();
        $adherent->status = $status;
        $adherent->nicknameUsed = false;
        $adherent->registeredAt = new \DateTime('now');
        $adherent->source = $source;
        $adherent->coalitionSubscription = $coalitionSubscription;
        $adherent->causeSubscription = $causeSubscription;

        return $adherent;
    }

    public static function create(
        UuidInterface $uuid,
        string $emailAddress,
        string $password,
        ?string $gender,
        string $firstName,
        string $lastName,
        ?\DateTime $birthDate,
        ?string $position,
        PostAddress $postAddress,
        PhoneNumber $phone = null,
        string $nickname = null,
        bool $nicknameUsed = false,
        string $status = self::DISABLED,
        string $registeredAt = 'now',
        ?array $tags = [],
        ?array $referentTags = [],
        array $mandates = null,
        string $nationality = null,
        string $customGender = null
    ): self {
        $adherent = new self();

        $adherent->uuid = $uuid;
        $adherent->password = $password;
        $adherent->gender = $gender;
        $adherent->firstName = $firstName;
        $adherent->lastName = $lastName;
        $adherent->nickname = $nickname;
        $adherent->nicknameUsed = $nicknameUsed;
        $adherent->emailAddress = $emailAddress;
        $adherent->birthdate = $birthDate;
        $adherent->position = $position;
        $adherent->postAddress = $postAddress;
        $adherent->phone = $phone;
        $adherent->status = $status;
        $adherent->registeredAt = new \DateTime($registeredAt);
        $adherent->tags = new ArrayCollection($tags);
        $adherent->referentTags = new ArrayCollection($referentTags);
        $adherent->mandates = $mandates;
        $adherent->nationality = $nationality;
        $adherent->customGender = $customGender;

        return $adherent;
    }

    public function getIdentifier()
    {
        return $this->getUuidAsString();
    }

    public static function createUuid(string $email): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $email);
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("uuid")
     * @JMS\Groups({"public"})
     */
    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("subscriptionExternalIds")
     * @JMS\Groups({"public"})
     */
    public function getSubscriptionExternalIds(): array
    {
        return array_values(array_filter(array_map(function (SubscriptionType $subscription) {
            return $subscription->getExternalId();
        }, $this->getSubscriptionTypes())));
    }

    /**
     * @SymfonySerializer\Groups({"user_profile"})
     */
    public function isElected(): bool
    {
        return $this->getMandates() && \count($this->getMandates()) > 0;
    }

    /**
     * @SymfonySerializer\Groups({"user_profile"})
     */
    public function isLarem(): bool
    {
        return $this->getTags()->filter(function (AdherentTag $tag) {
            return AdherentTagEnum::LAREM === $tag->getName();
        })->count() > 0;
    }

    public function getRoles()
    {
        $roles = ['ROLE_USER'];

        if ($this->isAdherent()) {
            $roles[] = 'ROLE_ADHERENT';
        }

        if ($this->isReferent()) {
            $roles[] = 'ROLE_REFERENT';
        }

        if ($this->isCoReferent()) {
            $roles[] = 'ROLE_COREFERENT';
        }

        if ($this->isDeputy()) {
            $roles[] = 'ROLE_DEPUTY';
        }

        if ($this->isSenator()) {
            $roles[] = 'ROLE_SENATOR';
        }

        if ($this->isConsular()) {
            $roles[] = 'ROLE_CONSULAR';
        }

        if ($this->hasFormationSpaceAccess()) {
            $roles[] = 'ROLE_FORMATION_SPACE';
        }

        if ($this->isCoordinator()) {
            $roles[] = 'ROLE_COORDINATOR';
        }

        if ($this->isCoordinatorCitizenProjectSector()) {
            $roles[] = 'ROLE_COORDINATOR_CITIZEN_PROJECT';
        }

        if ($this->isCoordinatorCommitteeSector()) {
            $roles[] = 'ROLE_COORDINATOR_COMMITTEE';
        }

        if ($this->isHost()) {
            $roles[] = 'ROLE_HOST';
        }

        if ($this->isSupervisor()) {
            $roles[] = 'ROLE_SUPERVISOR';
        }

        if ($this->isProcurationManager()) {
            $roles[] = 'ROLE_PROCURATION_MANAGER';
        }

        if ($this->isAssessorManager()) {
            $roles[] = 'ROLE_ASSESSOR_MANAGER';
        }

        if ($this->isAssessor()) {
            $roles[] = 'ROLE_ASSESSOR';
        }

        if ($this->isMunicipalManager()) {
            $roles[] = 'ROLE_MUNICIPAL_MANAGER';
        }

        if ($this->isMunicipalManagerSupervisor()) {
            $roles[] = 'ROLE_MUNICIPAL_MANAGER_SUPERVISOR';
        }

        if ($this->isJecouteManager()) {
            $roles[] = 'ROLE_JECOUTE_MANAGER';
        }

        if ($this->isLegislativeCandidate()) {
            $roles[] = 'ROLE_LEGISLATIVE_CANDIDATE';
        }

        if ($this->isBoardMember()) {
            $roles[] = 'ROLE_BOARD_MEMBER';
        }

        if ($this->isCitizenProjectAdministrator()) {
            $roles[] = 'ROLE_CITIZEN_PROJECT_ADMINISTRATOR';
        }

        if ($this->canaryTester) {
            $roles[] = 'ROLE_CANARY_TESTER';
        }

        if ($this->isMunicipalChief()) {
            $roles[] = 'ROLE_MUNICIPAL_CHIEF';
        }

        if ($this->hasPrintPrivilege()) {
            $roles[] = 'ROLE_PRINT_PRIVILEGE';
        }

        if ($this->isElectionResultsReporter()) {
            $roles[] = 'ROLE_ELECTION_RESULTS_REPORTER';
        }

        foreach ($this->receivedDelegatedAccesses as $delegatedAccess) {
            $roles[] = 'ROLE_DELEGATED_'.strtoupper($delegatedAccess->getType());
        }

        if ($this->isSenatorialCandidate()) {
            $roles[] = 'ROLE_SENATORIAL_CANDIDATE';
        }

        if ($this->isHeadedRegionalCandidate()) {
            $roles[] = 'ROLE_CANDIDATE_REGIONAL_HEADED';
        }

        if ($this->isLeaderRegionalCandidate()) {
            $roles[] = 'ROLE_CANDIDATE_REGIONAL_LEADER';
        }

        if ($this->isDepartmentalCandidate()) {
            $roles[] = 'ROLE_CANDIDATE_DEPARTMENTAL';
        }

        if ($this->isLre()) {
            $roles[] = 'ROLE_LRE';
        }

        if ($this->isThematicCommunityChief()) {
            $roles[] = 'ROLE_THEMATIC_COMMUNITY_CHIEF';
        }

        if ($this->isCoalitionModerator()) {
            $roles[] = 'ROLE_COALITION_MODERATOR';
        }

        if ($this->isApprovedCauseAuthor()) {
            $roles[] = 'ROLE_CAUSE_AUTHOR';
        }

        // Must be at the end as it uses $roles array
        if ($this->isAdherentMessageRedactor($roles)) {
            $roles[] = 'ROLE_MESSAGE_REDACTOR';
        }

        return array_merge(array_unique($roles), $this->roles);
    }

    public function addRoles(array $roles): void
    {
        foreach ($roles as $role) {
            $this->roles[] = $role;
        }
    }

    public function getType(): string
    {
        if ($this->isReferent()) {
            return 'REFERENT';
        }

        if ($this->isSupervisor() || $this->isHost()) {
            return 'HOST';
        }

        return 'ADHERENT';
    }

    public function hasAdvancedPrivileges(): bool
    {
        return $this->isReferent()
            || $this->isDelegatedReferent()
            || $this->isCoordinator()
            || $this->isProcurationManager()
            || $this->isAssessorManager()
            || $this->isAssessor()
            || $this->isJecouteManager()
            || $this->isSupervisor()
            || $this->isHost()
            || $this->isCitizenProjectAdministrator()
            || $this->isBoardMember()
            || $this->isDeputy()
            || $this->isDelegatedDeputy()
            || $this->isSenator()
            || $this->isDelegatedSenator()
            || $this->isMunicipalChief()
            || $this->isMunicipalManager()
            || $this->isElectionResultsReporter()
            || $this->isMunicipalManagerSupervisor()
            || $this->isSenatorialCandidate()
            || $this->isHeadedRegionalCandidate()
            || $this->isLeaderRegionalCandidate()
            || $this->isDepartmentalCandidate()
            || $this->isDelegatedCandidate()
            || $this->isLre()
            || $this->isLegislativeCandidate()
            || $this->isThematicCommunityChief()
        ;
    }

    public function getPassword()
    {
        return !$this->password ? $this->oldPassword : $this->password;
    }

    public function hasLegacyPassword(): bool
    {
        return null !== $this->oldPassword;
    }

    public function getEncoderName(): ?string
    {
        if ($this->hasLegacyPassword()) {
            return 'legacy_encoder';
        }

        return null;
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->emailAddress;
    }

    public function eraseCredentials()
    {
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getGenderName(): ?string
    {
        return array_search($this->gender, Genders::CHOICES);
    }

    public function getCustomGender(): ?string
    {
        return $this->customGender;
    }

    public function isForeignResident(): bool
    {
        return AreaUtils::CODE_FRANCE !== strtoupper($this->getCountry());
    }

    public function isParisResident(): bool
    {
        return AreaUtils::CODE_FRANCE === strtoupper($this->getCountry()) && AreaUtils::PREFIX_POSTALCODE_PARIS_DISTRICTS === substr($this->getPostalCode(), 0, 2);
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function isOtherGender(): bool
    {
        return Genders::OTHER === $this->gender;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    /**
     * @SymfonySerializer\Groups({"export"})
     */
    public function getAge(): ?int
    {
        return $this->birthdate ? $this->birthdate->diff(new \DateTime())->y : null;
    }

    public function isMinor(\DateTime $date = null): bool
    {
        return null === $this->birthdate || $this->birthdate->diff($date ?? new \DateTime())->y < 18;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        if (!ActivityPositions::exists($position)) {
            throw new \InvalidArgumentException(sprintf('Invalid position "%s", known positions are "%s".', $position, implode('", "', ActivityPositions::ALL)));
        }

        $this->position = $position;
    }

    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function isEnabled(): bool
    {
        return self::ENABLED === $this->status;
    }

    public function getActivatedAt(): ?\DateTime
    {
        return $this->activatedAt;
    }

    public function setMembershipReminded(): void
    {
        $this->membershipRemindedAt = new \DateTime();
    }

    public function isMembershipReminded(): bool
    {
        return null !== $this->membershipRemindedAt;
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"adherent_change_diff"})
     * @JMS\SerializedName("subscriptionTypeCodes")
     */
    public function getSubscriptionTypeCodes(): array
    {
        return array_values(array_map(static function (SubscriptionType $type) {
            return $type->getCode();
        }, $this->subscriptionTypes->toArray()));
    }

    /**
     * @return SubscriptionType[]
     */
    public function getSubscriptionTypes(): array
    {
        return $this->subscriptionTypes->toArray();
    }

    public function hasSubscriptionType(string $code): bool
    {
        return $this->subscriptionTypes->exists(function (int $index, SubscriptionType $type) use ($code) {
            return $type->getCode() === $code;
        });
    }

    public function hasSubscribedLocalHostEmails(): bool
    {
        return $this->hasSubscriptionType(SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
    }

    public function hasSmsSubscriptionType(): bool
    {
        return $this->hasSubscriptionType(SubscriptionTypeEnum::MILITANT_ACTION_SMS);
    }

    /**
     * Activates the Adherent account with the provided activation token.
     *
     * @throws AdherentException
     * @throws AdherentTokenException
     */
    public function activate(AdherentActivationToken $token, string $timestamp = 'now'): void
    {
        if ($this->activatedAt) {
            throw new AdherentAlreadyEnabledException($this->uuid);
        }

        $token->consume($this);

        $this->status = self::ENABLED;
        $this->activatedAt = new \DateTime($timestamp);
    }

    /**
     * Resets the Adherent password using a reset pasword token.
     *
     * @throws \InvalidArgumentException
     * @throws AdherentException
     * @throws AdherentTokenException
     */
    public function resetPassword(AdherentResetPasswordToken $token): void
    {
        if (!$newPassword = $token->getNewPassword()) {
            throw new \InvalidArgumentException('Token must have a new password.');
        }

        $token->consume($this);

        $this->clearOldPassword();
        $this->password = $newPassword;
    }

    public function changeEmail(AdherentChangeEmailToken $token): void
    {
        if (!$token->getEmail()) {
            throw new \InvalidArgumentException('Token must have a new email.');
        }

        $token->consume($this);

        $this->emailAddress = $token->getEmail();
    }

    public function clearOldPassword(): void
    {
        $this->oldPassword = null;
    }

    public function migratePassword(string $newEncodedPassword): void
    {
        $this->password = $newEncodedPassword;
    }

    /**
     * Records the adherent last login date and time.
     *
     * @param string|int $timestamp a valid date representation as a string or integer
     */
    public function recordLastLoginTime($timestamp = 'now'): void
    {
        $this->lastLoggedAt = new \DateTime($timestamp);
    }

    /**
     * Returns the last login date and time of this adherent.
     */
    public function getLastLoggedAt(): ?\DateTime
    {
        return $this->lastLoggedAt;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function updateProfile(AdherentProfile $adherentProfile, PostAddress $postAddress): void
    {
        $this->customGender = $adherentProfile->getCustomGender();
        $this->gender = $adherentProfile->getGender();
        $this->firstName = $adherentProfile->getFirstName();
        $this->lastName = $adherentProfile->getLastName();
        $this->birthdate = $adherentProfile->getBirthdate();
        $this->position = $adherentProfile->getPosition();
        $this->phone = $adherentProfile->getPhone();
        $this->nationality = $adherentProfile->getNationality();
        $this->facebookPageUrl = $adherentProfile->getFacebookPageUrl();
        $this->twitterPageUrl = $adherentProfile->getTwitterPageUrl();
        $this->telegramPageUrl = $adherentProfile->getTelegramPageUrl();
        $this->linkedinPageUrl = $adherentProfile->getLinkedinPageUrl();
        $this->job = $adherentProfile->getJob();
        $this->activityArea = $adherentProfile->getActivityArea();
        $this->mandates = $adherentProfile->getMandates();
        $this->interests = $adherentProfile->getInterests();
        $this->coalitionSubscription = $adherentProfile->isCoalitionSubscription();
        $this->causeSubscription = $adherentProfile->isCauseSubscription();
        $this->coalitionsCguAccepted = $adherentProfile->isCoalitionsCguAccepted();

        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }

    public function updateMembership(MembershipRequest $membership, PostAddress $postAddress): void
    {
        $this->customGender = $membership->customGender;
        $this->gender = $membership->gender;
        $this->firstName = $membership->firstName;
        $this->lastName = $membership->lastName;
        $this->birthdate = $membership->getBirthdate();
        $this->position = $membership->position;
        $this->phone = $membership->getPhone();
        $this->emailAddress = $membership->getEmailAddress();
        $this->mandates = $membership->getMandates();
        $this->nationality = $membership->nationality;

        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }

    /**
     * Joins a committee as a HOST privileged person.
     */
    public function hostCommittee(
        Committee $committee,
        \DateTimeInterface $subscriptionDate = null
    ): CommitteeMembership {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_HOST, $subscriptionDate ?? new \DateTime());
    }

    /**
     * Joins a committee as a simple FOLLOWER privileged person.
     */
    public function followCommittee(
        Committee $committee,
        \DateTimeInterface $subscriptionDate = null
    ): CommitteeMembership {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_FOLLOWER, $subscriptionDate ?? new \DateTime());
    }

    private function joinCommittee(
        Committee $committee,
        string $privilege,
        \DateTimeInterface $subscriptionDate
    ): CommitteeMembership {
        $committee->incrementMembersCount();

        return CommitteeMembership::createForAdherent($committee, $this, $privilege, $subscriptionDate);
    }

    /**
     * Joins a citizen project as a ADMINISTRATOR privileged person.
     */
    public function administrateCitizenProject(
        CitizenProject $citizenProject,
        string $subscriptionDate = 'now'
    ): CitizenProjectMembership {
        return $this->joinCitizenProject($citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR, $subscriptionDate);
    }

    /**
     * Joins a citizen project as a simple FOLLOWER privileged person.
     */
    public function followCitizenProject(
        CitizenProject $citizenProject,
        string $subscriptionDate = 'now'
    ): CitizenProjectMembership {
        return $this->joinCitizenProject($citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_FOLLOWER, $subscriptionDate);
    }

    private function joinCitizenProject(
        CitizenProject $citizenProject,
        string $privilege,
        string $subscriptionDate
    ): CitizenProjectMembership {
        $citizenProject->incrementMembersCount();

        $memberShip = CitizenProjectMembership::createForAdherent($citizenProject, $this, $privilege, $subscriptionDate);

        $this->citizenProjectMemberships->add($memberShip);

        return $memberShip;
    }

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    /**
     * Returns whether or not the current adherent is the same as the given one.
     */
    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
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
     * @SymfonySerializer\Groups({"export"})
     */
    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getManagedArea(): ?ReferentManagedArea
    {
        return $this->managedArea;
    }

    public function setManagedArea(?ReferentManagedArea $managedArea): void
    {
        $this->managedArea = $managedArea;
    }

    public function getReferentTeamMember(): ?ReferentTeamMember
    {
        return $this->referentTeamMember;
    }

    public function setReferentTeamMember(?ReferentTeamMember $referentTeam): void
    {
        if ($referentTeam) {
            $referentTeam->setMember($this);
        }
        $this->referentTeamMember = $referentTeam;
    }

    public function getReferentOfReferentTeam(): ?Adherent
    {
        return $this->referentTeamMember ? $this->referentTeamMember->getReferent() : null;
    }

    public function getMemberOfReferentTeam(): ?Adherent
    {
        return $this->referentTeamMember ? $this->referentTeamMember->getMember() : null;
    }

    /**
     * @SymfonySerializer\Groups({"referent"})
     *
     * @return string[]
     */
    public function getManagedAreaTagCodes(): array
    {
        return $this->getManagedArea()
            ? $this->getManagedArea()->getReferentTagCodes()
            : []
        ;
    }

    public function getProcurationManagedArea(): ?ProcurationManagedArea
    {
        return $this->procurationManagedArea;
    }

    public function setProcurationManagedArea(ProcurationManagedArea $procurationManagedArea = null): void
    {
        $this->procurationManagedArea = $procurationManagedArea;
    }

    public function getAssessorManagedArea(): ?AssessorManagedArea
    {
        return $this->assessorManagedArea;
    }

    public function setAssessorManagedArea(AssessorManagedArea $assessorManagedArea = null): void
    {
        $this->assessorManagedArea = $assessorManagedArea;
    }

    public function getAssessorRole(): ?AssessorRoleAssociation
    {
        return $this->assessorRole;
    }

    public function setAssessorRole(?AssessorRoleAssociation $assessorRole): void
    {
        $this->assessorRole = $assessorRole;
    }

    public function getMunicipalManagerRole(): ?MunicipalManagerRoleAssociation
    {
        return $this->municipalManagerRole;
    }

    public function setMunicipalManagerRole(?MunicipalManagerRoleAssociation $municipalManagerRole): void
    {
        $this->municipalManagerRole = $municipalManagerRole;
    }

    public function getMunicipalManagerSupervisorRole(): ?MunicipalManagerSupervisorRole
    {
        return $this->municipalManagerSupervisorRole;
    }

    public function setMunicipalManagerSupervisorRole(
        ?MunicipalManagerSupervisorRole $municipalManagerSupervisorRole
    ): void {
        $this->municipalManagerSupervisorRole = $municipalManagerSupervisorRole;
    }

    public function revokeMunicipalManagerSupervisorRole(): void
    {
        $this->municipalManagerSupervisorRole = null;
    }

    public function isMunicipalManagerSupervisor(): bool
    {
        return $this->municipalManagerSupervisorRole instanceof MunicipalManagerSupervisorRole;
    }

    public function getCoalitionModeratorRole(): ?CoalitionModeratorRoleAssociation
    {
        return $this->coalitionModeratorRole;
    }

    public function setCoalitionModeratorRole(?CoalitionModeratorRoleAssociation $coalitionModeratorRole): void
    {
        $this->coalitionModeratorRole = $coalitionModeratorRole;
    }

    public function revokeCoalitionModeratorRole(): void
    {
        $this->coalitionModeratorRole = null;
    }

    public function isCoalitionModerator(): bool
    {
        return $this->coalitionModeratorRole instanceof CoalitionModeratorRoleAssociation;
    }

    public function getBoardMember(): ?BoardMember
    {
        return $this->boardMember;
    }

    public function setBoardMember(string $area, iterable $roles): void
    {
        if (!$this->boardMember) {
            $this->boardMember = new BoardMember();
            $this->boardMember->setAdherent($this);
        }

        $this->boardMember->setArea($area);
        $this->boardMember->setRoles($roles);
    }

    public function isBoardMember(): bool
    {
        return $this->boardMember instanceof BoardMember
            && !empty($this->boardMember->getArea()) && !empty($this->boardMember->getRoles());
    }

    public function revokeBoardMember(): void
    {
        if (!$this->boardMember) {
            return;
        }

        $this->boardMember->revoke();
        $this->boardMember = null;
    }

    public function setReferent(array $tags, string $markerLatitude = null, string $markerLongitude = null): void
    {
        $this->managedArea = new ReferentManagedArea($tags, $markerLatitude, $markerLongitude);
    }

    public function isReferent(): bool
    {
        return $this->managedArea instanceof ReferentManagedArea
            && !$this->managedArea->getTags()->isEmpty();
    }

    public function isDelegatedReferent(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType('referent')) > 0;
    }

    public function isCoReferent(): bool
    {
        return $this->referentTeamMember instanceof ReferentTeamMember;
    }

    public function isLimitedCoReferent(): bool
    {
        return $this->isCoReferent() && $this->referentTeamMember->isLimited();
    }

    public function revokeReferent(): void
    {
        $this->managedArea = null;
    }

    public function revokeAssessorManager(): void
    {
        $this->assessorManagedArea = null;
    }

    public function revokeProcurationManager(): void
    {
        $this->procurationManagedArea = null;
    }

    public function revokeJecouteManager(): void
    {
        $this->jecouteManagedArea = null;
    }

    public function getTerritorialCouncilMembership(): ?TerritorialCouncilMembership
    {
        return $this->territorialCouncilMembership;
    }

    public function setTerritorialCouncilMembership(?TerritorialCouncilMembership $territorialCouncilMembership): void
    {
        $this->territorialCouncilMembership = $territorialCouncilMembership;

        if ($territorialCouncilMembership) {
            $this->territorialCouncilMembership->setAdherent($this);
        }
    }

    public function hasTerritorialCouncilMembership(): bool
    {
        return $this->territorialCouncilMembership instanceof TerritorialCouncilMembership;
    }

    public function isTerritorialCouncilMember(): bool
    {
        return $this->territorialCouncilMembership instanceof TerritorialCouncilMembership
            && $this->territorialCouncilMembership->getTerritorialCouncil()->isActive();
    }

    public function isTerritorialCouncilPresident(): bool
    {
        return $this->isTerritorialCouncilMember()
            && $this->territorialCouncilMembership->isPresident();
    }

    public function revokeTerritorialCouncilMembership(): void
    {
        if (!$this->territorialCouncilMembership) {
            return;
        }

        $this->territorialCouncilMembership->revoke();
        $this->territorialCouncilMembership = null;
    }

    public function getPoliticalCommitteeMembership(): ?PoliticalCommitteeMembership
    {
        return $this->politicalCommitteeMembership;
    }

    public function setPoliticalCommitteeMembership(?PoliticalCommitteeMembership $politicalCommitteeMembership): void
    {
        $this->politicalCommitteeMembership = $politicalCommitteeMembership;

        if ($politicalCommitteeMembership) {
            $this->politicalCommitteeMembership->setAdherent($this);
        }
    }

    public function hasPoliticalCommitteeMembership(): bool
    {
        return $this->politicalCommitteeMembership instanceof PoliticalCommitteeMembership;
    }

    public function isPoliticalCommitteeMember(): bool
    {
        return $this->politicalCommitteeMembership instanceof PoliticalCommitteeMembership
            && $this->politicalCommitteeMembership->getPoliticalCommittee()->isActive();
    }

    public function revokePoliticalCommitteeMembership(): void
    {
        if (!$this->politicalCommitteeMembership) {
            return;
        }

        $this->politicalCommitteeMembership->revoke();
        $this->politicalCommitteeMembership = null;
    }

    public function isMayorOrLeader(): bool
    {
        return $this->politicalCommitteeMembership
            && ($this->politicalCommitteeMembership->hasOneOfQualities([TerritorialCouncilQualityEnum::MAYOR, TerritorialCouncilQualityEnum::LEADER]));
    }

    public function getManagedAreaMarkerLatitude(): ?string
    {
        if (!$this->managedArea) {
            return '';
        }

        return $this->managedArea->getMarkerLatitude();
    }

    public function getManagedAreaMarkerLongitude(): ?string
    {
        if (!$this->managedArea) {
            return '';
        }

        return $this->managedArea->getMarkerLongitude();
    }

    public function isProcurationManager(): bool
    {
        return $this->procurationManagedArea instanceof ProcurationManagedArea && !empty($this->procurationManagedArea->getCodes());
    }

    public function isAssessorManager(): bool
    {
        return $this->assessorManagedArea instanceof AssessorManagedArea && !empty($this->assessorManagedArea->getCodes());
    }

    public function isAssessor(): bool
    {
        return !empty($this->assessorRole);
    }

    public function isMunicipalManager(): bool
    {
        return !empty($this->municipalManagerRole);
    }

    public function revokeMunicipalManager(): void
    {
        $this->municipalManagerRole = null;
    }

    public function canBeProxy(): bool
    {
        return $this->isReferent() || $this->isProcurationManager();
    }

    public function getProcurationManagedAreaCodesAsString(): ?string
    {
        if (!$this->procurationManagedArea) {
            return '';
        }

        return $this->procurationManagedArea->getCodesAsString();
    }

    public function setProcurationManagedAreaCodesAsString(string $codes = null): void
    {
        if (!$this->procurationManagedArea) {
            $this->procurationManagedArea = new ProcurationManagedArea();
        }

        $this->procurationManagedArea->setCodesAsString($codes);
    }

    public function getAssessorManagedAreaCodesAsString(): ?string
    {
        if (!$this->assessorManagedArea) {
            return '';
        }

        return $this->assessorManagedArea->getCodesAsString();
    }

    public function setAssessorManagedAreaCodesAsString(string $codes = null): void
    {
        if (!$this->assessorManagedArea) {
            $this->assessorManagedArea = new AssessorManagedArea();
        }

        $this->assessorManagedArea->setCodesAsString($codes);
    }

    public function isCoordinator(): bool
    {
        return $this->isCoordinatorCommitteeSector() || $this->isCoordinatorCitizenProjectSector();
    }

    public function isCoordinatorCitizenProjectSector(): bool
    {
        return $this->coordinatorCitizenProjectArea && $this->coordinatorCitizenProjectArea->getCodes();
    }

    public function isCoordinatorCommitteeSector(): bool
    {
        return $this->coordinatorCommitteeArea && $this->coordinatorCommitteeArea->getCodes();
    }

    public function getJecouteManagedArea(): ?JecouteManagedArea
    {
        return $this->jecouteManagedArea;
    }

    public function setJecouteManagedZone(Zone $zone = null): void
    {
        if (!$this->jecouteManagedArea) {
            $this->jecouteManagedArea = new JecouteManagedArea();
        }

        $this->jecouteManagedArea->setZone($zone);
    }

    public function isJecouteManager(): bool
    {
        return $this->jecouteManagedArea instanceof JecouteManagedArea && $this->jecouteManagedArea->getZone();
    }

    /**
     * @return CommitteeMembership[]|CommitteeMembershipCollection
     */
    public function getMemberships(): CommitteeMembershipCollection
    {
        if (!$this->memberships instanceof CommitteeMembershipCollection) {
            $this->memberships = new CommitteeMembershipCollection($this->memberships->toArray());
        }

        return $this->memberships;
    }

    public function hasVotingCommitteeMembership(): bool
    {
        return null !== $this->getMemberships()->getVotingCommitteeMembership();
    }

    public function hasLoadedMemberships(): bool
    {
        return $this->isCollectionLoaded($this->memberships);
    }

    public function getMembershipFor(Committee $committee): ?CommitteeMembership
    {
        foreach ($this->memberships as $membership) {
            if ($membership->matches($this, $committee)) {
                return $membership;
            }
        }

        return null;
    }

    /**
     * @return CitizenProjectMembership[]|CitizenProjectMembershipCollection
     */
    public function getCitizenProjectMemberships($withoutRefused = false): CitizenProjectMembershipCollection
    {
        if (!$this->citizenProjectMemberships instanceof CitizenProjectMembershipCollection) {
            $this->citizenProjectMemberships = new CitizenProjectMembershipCollection($this->citizenProjectMemberships->toArray());
        }

        if ($withoutRefused) {
            return $this->citizenProjectMemberships->filterRefusedProjects();
        }

        return $this->citizenProjectMemberships;
    }

    public function hasLoadedCitizenProjectMemberships(): bool
    {
        return $this->isCollectionLoaded($this->citizenProjectMemberships);
    }

    public function getCitizenProjectMembershipFor(CitizenProject $citizenProject): ?CitizenProjectMembership
    {
        foreach ($this->citizenProjectMemberships as $citizenProjectMembership) {
            if ($citizenProjectMembership->matches($this, $citizenProject)) {
                return $citizenProjectMembership;
            }
        }

        return null;
    }

    public function isBasicAdherent(): bool
    {
        return $this->isAdherent()
            && !$this->isHost()
            && !$this->isSupervisor()
            && !$this->isCitizenProjectAdministrator()
            && !$this->isReferent()
            && !$this->isBoardMember()
            && !$this->isDeputy()
            && !$this->isSenator()
        ;
    }

    public function isHost(): bool
    {
        return $this->getMemberships()->countCommitteeHostMemberships() >= 1;
    }

    public function isHostOf(Committee $committee): bool
    {
        if (!$membership = $this->getMembershipFor($committee)) {
            return false;
        }

        return $membership->isHostMember();
    }

    public function isCitizenProjectAdministrator(): bool
    {
        return $this->getCitizenProjectMemberships()->countCitizenProjectAdministratorMemberships() >= 1;
    }

    public function isAdministratorOf(CitizenProject $citizenProject): bool
    {
        if (!$membership = $this->getCitizenProjectMembershipFor($citizenProject)) {
            return false;
        }

        return $membership->canAdministrateCitizenProject();
    }

    public function isSupervisor(bool $isProvisional = null): bool
    {
        return $this->getSupervisorMandates($isProvisional)->count() > 0;
    }

    public function isSupervisorOf(Committee $committee, bool $isProvisional = null): bool
    {
        return $this->adherentMandates->filter(static function (AbstractAdherentMandate $mandate) use ($committee, $isProvisional) {
            return $mandate instanceof CommitteeAdherentMandate
                && $mandate->getCommittee() === $committee
                && null === $mandate->getFinishAt()
                && CommitteeMandateQualityEnum::SUPERVISOR === $mandate->getQuality()
                && (null === $isProvisional || $mandate->isProvisional() === $isProvisional)
            ;
        })->count() > 0;
    }

    public function isLegislativeCandidate(): bool
    {
        return $this->legislativeCandidateManagedDistrict instanceof District;
    }

    public function getLegislativeCandidateManagedDistrict(): ?District
    {
        return $this->legislativeCandidateManagedDistrict;
    }

    public function setLegislativeCandidateManagedDistrict(?District $legislativeCandidateManagedDistrict): void
    {
        $this->legislativeCandidateManagedDistrict = $legislativeCandidateManagedDistrict;
    }

    public function isNicknameUsed(): bool
    {
        return $this->nicknameUsed;
    }

    /**
     * @SymfonySerializer\Groups({"user_profile"})
     */
    public function getUseNickname(): bool
    {
        return $this->isNicknameUsed();
    }

    public function setNicknameUsed(bool $nicknameUsed): void
    {
        $this->nicknameUsed = $nicknameUsed;
    }

    public function addSubscriptionType(SubscriptionType $type): void
    {
        if (!$this->subscriptionTypes->contains($type)) {
            $this->subscriptionTypes->add($type);
        }
    }

    public function removeSubscriptionType(SubscriptionType $type): void
    {
        $this->subscriptionTypes->removeElement($type);
    }

    public function removeSubscriptionTypeByCode(string $code): void
    {
        foreach ($this->subscriptionTypes as $type) {
            if ($code === $type->getCode()) {
                $this->removeSubscriptionType($type);
            }
        }
    }

    public function setSubscriptionTypes(array $subscriptionTypes)
    {
        $this->subscriptionTypes = new ArrayCollection();
        foreach ($subscriptionTypes as $type) {
            $this->addSubscriptionType($type);
        }
    }

    public function getCommitteeFeedItems(): iterable
    {
        return $this->committeeFeedItems;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setTags(iterable $tags): void
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    public function addTag(AdherentTag $adherentTag): void
    {
        if (!$this->tags->contains($adherentTag)) {
            $this->tags->add($adherentTag);
        }
    }

    public function removeTag(AdherentTag $adherentTag): void
    {
        $this->tags->removeElement($adherentTag);
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"adherent_change_diff"})
     * @JMS\SerializedName("referentTagCodes")
     */
    public function getReferentTagCodes(): array
    {
        return array_map(function (ReferentTag $tag) { return $tag->getCode(); }, $this->referentTags->toArray());
    }

    public function hasCitizenProjectHostEmailSubscription(): bool
    {
        return $this->hasSubscriptionType(SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL);
    }

    public function getCoordinatorCitizenProjectArea(): ?CoordinatorManagedArea
    {
        return $this->coordinatorCitizenProjectArea;
    }

    public function setCoordinatorCitizenProjectArea(?CoordinatorManagedArea $coordinatorCitizenProjectArea): void
    {
        $this->coordinatorCitizenProjectArea = $coordinatorCitizenProjectArea;
    }

    public function getCoordinatorCommitteeArea(): ?CoordinatorManagedArea
    {
        return $this->coordinatorCommitteeArea;
    }

    public function setCoordinatorCommitteeArea(?CoordinatorManagedArea $coordinatorCommitteeArea): void
    {
        $this->coordinatorCommitteeArea = $coordinatorCommitteeArea;
    }

    public function getManagedDistrict(): ?District
    {
        return $this->managedDistrict;
    }

    public function setManagedDistrict(?District $district): void
    {
        $this->managedDistrict = $district;
    }

    public function isDeputy(): bool
    {
        return $this->managedDistrict instanceof District;
    }

    public function isDelegatedDeputy(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType('deputy')) > 0;
    }

    public function isAdherent(): bool
    {
        return $this->adherent;
    }

    public function isUser(): bool
    {
        return !$this->isAdherent();
    }

    public function join(): void
    {
        $this->adherent = true;
    }

    public function getOAuthUser(): InMemoryOAuthUser
    {
        if (!$this->oAuthUser) {
            $this->oAuthUser = new InMemoryOAuthUser($this->uuid);
        }

        return $this->oAuthUser;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->emailAddress,
            $this->password,
            $this->getRoles(),
        ]);
    }

    public function unserialize($serialized)
    {
        [$this->id, $this->emailAddress, $this->password, $this->roles] = unserialize($serialized);
    }

    public function setRemindSent(bool $remindSent): void
    {
        $this->remindSent = $remindSent;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function hasMandate(): bool
    {
        return !empty($this->mandates);
    }

    public function getSenatorArea(): ?SenatorArea
    {
        return $this->senatorArea;
    }

    public function setSenatorArea(?SenatorArea $senatorArea): void
    {
        $this->senatorArea = $senatorArea;
    }

    public function getConsularManagedArea(): ?ConsularManagedArea
    {
        return $this->consularManagedArea;
    }

    public function setConsularManagedArea(?ConsularManagedArea $consularManagedArea): void
    {
        $this->consularManagedArea = $consularManagedArea;
    }

    public function isCommentsCguAccepted(): bool
    {
        return $this->commentsCguAccepted;
    }

    public function setCommentsCguAccepted(bool $commentsCguAccepted): void
    {
        $this->commentsCguAccepted = $commentsCguAccepted;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(Media $media = null): void
    {
        $this->media = $media;
    }

    public function displayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function setDisplayMedia(bool $displayMedia): void
    {
        $this->displayMedia = $displayMedia;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @JMS\Groups({"adherent_change_diff", "public"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("city")
     *
     * @SymfonySerializer\Groups({"export"})
     */
    public function getCityName(): ?string
    {
        return $this->postAddress->getCityName();
    }

    public function isEmailUnsubscribed(): bool
    {
        return $this->emailUnsubscribed;
    }

    public function setEmailUnsubscribed(bool $value): void
    {
        if ($value) {
            $this->emailUnsubscribedAt = new \DateTime();
        }

        $this->emailUnsubscribed = $value;
    }

    private function isAdherentMessageRedactor(array $roles): bool
    {
        return
            array_intersect($roles, [
                'ROLE_REFERENT',
                'ROLE_DEPUTY',
                'ROLE_HOST',
                'ROLE_SUPERVISOR',
                'ROLE_CITIZEN_PROJECT_ADMINISTRATOR',
                'ROLE_MUNICIPAL_CHIEF',
                'ROLE_SENATOR',
                'ROLE_LEGISLATIVE_CANDIDATE',
                'ROLE_CAUSE_AUTHOR',
            ])
            || $this->isDelegatedReferent()
            || $this->isDelegatedDeputy()
            || $this->isDelegatedSenator()
            || $this->isCandidate()
            || $this->isDelegatedCandidate()
        ;
    }

    public function isMunicipalChief(): bool
    {
        return $this->municipalChiefManagedArea instanceof MunicipalChiefManagedArea
            && $this->municipalChiefManagedArea->getInseeCode();
    }

    public function getMunicipalChiefManagedArea(): ?MunicipalChiefManagedArea
    {
        return $this->municipalChiefManagedArea;
    }

    public function setMunicipalChiefManagedArea(MunicipalChiefManagedArea $municipalChiefManagedArea = null): void
    {
        $this->municipalChiefManagedArea = $municipalChiefManagedArea;
    }

    public function __clone()
    {
        $this->subscriptionTypes = new ArrayCollection($this->subscriptionTypes->toArray());
        $this->territorialCouncilMembership = $this->territorialCouncilMembership ? clone $this->territorialCouncilMembership : null;
    }

    /**
     * @SymfonySerializer\Groups({"user_profile"})
     */
    public function getDetailedRoles(): array
    {
        $roles = [];

        if ($this->isReferent()) {
            $roles[] = [
                'label' => 'ROLE_REFERENT',
                'codes' => $this->getManagedAreaTagCodes(),
            ];
        }

        if ($this->isMunicipalChief()) {
            $roles[] = [
                'label' => 'ROLE_MUNICIPAL_CHIEF',
                'codes' => [$this->municipalChiefManagedArea->getInseeCode()],
            ];
        }

        if ($this->hasPrintPrivilege()) {
            $roles[] = [
                'label' => 'ROLE_PRINT_PRIVILEGE',
            ];
        }

        return $roles;
    }

    public function hasPrintPrivilege(): bool
    {
        return $this->printPrivilege;
    }

    public function setPrintPrivilege(bool $printPrivilege): void
    {
        $this->printPrivilege = $printPrivilege;
    }

    public function hasFormationSpaceAccess(): bool
    {
        return
            $this->isHost()
            || $this->isSupervisor()
            || $this->isReferent()
            || $this->isMunicipalChief()
        ;
    }

    public function getCharters(): AdherentCharterCollection
    {
        if (!$this->charters instanceof AdherentCharterCollection) {
            $this->charters = new AdherentCharterCollection($this->charters->toArray());
        }

        return $this->charters;
    }

    public function addCharter(AdherentCharterInterface $charter): void
    {
        if (!$this->charters->contains($charter)) {
            $charter->setAdherent($this);
            $this->charters->add($charter);
        }
    }

    public function isSenator(): bool
    {
        return !empty($this->senatorArea);
    }

    public function isDelegatedSenator(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType('senator')) > 0;
    }

    public function isConsular(): bool
    {
        return !empty($this->consularManagedArea);
    }

    /**
     * @param UserInterface|self $user
     */
    public function isEqualTo(UserInterface $user)
    {
        return $this->id === $user->getId() && [] === array_diff($this->roles, $user->getRoles());
    }

    public function isElectionResultsReporter(): bool
    {
        return $this->electionResultsReporter;
    }

    public function setElectionResultsReporter(bool $electionResultsReporter): void
    {
        $this->electionResultsReporter = $electionResultsReporter;
    }

    public function markAsToDelete(): void
    {
        $this->status = self::TO_DELETE;
    }

    public function isToDelete(): bool
    {
        return self::TO_DELETE === $this->status;
    }

    public function getCertifiedAt(): ?\DateTime
    {
        return $this->certifiedAt;
    }

    /**
     * @SymfonySerializer\Groups({"user_profile", "profile_read"})
     */
    public function isCertified(): bool
    {
        return null !== $this->certifiedAt;
    }

    public function certify(): void
    {
        if ($this->certifiedAt) {
            return;
        }

        $this->certifiedAt = new \DateTime();
    }

    public function uncertify(): void
    {
        $this->certifiedAt = null;
    }

    public function getCertificationRequests(): CertificationRequestCollection
    {
        if (!$this->certificationRequests instanceof CertificationRequestCollection) {
            $this->certificationRequests = new CertificationRequestCollection($this->certificationRequests->toArray());
        }

        return $this->certificationRequests;
    }

    public function startCertificationRequest(): CertificationRequest
    {
        if ($this->getCertificationRequests()->hasPendingCertificationRequest()) {
            throw new \LogicException('Adherent already has a pending certification request.');
        }

        $pendingCertificationRequest = new CertificationRequest($this);

        $this->certificationRequests->add($pendingCertificationRequest);

        return $pendingCertificationRequest;
    }

    /**
     * @return DelegatedAccess[]|Collection|iterable
     */
    public function getReceivedDelegatedAccesses(): iterable
    {
        return $this->receivedDelegatedAccesses;
    }

    public function getReceivedDelegatedAccessByUuid(?string $delegatedAccessUuid): ?DelegatedAccess
    {
        if (null === $delegatedAccessUuid) {
            return null;
        }

        /** @var DelegatedAccess $delegatedAccess */
        foreach ($this->receivedDelegatedAccesses as $delegatedAccess) {
            if ($delegatedAccess->getUuid()->toString() === $delegatedAccessUuid) {
                return $delegatedAccess;
            }
        }

        return null;
    }

    public function addReceivedDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        if (!$this->receivedDelegatedAccesses->contains($delegatedAccess)) {
            $this->receivedDelegatedAccesses->add($delegatedAccess);
        }
    }

    public function removeReceivedDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        $this->receivedDelegatedAccesses->removeElement($delegatedAccess);
    }

    public function setReceivedDelegatedAccesses(iterable $delegatedAccesses): void
    {
        $this->receivedDelegatedAccesses->clear();
        foreach ($delegatedAccesses as $delegatedAccess) {
            $this->addReceivedDelegatedAccess($delegatedAccess);
        }
    }

    /**
     * @return DelegatedAccess[]|Collection
     */
    public function getReceivedDelegatedAccessOfType(string $type): Collection
    {
        return $this->receivedDelegatedAccesses->filter(static function (DelegatedAccess $delegatedAccess) use ($type) {
            return $delegatedAccess->getType() === $type && \count($delegatedAccess->getAccesses());
        });
    }

    public function hasDelegatedFromUser(self $delegator, string $access = null): bool
    {
        /** @var DelegatedAccess $delegatedAccess */
        foreach ($this->getReceivedDelegatedAccesses() as $delegatedAccess) {
            if ($delegatedAccess->getDelegator() === $delegator && (!$access || \in_array($access, $delegatedAccess->getAccesses(), true))) {
                return true;
            }
        }

        return false;
    }

    public function isSenatorialCandidate(): bool
    {
        return $this->senatorialCandidateManagedArea instanceof SenatorialCandidateManagedArea;
    }

    public function getSenatorialCandidateManagedArea(): ?SenatorialCandidateManagedArea
    {
        return $this->senatorialCandidateManagedArea;
    }

    public function setSenatorialCandidateManagedArea(
        ?SenatorialCandidateManagedArea $senatorialCandidateManagedArea
    ): void {
        $this->senatorialCandidateManagedArea = $senatorialCandidateManagedArea;
    }

    public function getCandidateManagedArea(): ?CandidateManagedArea
    {
        return $this->candidateManagedArea;
    }

    public function setCandidateManagedArea(?CandidateManagedArea $candidateManagedArea): void
    {
        $this->candidateManagedArea = $candidateManagedArea;
    }

    public function isHeadedRegionalCandidate(): bool
    {
        return $this->candidateManagedArea && $this->candidateManagedArea->isRegionalZone();
    }

    public function isLeaderRegionalCandidate(): bool
    {
        return $this->candidateManagedArea && $this->candidateManagedArea->isDepartmentalZone();
    }

    public function isDepartmentalCandidate(): bool
    {
        return $this->candidateManagedArea && $this->candidateManagedArea->isCantonalZone();
    }

    public function isCandidate(): bool
    {
        return $this->candidateManagedArea instanceof CandidateManagedArea;
    }

    public function isDelegatedCandidate(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE)) > 0;
    }

    public function isDelegatedHeadedRegionalCandidate(): bool
    {
        foreach ($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE) as $delegatedAccess) {
            if ($delegatedAccess->getDelegator()->isHeadedRegionalCandidate()) {
                return true;
            }
        }

        return false;
    }

    public function isDelegatedLeaderRegionalCandidate(): bool
    {
        foreach ($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE) as $delegatedAccess) {
            if ($delegatedAccess->getDelegator()->isLeaderRegionalCandidate()) {
                return true;
            }
        }

        return false;
    }

    public function isDelegatedDepartmentalCandidate(): bool
    {
        foreach ($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE) as $delegatedAccess) {
            if ($delegatedAccess->getDelegator()->isDepartmentalCandidate()) {
                return true;
            }
        }

        return false;
    }

    public function getLreArea(): ?LreArea
    {
        return $this->lreArea;
    }

    public function setLreArea(?LreArea $lreArea): void
    {
        $this->lreArea = $lreArea;
    }

    public function isLre(): bool
    {
        return $this->lreArea instanceof LreArea;
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

    public function getLinkedinPageUrl(): ?string
    {
        return $this->linkedinPageUrl;
    }

    public function setLinkedinPageUrl(?string $linkedinPageUrl): void
    {
        $this->linkedinPageUrl = $linkedinPageUrl;
    }

    public function getTelegramPageUrl(): ?string
    {
        return $this->telegramPageUrl;
    }

    public function setTelegramPageUrl(?string $telegramPageUrl): void
    {
        $this->telegramPageUrl = $telegramPageUrl;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(?string $job): void
    {
        $this->job = $job;
    }

    public function getActivityArea(): ?string
    {
        return $this->activityArea;
    }

    public function setActivityArea(?string $activityArea): void
    {
        $this->activityArea = $activityArea;
    }

    public function getCommitment(): ?AdherentCommitment
    {
        return $this->commitment;
    }

    public function setCommitment(AdherentCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public function getHandledThematicCommunities(): Collection
    {
        return $this->handledThematicCommunities;
    }

    public function setHandledThematicCommunities(Collection $handledThematicCommunities): void
    {
        $this->handledThematicCommunities = $handledThematicCommunities;
    }

    public function addHandledThematicCommunity(ThematicCommunity $thematicCommunity): void
    {
        if (!$this->handledThematicCommunities->contains($thematicCommunity)) {
            $this->handledThematicCommunities->add($thematicCommunity);
        }
    }

    public function removeHandledThematicCommunity(ThematicCommunity $thematicCommunity): void
    {
        $this->handledThematicCommunities->removeElement($thematicCommunity);
    }

    public function isThematicCommunityChief()
    {
        return $this->handledThematicCommunities->count() > 0;
    }

    public function getAdherentMandates(): Collection
    {
        return $this->adherentMandates;
    }

    public function getActiveAdherentMandates(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->orderBy(['beginAt' => 'DESC'])
        ;

        return $this->adherentMandates->matching($criteria);
    }

    /**
     * @return CommitteeAdherentMandate[]
     */
    public function getActiveDesignatedAdherentMandates(): array
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
        ;

        return $this->adherentMandates
            ->matching($criteria)
            ->filter(function (AbstractAdherentMandate $mandate) {
                return $mandate instanceof CommitteeAdherentMandate;
            })
            ->toArray()
        ;
    }

    public function findTerritorialCouncilMandates(?string $quality = null, bool $active = false): array
    {
        return $this->adherentMandates->filter(function (AbstractAdherentMandate $mandate) use ($quality, $active) {
            return $mandate instanceof TerritorialCouncilAdherentMandate
                && (null === $quality || $mandate->getQuality() === $quality)
                && (false === $active || null === $mandate->getFinishAt())
            ;
        })->toArray();
    }

    public function getFilePermissions(): array
    {
        $roles = array_map(static function (string $role) {
            return str_replace('role_', '', mb_strtolower($role));
        }, $this->getRoles());

        return array_values(array_intersect(FilePermissionEnum::toArray(), $roles));
    }

    public function isNotifiedForElection(): bool
    {
        return $this->notifiedForElection;
    }

    public function notifyForElection(): void
    {
        $this->notifiedForElection = true;
    }

    public function getProvisionalSupervisors(): Collection
    {
        return $this->provisionalSupervisors;
    }

    public function isProvisionalSupervisor(): bool
    {
        return $this->provisionalSupervisors->filter(function (ProvisionalSupervisor $provisionalSupervisor) {
            $committee = $provisionalSupervisor->getCommittee();

            return $committee->isWaitingForApproval();
        })->count() > 0;
    }

    public function getSupervisorMandates(bool $isProvisional = null, string $gender = null): Collection
    {
        return $this->adherentMandates->filter(static function (AbstractAdherentMandate $mandate) use ($gender, $isProvisional) {
            return $mandate instanceof CommitteeAdherentMandate
                && null !== $mandate->getCommittee()
                && CommitteeMandateQualityEnum::SUPERVISOR === $mandate->getQuality()
                && null === $mandate->getFinishAt()
                && (null === $isProvisional || $mandate->isProvisional() === $isProvisional)
                && (null === $gender || $mandate->getGender() === $gender)
            ;
        });
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function isCoalitionUser(): bool
    {
        return MembershipSourceEnum::COALITIONS === $this->source;
    }

    public function isCoalitionSubscription(): bool
    {
        return $this->coalitionSubscription;
    }

    public function setCoalitionSubscription(bool $coalitionSubscription): void
    {
        $this->coalitionSubscription = $coalitionSubscription;
    }

    public function isCauseSubscription(): bool
    {
        return $this->causeSubscription;
    }

    public function setCauseSubscription(bool $causeSubscription): void
    {
        $this->causeSubscription = $causeSubscription;
    }

    public function isCoalitionsCguAccepted(): bool
    {
        return $this->coalitionsCguAccepted;
    }

    public function setCoalitionsCguAccepted(bool $coalitionsCguAccepted): void
    {
        $this->coalitionsCguAccepted = $coalitionsCguAccepted;
    }

    public function isApprovedCauseAuthor(): bool
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('status', Cause::STATUS_APPROVED));

        return $this->causes->matching($criteria)->count();
    }
}
