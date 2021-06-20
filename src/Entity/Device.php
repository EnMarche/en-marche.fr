<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"device_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"device_write"}
 *         },
 *     },
 *     collectionOperations={},
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/device/{deviceUuid}",
 *             "requirements": {"deviceUuid": "[\w-]+"},
 *             "access_control": "is_granted('ROLE_OAUTH_DEVICE') and object.equals(user.getDevice())"
 *         },
 *         "put": {
 *             "path": "/v3/device/{deviceUuid}",
 *             "requirements": {"deviceUuid": "[\w-]+"},
 *             "access_control": "is_granted('ROLE_OAUTH_DEVICE') and object.equals(user.getDevice())"
 *         },
 *     }
 * )
 *
 * @ORM\Table(name="devices", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="devices_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="devices_device_uuid_unique", columns="device_uuid")
 * })
 * @ORM\Entity(repositoryClass="App\Repository\DeviceRepository")
 */
class Device
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     * @ApiProperty(identifier=false)
     *
     * @SymfonySerializer\Groups({"user_profile"})
     */
    protected $uuid;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     *
     * @ApiProperty(identifier=true)
     *
     * @SymfonySerializer\Groups("user_profile")
     */
    protected $deviceUuid;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @SymfonySerializer\Groups({"user_profile", "device_write"})
     *
     * @Assert\Length(max=15)
     */
    private $postalCode;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoggedAt;

    public function __construct(UuidInterface $uuid, string $deviceUuid, string $postalCode = null)
    {
        $this->uuid = $uuid;
        $this->deviceUuid = $deviceUuid;
        $this->postalCode = $postalCode;

        $this->zones = new ArrayCollection();
    }

    public function getLastLoggedAt(): ?\DateTimeInterface
    {
        return $this->lastLoggedAt;
    }

    public function login(): void
    {
        $this->lastLoggedAt = new \DateTime('now');
    }

    public function getDeviceUuid(): string
    {
        return $this->deviceUuid;
    }

    public function getIdentifier(): string
    {
        return $this->getDeviceUuid();
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }
}
