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
 *             "groups": {"idea_category_read"}
 *         },
 *         "order": {"name": "ASC"},
 *         "pagination_enabled": false,
 *     },
 *     collectionOperations={"get": {"path": "/ideas-workshop/categories"}},
 *     itemOperations={"get": {"path": "/ideas-workshop/categories/{id}"}},
 * )
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="ideas_workshop_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="category_name_unique", columns="name")
 *     }
 * )
 *
 * @UniqueEntity("name")
 */
class Category implements EnabledInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @SymfonySerializer\Groups({"idea_category_read", "idea_read", "idea_list_read"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"idea_category_read", "idea_list_read"})
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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
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
