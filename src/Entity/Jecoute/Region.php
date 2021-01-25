<?php

namespace App\Entity\Jecoute;

use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Region as GeoRegion;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_region")
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\RegionRepository")
 *
 * @UniqueEntity(fields={"geoRegion"})
 */
class Region
{
    use EntityTimestampableTrait;

    private const IMAGES_DIRECTORY = 'files/jemarche/regions';

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    protected $uuid;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\Length(max=120)
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $subtitle;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\Choice(callback={"App\Jecoute\RegionColorEnum", "all"})
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $primaryColor;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $externalLink;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $banner;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "image/*"
     *     }
     * )
     */
    private $bannerFile;

    private $removeBanner = false;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $logo;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "image/*"
     *     }
     * )
     */
    private $logoFile;

    /**
     * @var GeoRegion|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Geo\Region")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     */
    private $geoRegion;

    public function __construct(
        UuidInterface $uuid = null,
        GeoRegion $geoRegion = null,
        string $subtitle = null,
        string $description = null,
        string $primaryColor = null,
        string $logo = null,
        string $banner = null,
        string $externalLink = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->geoRegion = $geoRegion;
        $this->subtitle = $subtitle;
        $this->description = $description;
        $this->primaryColor = $primaryColor;
        $this->logo = $logo;
        $this->banner = $banner;
        $this->externalLink = $externalLink;
    }

    public function __toString()
    {
        if ($this->geoRegion) {
            return sprintf('%s (%s)', $this->geoRegion->getName(), $this->geoRegion->getCode());
        }

        return $this->uuid->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): void
    {
        $this->primaryColor = $primaryColor;
    }

    public function hasBannerUploaded(): bool
    {
        return null !== $this->banner;
    }

    public function getBannerFile(): ?UploadedFile
    {
        return $this->bannerFile;
    }

    public function setBannerFile(?UploadedFile $file): void
    {
        $this->bannerFile = $file;
    }

    public function removeBanner(): void
    {
        $this->banner = null;
    }

    public function setBanner(string $banner): void
    {
        $this->banner = $banner;
    }

    public function getRemoveBanner(): bool
    {
        return $this->removeBanner;
    }

    public function setRemoveBanner(bool $removeBanner): void
    {
        $this->removeBanner = $removeBanner;
    }

    public function getBannerPathWithDirectory(): string
    {
        return sprintf('%s/%s', self::IMAGES_DIRECTORY, $this->banner);
    }

    public function setBannerFromUploadedFile(): void
    {
        $this->banner = sprintf('%s-banner.%s',
            md5(sprintf('%s@%s', $this->getUuid(), $this->bannerFile->getClientOriginalName())),
            $this->bannerFile->getClientOriginalExtension()
        );
    }

    public function hasLogoUploaded(): bool
    {
        return null !== $this->logo;
    }

    public function getLogoFile(): ?UploadedFile
    {
        return $this->logoFile;
    }

    public function setLogoFile(?UploadedFile $file): void
    {
        $this->logoFile = $file;
    }

    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    public function getLogoPathWithDirectory(): string
    {
        return sprintf('%s/%s', self::IMAGES_DIRECTORY, $this->logo);
    }

    public function setLogoFromUploadedFile(): void
    {
        $this->logo = sprintf('%s-logo.%s',
            md5(sprintf('%s@%s', $this->getUuid(), $this->logoFile->getClientOriginalName())),
            $this->logoFile->getClientOriginalExtension()
        );
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function getGeoRegion(): ?GeoRegion
    {
        return $this->geoRegion;
    }

    public function setGeoRegion(?GeoRegion $geoRegion): void
    {
        $this->geoRegion = $geoRegion;
    }
}
