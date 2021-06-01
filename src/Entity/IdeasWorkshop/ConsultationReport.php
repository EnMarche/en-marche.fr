<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get": {"method": "GET", "path": "/ideas-workshop/consultation_reports"}},
 *     itemOperations={"get": {"method": "GET", "path": "/ideas-workshop/consultation_reports/{id}"}},
 *     attributes={
 *         "normalization_context": {"groups": {"idea_consultation_report_read"}},
 *         "order": {"position": "ASC"}
 *     }
 * )
 *
 * @ORM\Table(name="ideas_workshop_consultation_report")
 * @ORM\Entity
 */
class ConsultationReport
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Assert\Url
     *
     * @SymfonySerializer\Groups("idea_consultation_report_read")
     * @ORM\Column
     */
    private $url;

    /**
     * @Assert\GreaterThanOrEqual(0)
     *
     * @Gedmo\SortablePosition
     *
     * @SymfonySerializer\Groups("idea_consultation_report_read")
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $position = 0;

    /**
     * @SymfonySerializer\Groups("idea_consultation_report_read")
     * @ORM\Column
     */
    private $name;

    public function __construct(string $name = null, string $url = null, int $position = 1)
    {
        $this->name = $name;
        $this->url = $url;
        $this->position = $position;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }
}
