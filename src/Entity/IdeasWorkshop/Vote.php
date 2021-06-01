<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"idea_vote_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"idea_vote_write"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/ideas-workshop/votes",
 *         },
 *         "post": {
 *             "path": "/ideas-workshop/votes",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *         }
 *     },
 *     itemOperations={
 *         "get": {"path": "/ideas-workshop/votes/{id}"},
 *         "delete": {
 *             "path": "/ideas-workshop/votes/{id}",
 *             "access_control": "object.getAuthor() == user"
 *         }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\VoteRepository")
 * @ORM\Table(name="ideas_workshop_vote")
 * @ORM\EntityListeners({"App\EntityListener\VoteListener"})
 *
 * @UniqueEntity(fields={"idea", "author", "type"})
 */
class Vote implements AuthorInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_vote_read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Idea", inversedBy="votes")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_vote_read", "idea_vote_write"})
     */
    private $idea;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_vote_read", "idea_vote_write"})
     */
    private $author;

    /**
     * @ORM\Column(length=10)
     *
     * @Assert\Choice(callback={"App\Entity\IdeasWorkshop\VoteTypeEnum", "toArray"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_vote_read", "idea_vote_write"})
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdea(): Idea
    {
        return $this->idea;
    }

    public function setIdea(Idea $idea): void
    {
        $this->idea = $idea;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
