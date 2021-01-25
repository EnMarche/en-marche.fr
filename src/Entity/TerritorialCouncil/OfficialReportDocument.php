<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * @ORM\Entity
 * @ORM\Table(name="territorial_council_official_report_document")
 */
class OfficialReportDocument
{
    public const MIME_TYPE_PDF = 'application/pdf';
    public const MIME_TYPE_X_PDF = 'application/x-pdf';

    public const MIME_TYPES = [
        self::MIME_TYPE_PDF,
        self::MIME_TYPE_X_PDF,
    ];

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=36)
     */
    private $filename;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10)
     */
    private $extension;

    /**
     * @var string|null
     *
     * @ORM\Column(length=30)
     */
    private $mimeType;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", length=1, options={"unsigned": true})
     */
    private $version;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var Adherent|null
     *
     * @Gedmo\Blameable(on="create")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\OfficialReport", inversedBy="documents", cascade={"all"})
     */
    private $report;

    public function __construct(
        OfficialReport $report,
        string $filename,
        string $extension,
        string $mimeType,
        int $version = 1
    ) {
        $this->report = $report;
        $this->filename = $filename;
        $this->extension = $extension;
        $this->mimeType = $mimeType;
        $this->version = $version;
    }

    public function getReport(): ?OfficialReport
    {
        return $this->report;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getFilenameForDownload(): ?string
    {
        return \sprintf('%s_v%d.%s', Urlizer::urlize($this->report->getName()), $this->version, $this->getExtension());
    }

    public function getFilePathWithDirectory(): string
    {
        return \sprintf('%s/%s', 'files/territorial_council/official_reports', $this->filename);
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?Adherent
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Adherent $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function incrementVersion(): void
    {
        ++$this->version;
    }
}
