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
 *         "normalization_context": {"groups": {"coalition_read", "image_owner_exposed"}},
 *         "pagination_client_items_per_page": true,
 *         "order": {"name": "ASC"}
 *     },
 *     collectionOperations={
 *         "get": {"path": "/coalitions"},
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/coalitions/{id}",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "follow": {
 *             "method": "PUT",
 *             "path": "/v3/coalitions/{id}/follow",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "controller": "App\Controller\Api\Coalition\FollowController::follow",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "unfollow": {
 *             "method": "PUT",
 *             "path": "/v3/coalitions/{id}/unfollow",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "controller": "App\Controller\Api\Coalition\FollowController::unfollow",
 *             "requirements": {"id": "%pattern_uuid%"}
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
 * @ORM\Entity
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
     * @SymfonySerializer\Groups({"coalition_read", "cause_read"})
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
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $enabled = true;

    /**
     * @var CoalitionEvent[]|Collection
     *
     * @ApiSubresource
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\Event\CoalitionEvent",
     *     mappedBy="coalitions",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $events;

    /**
     * @var Collection|Adherent[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Adherent", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(
     *     name="coalition_follower",
     *     joinColumns={
     *         @ORM\JoinColumn(name="coalition_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="adherent_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
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
        bool $enabled = true
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
        $this->enabled = $enabled;

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

    public function getImagePath(): string
    {
        return $this->imageName ? \sprintf('images/coalitions/%s', $this->getImageName()) : '';
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

    /**
     * @SymfonySerializer\Groups({"coalition_read"})
     */
    public function getFollowersCount(): int
    {
        return $this->followers->count();
    }
}
