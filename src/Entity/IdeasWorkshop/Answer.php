<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ORM\Table(name="ideas_workshop_answer")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Answer implements GroupSequenceProviderInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(groups={"idea_post", "idea_put"})
     * @Assert\Length(max=1700, groups={"idea_post", "idea_put"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Question")
     *
     * @Assert\NotBlank
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="answer")
     */
    private $threads;

    /**
     * @ORM\ManyToOne(targetEntity="Idea", inversedBy="answers")
     */
    private $idea;

    public function __construct(
        string $content,
        Question $question
    ) {
        $this->content = $content;
        $this->question = $question;
        $this->threads = new ArrayCollection();
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
        }
    }

    public function removeThread(Thread $thread): void
    {
        $this->threads->removeElement($thread);
    }

    public function getThreads(): ArrayCollection
    {
        return $this->threads;
    }

    public function getIdea(): ?Idea
    {
        return $this->idea;
    }

    public function setIdea(Idea $idea): void
    {
        $this->idea = $idea;
    }

    public function getGroupSequence()
    {
        if ($this->getQuestion() && $this->getQuestion()->isRequired()) {
            return [['idea_post', 'idea_put']];
        } else {
            return ['Answer'];
        }
    }
}
