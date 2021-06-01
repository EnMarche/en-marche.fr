<?php

namespace App\Entity\Filesystem;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="filesystem_file_permission", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="file_permission_unique", columns={"file_id", "name"})
 * })
 */
class FilePermission
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var File
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Filesystem\File", inversedBy="permissions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *     callback={"App\Entity\Filesystem\FilePermissionEnum", "toArray"},
     *     strict=true
     * )
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file = null): void
    {
        $this->file = $file;
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
        return (string) $this->name;
    }
}
