<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EnabledInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get": {"method": "GET", "path": "/ideas-workshop/consultations"}},
 *     itemOperations={},
 *     attributes={
 *         "normalization_context": {"groups": {"idea_consultation_list_read"}},
 *         "order": {"startedAt": "ASC"}
 *     }
 * )
 *
 * @ORM\Table(
 *     name="ideas_workshop_consultation",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="consultation_enabled_unique", columns="enabled")
 *     },
 * )
 * @ORM\Entity
 *
 * @UniqueEntity("enabled")
 */
class Consultation implements EnabledInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Response time in minutes
     *
     * @var int
     *
     * @Assert\GreaterThanOrEqual(0)
     *
     * @SymfonySerializer\Groups("idea_consultation_list_read")
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $responseTime;

    /**
     * @SymfonySerializer\Groups("idea_consultation_list_read")
     * @ORM\Column(type="datetime")
     */
    private $startedAt;

    /**
     * @SymfonySerializer\Groups("idea_consultation_list_read")
     * @ORM\Column(type="datetime")
     */
    private $endedAt;

    /**
     * @Assert\Url
     *
     * @SymfonySerializer\Groups("idea_consultation_list_read")
     * @ORM\Column
     */
    private $url;

    /**
     * @SymfonySerializer\Groups("idea_consultation_list_read")
     * @ORM\Column
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enabled;

    public function __construct(
        string $name = null,
        string $url = null,
        \DateTime $startedAt = null,
        \DateTime $endedAt = null,
        int $responseTime = 0,
        bool $enabled = null
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
        $this->responseTime = $responseTime;
        $this->enabled = $enabled;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponseTime(): int
    {
        return $this->responseTime;
    }

    public function setResponseTime(int $responseTime): void
    {
        $this->responseTime = $responseTime;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTime $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getEndedAt(): ?\DateTime
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTime $endedAt): void
    {
        $this->endedAt = $endedAt;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isEnabled(): bool
    {
        return true === $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled ?: null;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }
}
