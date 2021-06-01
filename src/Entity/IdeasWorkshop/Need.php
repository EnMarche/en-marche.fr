<?php

namespace App\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EnabledInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"idea_need_read"}
 *         },
 *         "order": {"name": "ASC"},
 *         "pagination_enabled": false,
 *     },
 *     collectionOperations={"get": {"path": "/ideas-workshop/needs"}},
 *     itemOperations={"get": {"path": "/ideas-workshop/needs/{id}"}},
 * )
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="ideas_workshop_need",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="need_name_unique", columns="name")
 *     }
 * )
 *
 * @UniqueEntity("name")
 */
class Need implements EnabledInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @SymfonySerializer\Groups({"idea_need_read", "idea_read", "idea_list_read"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"idea_need_read", "idea_list_read"})
     */
    protected $name;

    /**
     * @var bool
     *
     * @SymfonySerializer\Groups("idea_list_read")
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    public function __construct(string $name = null, bool $enabled = false)
    {
        $this->name = $name;
        $this->enabled = $enabled;
    }

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }
}
