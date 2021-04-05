<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\MyCausesFilter;
use App\Api\Filter\OrTextSearchFilter;
use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\AuthorInterface;
use App\Entity\EntityFollowersTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use App\Entity\Event\CauseEvent;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\FollowedInterface;
use App\Entity\FollowerInterface;
use App\Entity\ImageTrait;
use App\Entity\StaticSegmentTrait;
use App\Validator\Coalition\EnabledCoalitionUser as AssertEnabledCoalitionUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_client_items_per_page": true,
 *         "order": {"followersCount": "DESC", "name": "ASC"},
 *         "normalization_context": {
 *             "groups": {"cause_read", "image_owner_exposed"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"cause_write"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/causes",
 *         },
 *         "mine": {
 *             "method": "GET",
 *             "path": "/v3/causes",
 *         },
 *         "post": {
 *             "path": "/v3/causes",
 *             "access_control": "is_granted('IS_AUTHENTICATED_REMEMBERED')",
 *             "normalization_context": {"groups": {"cause_read"}}
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/causes/{id}",
 *             "requirements": {"id": "[\w-]+"},
 *             "defaults": {"_api_receive": false},
 *             "controller": "App\Controller\Api\Coalition\RetrieveCauseController",
 *         },
 *         "put": {
 *             "path": "/v3/causes/{id}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "access_control": "object.getAuthor() == user",
 *             "denormalization_context": {"groups": {"cause_update"}}
 *         },
 *         "follow": {
 *             "method": "PUT|DELETE",
 *             "denormalization_context": {"api_allow_update": false},
 *             "path": "/v3/causes/{id}/follower",
 *             "controller": "App\Controller\Api\FollowController::follower",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "deserialize": false,
 *         },
 *         "update_image": {
 *             "method": "POST",
 *             "path": "/v3/causes/{uuid}/image",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "defaults": {"_api_receive": false},
 *             "controller": "App\Controller\Api\Coalition\CauseController::updateImage",
 *         },
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"coalition.uuid": "exact"})
 * @ApiFilter(OrTextSearchFilter::class, properties={"name"})
 * @ApiFilter(OrderFilter::class, properties={"createdAt", "followersCount"})
 * @ApiFilter(MyCausesFilter::class)
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="cause_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="cause_name_unique", columns="name")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\Coalition\CauseRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="author",
 *         joinColumns={
 *             @ORM\JoinColumn(onDelete="SET NULL")
 *         }
 *     )
 * })
 *
 * @UniqueEntity(fields={"name"})
 *
 * @Assert\Expression("this.getSecondCoalition() === null || this.getSecondCoalition() !== this.getCoalition()", message="Veuillez choisir une autre coalition en tant que secondaire.")
 */
class Cause implements ExposedImageOwnerInterface, AuthoredInterface, FollowedInterface, AuthorInterface
{
    use EntityNameSlugTrait;
    use EntityIdentityTrait;
    use TimestampableEntity;
    use ImageTrait;
    use EntityFollowersTrait;
    use StaticSegmentTrait;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REFUSED = 'refused';
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REFUSED,
    ];

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png"}
     * )
     */
    protected $image;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"cause_read", "cause_write", "event_read", "event_list_read"})
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @SymfonySerializer\Groups({"cause_read", "cause_write", "cause_update"})
     *
     * @Assert\Length(max=10000)
     */
    private $description;

    /**
     * @var Collection|CauseFollower[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Coalition\CauseFollower", mappedBy="cause", fetch="EXTRA_LAZY", cascade={"all"})
     */
    private $followers;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="causes", fetch="EAGER")
     *
     * @SymfonySerializer\Groups({"cause_read"})
     *
     * @Assert\NotBlank
     * @AssertEnabledCoalitionUser
     */
    private $author;

    /**
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="causes", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     *
     * @SymfonySerializer\Groups({"cause_read", "cause_write", "event_read", "event_list_read"})
     *
     * @Assert\NotBlank
     */
    private $coalition;

    /**
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition")
     *
     * @SymfonySerializer\Groups({"cause_read", "cause_write"})
     */
    private $secondCoalition;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @SymfonySerializer\Groups({"cause_read", "coalition_read"})
     */
    protected $followersCount;

    /**
     * @var QuickAction[]|Collection
     *
     * @ApiSubresource
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Coalition\QuickAction", mappedBy="cause", fetch="EXTRA_LAZY", orphanRemoval=true, cascade={"all"})
     *
     * @SymfonySerializer\Groups({"cause_update"})
     *
     * @Assert\Valid
     */
    private $quickActions;

    /**
     * @var CauseEvent[]|Collection
     *
     * @ApiSubresource
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Event\CauseEvent",
     *     mappedBy="cause",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $events;

    public function __construct(UuidInterface $uuid = null, int $followersCount = 0)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->followersCount = $followersCount;

        $this->followers = new ArrayCollection();
        $this->quickActions = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getImagePath(): string
    {
        return $this->imageName ? sprintf('images/causes/%s', $this->getImageName()) : '';
    }

    public function getCoalition(): ?Coalition
    {
        return $this->coalition;
    }

    public function setCoalition(?Coalition $coalition): void
    {
        $this->coalition = $coalition;
    }

    public function getSecondCoalition(): ?Coalition
    {
        return $this->secondCoalition;
    }

    public function setSecondCoalition(?Coalition $secondCoalition): void
    {
        $this->secondCoalition = $secondCoalition;
    }

    public function createFollower(Adherent $adherent): FollowerInterface
    {
        return new CauseFollower($this, $adherent);
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $adherent): void
    {
        $this->author = $adherent;
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
     * @SymfonySerializer\Groups({"cause_read", "coalition_read"})
     */
    public function getFollowersCount(): int
    {
        return $this->followersCount;
    }

    public function setFollowersCount(int $count): void
    {
        $this->followersCount = $count;
    }

    public function getQuickActions(): Collection
    {
        return $this->quickActions;
    }

    public function addQuickAction(QuickAction $quickAction): void
    {
        if (!$this->quickActions->contains($quickAction) && !$quickAction->getCause()) {
            $quickAction->setCause($this);
            $this->quickActions->add($quickAction);
        }
    }

    public function removeQuickAction(QuickAction $quickAction): void
    {
        $this->quickActions->removeElement($quickAction);
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(CauseEvent $event): void
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }
    }

    public function removeEvent(CauseEvent $event): void
    {
        $this->events->removeElement($event);
    }

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
    }

    public function refuse(): void
    {
        $this->status = self::STATUS_REFUSED;
    }

    public function refreshFollowersCount(): void
    {
        $this->followersCount = $this->followers->count();
    }

    public function isApproved(): bool
    {
        return self::STATUS_APPROVED === $this->status;
    }

    public function isRefused(): bool
    {
        return self::STATUS_REFUSED === $this->status;
    }
}
