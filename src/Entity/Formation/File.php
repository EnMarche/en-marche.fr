<?php

namespace App\Entity\Formation;

use App\Entity\BaseFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="formation_files",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="formation_file_slug_extension", columns={"slug", "extension"})}
 * )
 */
class File extends BaseFile
{
    public const PREFIX_PATH = 'files/formations';

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf", "application/x-pdf"}
     * )
     */
    protected $file;

    /**
     * @var Module
     *
     * @ORM\ManyToOne(targetEntity="Module", inversedBy="files")
     * @ORM\OrderBy({"id": "ASC"})
     */
    private $module;

    public function getPrefixPath(): string
    {
        return self::PREFIX_PATH;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): void
    {
        $this->module = $module;
    }
}
