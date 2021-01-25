<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\UserDocument;
use App\Entity\UserDocumentInterface;
use App\Entity\UserDocumentTrait;
use App\Validator\WysiwygLength as AssertWysiwygLength;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {"path": "/ideas-workshop/answers"},
 *         "post": {
 *             "path": "/ideas-workshop/answers",
 *             "access_control": "is_granted('ROLE_ADHERENT')"
 *         }
 *     },
 *     itemOperations={
 *         "get": {"path": "/ideas-workshop/answers/{id}"},
 *         "put": {
 *             "path": "/ideas-workshop/answers/{id}",
 *             "access_control": "object.getAuthor() == user"
 *         }
 *     }
 * )
 *
 * @ORM\Table(name="ideas_workshop_answer")
 * @ORM\Entity
 */
class Answer implements UserDocumentInterface
{
    use UserDocumentTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @SymfonySerializer\Groups({"idea_thread_comment_read", "idea_read", "idea_with_answers", "idea_thread_list_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @AssertWysiwygLength(max=1700)
     *
     * @SymfonySerializer\Groups({"idea_write", "idea_publish", "idea_read", "idea_with_answers"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumn(nullable=false)
     *
     * @SymfonySerializer\Groups({"idea_write", "idea_publish", "idea_read", "idea_with_answers"})
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="answer", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt": "ASC"})
     *
     * @SymfonySerializer\Groups({"idea_read"})
     */
    private $threads;

    /**
     * @ORM\ManyToOne(targetEntity="Idea", inversedBy="answers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $idea;

    /**
     * @var UserDocument[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\UserDocument", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinTable(
     *     name="ideas_workshop_answer_user_documents",
     *     joinColumns={
     *         @ORM\JoinColumn(name="ideas_workshop_answer_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="user_document_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    protected $documents;

    public function __construct(string $content)
    {
        $this->content = $content;
        $this->threads = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    public function addThread(Thread $thread): void
    {
        if (!$this->threads->contains($thread)) {
            $this->threads->add($thread);
            $thread->setAnswer($this);
        }
    }

    public function removeThread(Thread $thread): void
    {
        $this->threads->removeElement($thread);
    }

    public function getThreads(): Collection
    {
        return $this->threads;
    }

    public function getIdea(): Idea
    {
        return $this->idea;
    }

    public function setIdea(Idea $idea): void
    {
        $this->idea = $idea;
    }
}
