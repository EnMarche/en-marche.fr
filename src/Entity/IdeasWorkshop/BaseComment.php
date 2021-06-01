<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Adherent;
use App\Entity\EnabledInterface;
use App\Entity\EntitySoftDeletableTrait;
use App\Entity\EntitySoftDeletedInterface;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 *
 * @Assert\Expression(
 *     expression="this.getAuthor().isCommentsCguAccepted()",
 *     message="Vous devez cocher la case CGU afin de poster un commentaire.",
 *     groups={"write"}
 * )
 */
abstract class BaseComment implements EnabledInterface, EntitySoftDeletedInterface
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;

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
     * @SymfonySerializer\Groups({"idea_thread_comment_read", "idea_thread_list_read", "idea_read"})
     */
    protected $uuid;

    /**
     * @ORM\Column(type="text")
     *
     * @SymfonySerializer\Groups({"idea_thread_comment_read", "idea_thread_list_read", "idea_read"})
     *
     * @Assert\NotBlank
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @SymfonySerializer\Groups({"idea_thread_comment_read", "idea_thread_list_read", "idea_read"})
     */
    protected $author;

    /**
     * @ORM\Column(type="boolean")
     *
     * @SymfonySerializer\Groups({"idea_thread_comment_read", "idea_thread_list_read", "idea_thread_approval", "idea_thread_comment_approval", "idea_read"})
     */
    protected $approved = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    protected $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function __toString()
    {
        return substr($this->getContent(), 0, 300);
    }
}
