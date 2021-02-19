<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use App\Entity\Report\ReportableInterface;
use App\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_items_per_page": 3,
 *         "normalization_context": {
 *             "groups": {"idea_thread_comment_read"}
 *         },
 *         "order": {"createdAt": "ASC"},
 *         "filters": {"thread.answer"}
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/ideas-workshop/threads",
 *             "normalization_context": {
 *                 "groups": {"idea_thread_list_read"}
 *             }
 *         },
 *         "post": {
 *             "path": "/ideas-workshop/threads",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "validation_groups": {"Default", "write"}
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/ideas-workshop/threads/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         },
 *         "approve": {
 *             "method": "PUT",
 *             "path": "/ideas-workshop/threads/{id}/approve",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "object.getIdeaAuthor() == user",
 *             "controller": "App\Controller\Api\IdeasWorkshop\ApproveThreadController::approve",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         },
 *         "disapprove": {
 *             "method": "PUT",
 *             "path": "/ideas-workshop/threads/{id}/disapprove",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "object.getIdeaAuthor() == user",
 *             "controller": "App\Controller\Api\IdeasWorkshop\ApproveThreadController::disapprove",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         },
 *         "put_approval_toggle": {
 *             "method": "PUT",
 *             "path": "/ideas-workshop/threads/{id}/approval-toggle",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "denormalization_context": {
 *                 "groups": {"idea_thread_approval"}
 *             },
 *             "access_control": "object.getIdeaAuthor() == user",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         },
 *         "delete": {
 *             "path": "/ideas-workshop/threads/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "object.getAuthor() == user",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     subresourceOperations={
 *         "comments_get_subresource": {
 *             "method": "GET",
 *             "path": "/ideas-workshop/threads/{id}/comments"
 *         },
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"answer.id": "exact"})
 *
 * @ORM\Table(
 *     name="ideas_workshop_thread",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="threads_uuid_unique", columns="uuid")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ThreadRepository")
 * @ORM\EntityListeners({"App\EntityListener\IdeaThreadListener"})
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Thread extends BaseComment implements AuthorInterface, ReportableInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="Answer", inversedBy="threads")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups({"idea_thread_comment_read", "idea_thread_list_read"})
     */
    private $answer;

    /**
     * @ApiSubresource
     *
     * @ORM\OneToMany(targetEntity="ThreadComment", mappedBy="thread", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt": "DESC"})
     *
     * @SymfonySerializer\Groups({"idea_thread_list_read", "idea_read"})
     */
    private $comments;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
    }

    public static function create(
        UuidInterface $uuid,
        string $content,
        Adherent $author,
        Answer $answer,
        \DateTime $createdAt = null,
        bool $approved = false,
        bool $enabled = true
    ): self {
        $thread = new self($uuid);
        $thread->content = $content;
        $thread->author = $author;
        $thread->answer = $answer;
        $thread->createdAt = $createdAt ?: new \DateTime();
        $thread->approved = $approved;
        $thread->enabled = $enabled;

        return $thread;
    }

    public function __toString()
    {
        return (string) $this->content;
    }

    public function getAnswer(): Answer
    {
        return $this->answer;
    }

    public function setAnswer(Answer $answer): void
    {
        $this->answer = $answer;
    }

    public function addComment(ThreadComment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setThread($this);
        }
    }

    public function removeComment(ThreadComment $comment): void
    {
        $this->comments->removeElement($comment);
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getReportType(): string
    {
        return ReportType::IDEAS_WORKSHOP_THREAD;
    }

    public function getIdeaAuthor(): Adherent
    {
        return $this->getAnswer()->getIdea()->getAuthor();
    }

    public function getIdea(): Idea
    {
        return $this->getAnswer()->getIdea();
    }

    public function getContributors(): ArrayCollection
    {
        $contributors = new ArrayCollection();

        foreach ($this->comments as $comment) {
            if (!$contributors->contains($comment->getAuthor())) {
                $contributors->add($comment->getAuthor());
            }
        }

        return $contributors;
    }

    public function countEnabledComment(): int
    {
        return $this->comments
            ->filter(function (ThreadComment $comment) {
                return $comment->isEnabled();
            })
            ->count()
        ;
    }
}
