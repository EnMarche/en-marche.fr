<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Adherent;
use App\Entity\EntityFollowersTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\Event\CoalitionEvent;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\FollowedInterface;
use App\Entity\FollowerInterface;
use App\Entity\ImageTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_enabled": false,
 *         "order": {"name": "ASC"},
 *         "normalization_context": {"groups": {"coalition_read", "image_owner_exposed"}},
 *     },
 *     collectionOperations={
 *         "get": {"path": "/coalitions"},
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/coalitions/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"}
 *         },
 *         "follow": {
 *             "method": "PUT|DELETE",
 *             "path": "/v3/coalitions/{uuid}/follower",
 *             "denormalization_context": {"api_allow_update": false},
 *             "controller": "App\Controller\Api\FollowController::follower",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "deserialize": false,
 *         },
 *     },
 * )
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="coalition_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="coalition_name_unique", columns="name")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\Coalition\CoalitionRepository")
 */
class Coalition implements ExposedImageOwnerInterface, FollowedInterface
{
    use EntityIdentityTrait;
    use TimestampableEntity;
    use ImageTrait;
    use EntityFollowersTrait;

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
     * @var string
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"coalition_read", "cause_read", "event_read", "event_list_read"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @SymfonySerializer\Groups({"coalition_read"})
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(length=11, nullable=true)
     *
     * @Assert\Regex(pattern="/^[A-Za-z0-9_-]{2,11}$/", message="coalition.youtubeid_syntax")
     *
     * @SymfonySerializer\Groups({"coalition_read"})
     */
    private $youtubeId;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $enabled;

    /**
     * @var CoalitionEvent[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Event\CoalitionEvent",
     *     mappedBy="coalition",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $events;

    /**
     * @var Collection|CoalitionFollower[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Coalition\CoalitionFollower", mappedBy="coalition", fetch="EXTRA_LAZY", cascade={"all"}, orphanRemoval=true)
     */
    private $followers;

    /**
     * @var Cause[]|Collection
     *
     * @ApiSubresource
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Coalition\Cause",
     *     mappedBy="coalition",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $causes;

    public function __construct(
        UuidInterface $uuid = null,
        string $name = null,
        string $description = null,
        string $youtubeId = null,
        bool $enabled = true
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
        $this->enabled = $enabled;
        $this->youtubeId = $youtubeId;

        $this->events = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->causes = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): void
    {
        $this->youtubeId = $youtubeId;
    }

    public function getImagePath(): string
    {
        return $this->imageName ? sprintf('images/coalitions/%s', $this->getImageName()) : '';
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(CoalitionEvent $event): void
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }
    }

    public function removeEvent(CoalitionEvent $event): void
    {
        $this->events->removeElement($event);
    }

    public function getCauses(): Collection
    {
        return $this->causes;
    }

    public function addCause(Cause $cause): void
    {
        if (!$this->causes->contains($cause)) {
            $cause->setCoalition($this);
            $this->causes->add($cause);
        }
    }

    public function removeCause(Cause $cause): void
    {
        $this->causes->removeElement($cause);
    }

    public function createFollower(Adherent $adherent): FollowerInterface
    {
        return new CoalitionFollower($this, $adherent);
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
