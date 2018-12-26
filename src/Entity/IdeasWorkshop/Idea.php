<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthorInterface;
use AppBundle\Entity\Committee;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntityNameSlugTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Report\ReportType;
use AppBundle\Entity\VisibleStatusesInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {"method": "GET"},
 *         "get_my_contributions": {
 *             "method": "GET",
 *             "path": "/ideas/my-contributions",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "normalization_context": {"groups": {"idea_list_read"}}
 *         },
 *         "post": {
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *         }
 *     },
 *     itemOperations={
 *         "get": {"method": "GET"},
 *         "put": {"access_control": "object.getAuthor() == user"}
 *     },
 *     attributes={
 *         "normalization_context": {"groups": {"idea_list_read"}},
 *         "denormalization_context": {
 *             "groups": {"idea_write"}
 *         },
 *         "order": {"createdAt": "ASC"}
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"status": "exact", "name": "partial", "theme": "exact", "author_category": "exact", "author.uuid": "exact"})
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IdeaRepository")
 *
 * @ORM\Table(
 *     name="ideas_workshop_idea",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idea_slug_unique", columns="slug")
 *     },
 *     indexes={
 *         @ORM\Index(name="idea_workshop_status_idx", columns={"status"}),
 *         @ORM\Index(name="idea_workshop_author_category_idx", columns={"author_category"})
 *     }
 * )
 *
 * @UniqueEntity("slug")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Idea implements AuthorInterface, ReportableInterface, VisibleStatusesInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    private const PUBLISHED_INTERVAL = 'P21D';

    /**
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "vote_read"})
     */
    protected $name;

    /**
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\ManyToOne(targetEntity="Theme")
     */
    private $theme;

    /**
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\ManyToMany(targetEntity="Need")
     * @ORM\JoinTable(name="ideas_workshop_ideas_needs")
     */
    private $needs;

    /**
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="ideas")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @var Committee
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     */
    private $committee;

    /**
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum", "toArray"},
     *     strict=true,
     * )
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\Column(length=11, options={"default": IdeaStatusEnum::DRAFT})
     */
    private $status;

    /**
     * @SymfonySerializer\Groups("idea_write")
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="idea", cascade={"all"}, orphanRemoval=true)
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="idea")
     *
     * @ApiSubresource
     */
    private $votes;

    /**
     * @SymfonySerializer\Groups("idea_list_read")
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $votesCount = 0;

    /**
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\IdeasWorkshop\AuthorCategoryEnum", "toArray"},
     *     strict=true,
     * )
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\Column(length=9)
     */
    private $authorCategory;

    /**
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct(
        string $name,
        string $description = null,
        string $authorCategory = AuthorCategoryEnum::ADHERENT,
        \DateTime $publishedAt = null,
        string $status = IdeaStatusEnum::DRAFT,
        Adherent $author = null,
        UuidInterface $uuid = null,
        \DateTime $createdAt = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->setName($name);
        $this->description = $description;
        $this->authorCategory = $authorCategory;
        $this->publishedAt = $publishedAt;
        $this->status = $status;
        $this->needs = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->createdAt = $createdAt ?: new \DateTime();
    }

    public static function getVisibleStatuses(): array
    {
        return IdeaStatusEnum::VISIBLE_STATUSES;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): void
    {
        $this->theme = $theme;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
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

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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

    /**
     * @SymfonySerializer\Groups("idea_list_read")
     */
    public function getDaysBeforeDeadline(): int
    {
        $deadline = $this->createdAt->add(new \DateInterval(self::PUBLISHED_INTERVAL));
        $now = new \DateTime();

        return $deadline <= $now ? 0 : $deadline->diff($now)->d;
    }

    public function isDraft(): bool
    {
        return IdeaStatusEnum::DRAFT === $this->status;
    }

    public function isPending(): bool
    {
        return IdeaStatusEnum::PENDING === $this->status;
    }

    public function isFinalized(): bool
    {
        return IdeaStatusEnum::FINALIZED === $this->status;
    }

    public function isUnpublished(): bool
    {
        return IdeaStatusEnum::UNPUBLISHED === $this->status;
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
}
