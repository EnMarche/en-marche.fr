<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacebookVideoRepository")
 * @ORM\Table(name="facebook_videos")
 */
class FacebookVideo
{
    use EntityTimestampableTrait;
    use EntityPublishableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column
     *
     * @Assert\Length(max=255)
     * @Assert\NotBlank
     * @Assert\Url
     */
    private $facebookUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     * @Assert\Url
     */
    private $twitterUrl;

    /**
     * @ORM\Column(length=255)
     *
     * @Assert\Length(max=255)
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\Length(max=100)
     * @Assert\NotBlank
     */
    private $author;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    public function __construct()
    {
        $this->position = 1;
        $this->published = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl)
    {
        $this->facebookUrl = $facebookUrl;
    }

    public function getTwitterUrl(): ?string
    {
        return $this->twitterUrl;
    }

    public function setTwitterUrl(?string $twitterUrl)
    {
        $this->twitterUrl = $twitterUrl;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author)
    {
        $this->author = $author;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position)
    {
        $this->position = $position;
    }
}
