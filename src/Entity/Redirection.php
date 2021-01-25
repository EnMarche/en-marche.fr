<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="redirections")
 * @ORM\Entity(repositoryClass="App\Repository\RedirectionRepository")
 * @ORM\EntityListeners({"App\EntityListener\RedirectionListener"})
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Redirection
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url_from")
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Serializer\Expose
     */
    private $from;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url_to")
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Serializer\Expose
     */
    private $to;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={301, 302})
     *
     * @Serializer\Expose
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __toString()
    {
        return 'Redirection from '.$this->from.' to '.$this->to;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from)
    {
        $this->from = $from;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(?string $to)
    {
        $this->to = $to;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type)
    {
        $this->type = $type;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
