<?php

namespace App\Entity\EmailTemplate;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\AuthoredItemsCollectionInterface;
use App\Entity\AuthoredTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "access_control": "is_granted('ROLE_MESSAGE_REDACTOR')",
 *         "normalization_context": {
 *             "groups": {"email_template_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"email_template_write"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/email_templates",
 *             "normalization_context": {
 *                 "groups": {"email_template_list_read"}
 *             },
 *         },
 *         "post": {
 *             "path": "/v3/email_templates",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/email_templates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR') and object.getAuthor() == user",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Email Template resource.",
 *                         "example": "de7982c4-3729-4f9d-9587-376df25354c3",
 *                     },
 *                 },
 *             },
 *         },
 *         "put": {
 *             "path": "/v3/email_templates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR') and object.getAuthor() == user",
 *         },
 *         "delete": {
 *             "path": "/v3/email_templates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR') and object.getAuthor() == user",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Email Template resource.",
 *                         "example": "de7982c4-3729-4f9d-9587-376df25354c3",
 *                     },
 *                 },
 *             },
 *         },
 *     },
 * )
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="email_templates",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="email_template_uuid_unique", columns="uuid")
 *     }
 * )
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="author",
 *         joinColumns={
 *             @ORM\JoinColumn(onDelete="CASCADE")
 *         }
 *     )
 * })
 */
class EmailTemplate implements AuthoredItemsCollectionInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use AuthoredTrait;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"email_template_read", "email_template_write", "email_template_list_read"})
     *
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"email_template_read", "email_template_write"})
     */
    private $content;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function setAuthor(Adherent $adherent): void
    {
        $this->author = $adherent;
    }
}
