<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use App\Entity\Committee;
use App\Entity\EnabledInterface;
use App\Entity\EntityNameSlugTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Report\ReportableInterface;
use App\Filter\ContributorsCountFilter;
use App\Filter\IdeaStatusFilter;
use App\Filter\OrTextSearchFilter;
use App\Filter\SearchFilter;
use App\Report\ReportType;
use App\Validator\CommitteeMember;
use App\Validator\MandatoryQuestion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {
 *             "path": "/ideas-workshop/ideas",
 *             "method": "GET",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "status",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The status of the Idea resource.",
 *                         "enum": IdeaStatusEnum::ALL_STATUSES,
 *                         "example": IdeaStatusEnum::PENDING,
 *                     },
 *                     {
 *                         "name": "name",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The name of the Idea resource.",
 *                         "example": "écologie",
 *                     },
 *                     {
 *                         "name": "theme.name",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The theme name of the Idea resource.",
 *                         "example": "armée",
 *                     },
 *                     {
 *                         "name": "author_category",
 *                         "in": "query",
 *                         "type": "uuid",
 *                         "description": "The author category of the Idea resource.",
 *                         "enum": AuthorCategoryEnum::ALL_CATEGORIES,
 *                         "example": AuthorCategoryEnum::ADHERENT,
 *                     },
 *                     {
 *                         "name": "author.uuid",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The author uuid of the Idea resource.",
 *                         "example": "a046adbe-9c7b-56a9-a676-6151a6785dda",
 *                     },
 *                 }
 *             }
 *         },
 *         "get_my_contributions": {
 *             "method": "GET",
 *             "path": "/ideas-workshop/ideas/my-contributions",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "normalization_context": {"groups": {"idea_list_read"}}
 *         },
 *         "post": {
 *             "path": "/ideas-workshop/ideas",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "normalization_context": {"groups": {"idea_list_read", "idea_with_answers"}}
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/ideas-workshop/ideas/{id}",
 *             "method": "GET",
 *             "normalization_context": {"groups": {"idea_read"}},
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Idea resource.",
 *                         "example": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
 *                     }
 *                 }
 *             }
 *         },
 *         "put": {
 *             "path": "/ideas-workshop/ideas/{id}",
 *             "access_control": "object.getAuthor() == user",
 *             "normalization_context": {"groups": {"idea_list_read", "idea_with_answers"}},
 *         },
 *         "publish": {
 *             "method": "PUT",
 *             "denormalization_context": {"api_allow_update": false},
 *             "access_control": "object.getAuthor() == user",
 *             "path": "/ideas-workshop/ideas/{id}/publish",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\IdeasWorkshop\IdeaController::publish",
 *             "normalization_context": {"groups": {"idea_list_read"}},
 *             "validation_groups": {"idea_publish"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Idea resource.",
 *                         "example": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
 *                     }
 *                 }
 *             }
 *         },
 *         "extend": {
 *             "method": "PUT",
 *             "denormalization_context": {"api_allow_update": false},
 *             "access_control": "object.getAuthor() == user",
 *             "path": "/ideas-workshop/ideas/{id}/extend",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\IdeasWorkshop\IdeaController::extend",
 *             "normalization_context": {"groups": {"idea_read"}},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Idea resource.",
 *                         "example": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
 *                     }
 *                 }
 *             }
 *         },
 *         "delete": {
 *             "path": "/ideas-workshop/ideas/{id}",
 *             "access_control": "object.getAuthor() == user",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Idea resource.",
 *                         "example": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     attributes={
 *         "normalization_context": {"groups": {"idea_list_read"}},
 *         "denormalization_context": {
 *             "groups": {"idea_write"}
 *         },
 *         "order": {"createdAt": "ASC"},
 *         "filters": {ContributorsCountFilter::class, IdeaStatusFilter::class}
 *     },
 *     subresourceOperations={
 *         "votes_get_subresource": {
 *             "method": "GET",
 *             "path": "/ideas-workshop/ideas/{id}/votes"
 *         },
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "ipartial",
 *     "themes.name": "iexact",
 *     "authorCategory": "iexact",
 *     "author.uuid": "exact",
 *     "category.name": "iexact",
 *     "needs.name": "iexact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"publishedAt", "votesCount", "commentsCount"})
 * @ApiFilter(OrTextSearchFilter::class, properties={"name"})
 *
 * @ORM\Entity(repositoryClass="App\Repository\IdeasWorkshop\IdeaRepository")
 *
 * @ORM\Table(
 *     name="ideas_workshop_idea",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idea_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="idea_slug_unique", columns="slug")
 *     },
 *     indexes={
 *         @ORM\Index(name="idea_workshop_author_category_idx", columns={"author_category"})
 *     }
 * )
 *
 * @UniqueEntity("slug")
 */
class Idea implements AuthorInterface, ReportableInterface, EnabledInterface
{
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    public const EXTEND_COUNT_LIMIT = 2;
    private const PUBLISHED_INTERVAL = 'P10D';

    /**
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ApiProperty(identifier=true)
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "my_committees", "idea_thread_comment_read"})
     */
    protected $uuid;

    /**
     * @ORM\Column
     *
     * @Assert\Length(max=120, groups={"Admin"})
     * @Assert\NotBlank(message="idea.name.not_blank", groups={"Admin"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read", "idea_vote_read"})
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Theme")
     * @ORM\JoinTable(name="ideas_workshop_ideas_themes")
     *
     * @Assert\Count(min=1, minMessage="idea.theme.min_count", groups={"idea_publish", "Admin"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $themes;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     *
     * @Assert\NotBlank(message="idea.category.not_blank", groups={"idea_publish", "Admin"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="Need")
     * @ORM\JoinTable(name="ideas_workshop_ideas_needs")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $needs;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="ideas")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\NotNull(message="idea.author.not_null", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $author;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotNull(message="idea.published_at.not_null", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $publishedAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_read"})
     */
    private $finalizedAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastContributionNotificationDate;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $enabled;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     *
     * @CommitteeMember
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     */
    private $committee;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="idea", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     *
     * @MandatoryQuestion(groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_write", "idea_read", "idea_with_answers"})
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="idea", cascade={"remove"}, orphanRemoval=true)
     *
     * @ApiSubresource
     */
    private $votes;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_read"})
     */
    private $votesCount = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     *
     * @SymfonySerializer\Groups({"idea_list_read"})
     */
    private $commentsCount = 0;

    /**
     * @ORM\Column(length=9)
     *
     * @Assert\Choice(choices=AuthorCategoryEnum::ALL_CATEGORIES, strict=true)
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "enum": AuthorCategoryEnum::ALL_CATEGORIES,
     *             "example": AuthorCategoryEnum::ADHERENT
     *         }
     *     }
     * )
     */
    private $authorCategory;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(message="idea.description.not_blank", groups={"idea_publish", "Admin"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $extensionsCount;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $lastExtensionDate;

    public function __construct(
        string $name,
        string $description = null,
        string $authorCategory = AuthorCategoryEnum::ADHERENT,
        \DateTimeInterface $publishedAt = null,
        \DateTimeInterface $finalizedAt = null,
        bool $enabled = true,
        Adherent $author = null,
        UuidInterface $uuid = null,
        \DateTimeInterface $createdAt = null,
        int $extensionsCount = 0,
        \DateTimeInterface $lastExtensionDate = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->setName($name);
        $this->description = $description;
        $this->authorCategory = $authorCategory;
        $this->publishedAt = $publishedAt;
        $this->finalizedAt = $finalizedAt;
        $this->enabled = $enabled;
        $this->needs = new ArrayCollection();
        $this->themes = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->createdAt = $createdAt ?: new \DateTime();
        $this->extensionsCount = $extensionsCount;
        $this->lastExtensionDate = $lastExtensionDate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): void
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }
    }

    public function removeTheme(Theme $theme): void
    {
        $this->themes->removeElement($theme);
    }

    public function getNeeds(): Collection
    {
        return $this->needs;
    }

    public function addNeed(Need $need): void
    {
        if (!$this->needs->contains($need)) {
            $this->needs->add($need);
        }
    }

    public function removeNeed(Need $need): void
    {
        $this->needs->removeElement($need);
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeInterface $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getFinalizedAt(): ?\DateTimeInterface
    {
        return $this->finalizedAt;
    }

    public function setFinalizedAt(\DateTimeInterface $finalizedAt): void
    {
        $this->finalizedAt = $finalizedAt;
    }

    public function getLastContributionNotificationDate(): ?\DateTimeInterface
    {
        return $this->lastContributionNotificationDate;
    }

    public function setLastContributionNotificationDate(\DateTimeInterface $lastContributionNotificationDate): void
    {
        $this->lastContributionNotificationDate = $lastContributionNotificationDate;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }

    /**
     * @SymfonySerializer\Groups({"idea_list_read", "idea_read"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "enum": IdeaStatusEnum::ALL_STATUSES,
     *             "example": IdeaStatusEnum::DRAFT
     *         }
     *     }
     * )
     */
    public function getStatus(): string
    {
        if (!$this->isEnabled()) {
            return IdeaStatusEnum::UNPUBLISHED;
        }

        if ($this->isDraft()) {
            return IdeaStatusEnum::DRAFT;
        }

        if ($this->isPending()) {
            return IdeaStatusEnum::PENDING;
        }

        return IdeaStatusEnum::FINALIZED;
    }

    public function addAnswer(Answer $answer): void
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setIdea($this);
        }
    }

    public function removeAnswer(Answer $answer): void
    {
        $this->answers->removeElement($answer);
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addVote(Vote $vote): void
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setIdea($this);
        }
    }

    public function removeVote(Vote $vote): void
    {
        $this->votes->removeElement($vote);
        $this->decrementVotesCount();
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function setCanonicalName(string $name): void
    {
        $this->canonicalName = static::canonicalize($name);
    }

    public function getCanonicalName(): string
    {
        return $this->canonicalName;
    }

    /**
     * @SymfonySerializer\Groups("idea_list_read")
     */
    public function getDaysBeforeDeadline(): int
    {
        if (!$this->isPending()) {
            return 0;
        }

        return $this->finalizedAt->diff(new \DateTime('now'))->d;
    }

    /**
     * @SymfonySerializer\Groups("idea_list_read")
     */
    public function getHoursBeforeDeadline(): int
    {
        if (!$this->isPending()) {
            return 0;
        }

        return $this->finalizedAt->diff(new \DateTime('now'))->h;
    }

    public function isDraft(): bool
    {
        return null === $this->publishedAt;
    }

    public function isPending(): bool
    {
        return !$this->isDraft() && new \DateTime('now') < $this->finalizedAt;
    }

    public function isFinalized(): bool
    {
        return !$this->isDraft() && new \DateTime('now') >= $this->finalizedAt;
    }

    public function isUnpublished(): bool
    {
        return !$this->enabled;
    }

    public function publish(): void
    {
        $this->publishedAt = $now = new \DateTimeImmutable();
        $this->finalizedAt = $now->add(new \DateInterval(self::PUBLISHED_INTERVAL));
    }

    public function extend(): void
    {
        $this->incrementExtensionsCount();
        $this->lastExtensionDate = new \DateTime();
        $this->publish();
    }

    public function finalize(): void
    {
        $this->finalizedAt = new \DateTime();
    }

    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    public function getAuthorCategory(): string
    {
        return $this->authorCategory;
    }

    public function setAuthorCategory(string $authorCategory): void
    {
        $this->authorCategory = $authorCategory;
    }

    public function getVotesCount(): int
    {
        return $this->votesCount;
    }

    public function incrementVotesCount(int $increment = 1): void
    {
        $this->votesCount += $increment;
    }

    public function decrementVotesCount(int $increment = 1): void
    {
        $this->votesCount -= $increment;
    }

    public function getCommentsCount(): int
    {
        return $this->commentsCount;
    }

    public function incrementCommentsCount(int $increment = 1): void
    {
        $this->commentsCount += $increment;
    }

    public function decrementCommentsCount(int $increment = 1): void
    {
        $this->commentsCount -= $increment;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getReportType(): string
    {
        return ReportType::IDEAS_WORKSHOP_IDEA;
    }

    /**
     * @SymfonySerializer\Groups("idea_read")
     */
    public function isExtendable(): bool
    {
        return !$this->isDraft() && $this->extensionsCount < self::EXTEND_COUNT_LIMIT;
    }

    public function getExtensionsCount(): int
    {
        return $this->extensionsCount;
    }

    public function incrementExtensionsCount(int $increment = 1): void
    {
        $this->extensionsCount += $increment;
    }

    public function getLastExtensionDate(): ?\DateTimeInterface
    {
        return $this->lastExtensionDate;
    }

    public function setLastExtensionDate(\DateTimeInterface $lastExtensionDate): void
    {
        $this->lastExtensionDate = $lastExtensionDate;
    }

    public function __toString(): string
    {
        return substr($this->getName(), 0, 300);
    }
}
