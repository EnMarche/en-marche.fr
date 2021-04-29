<?php

namespace App\Entity\Event;

use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Entity\ExposedObjectInterface;
use App\Entity\PostAddress;
use App\Entity\Report\ReportableInterface;
use App\Event\EventTypeEnum;
use App\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CitizenActionRepository")
 */
class CitizenAction extends BaseEvent implements ReportableInterface, ExposedObjectInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\CitizenActionCategory")
     *
     * @Groups({"event_list_read"})
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CitizenProject")
     */
    private $citizenProject;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        CitizenProject $citizenProject,
        string $name,
        CitizenActionCategory $category,
        string $description,
        PostAddress $address,
        \DateTimeInterface $beginAt,
        \DateTimeInterface $finishAt,
        int $participantsCount = 0,
        array $referentTags = [],
        string $timeZone = GeoCoder::DEFAULT_TIME_ZONE,
        ?string $visioUrl = null
    ) {
        parent::__construct($uuid);

        $this->organizer = $organizer;
        $this->citizenProject = $citizenProject;
        $this->setName($name);
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->participantsCount = $participantsCount;
        // We need a \DateTime object for now to work with Gedmo sluggable
        $this->beginAt = $beginAt instanceof \DateTimeImmutable ? new \DateTime($beginAt->format(\DATE_ATOM)) : $beginAt;
        $this->finishAt = $finishAt;
        $this->referentTags = new ArrayCollection($referentTags);
        $this->zones = new ArrayCollection();
        $this->timeZone = $timeZone;
        $this->setVisioUrl($visioUrl);
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }

    public function getCategory(): CitizenActionCategory
    {
        return $this->category;
    }

    public function setCategory(CitizenActionCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategoryName(): string
    {
        return $this->category->getName();
    }

    public function getType(): string
    {
        return EventTypeEnum::TYPE_CITIZEN_ACTION;
    }

    public function getReportType(): string
    {
        return ReportType::CITIZEN_ACTION;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("categoryName")
     * @JMS\Groups({"public", "citizen_action_read"})
     */
    public function getCitizenActionCategoryName(): ?string
    {
        if (!$category = $this->getCategory()) {
            return null;
        }

        return $category->getName();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("citizenProjectUuid")
     * @JMS\Groups({"public", "citizen_action_read"})
     */
    public function getCitizenProjectUuidAsString(): ?string
    {
        if (!$citizenProject = $this->getCitizenProject()) {
            return null;
        }

        return $citizenProject->getUuidAsString();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("organizerUuid")
     * @JMS\Groups({"public", "citizen_action_read"})
     */
    public function getOrganizerUuid(): ?string
    {
        if (!$organizer = $this->getOrganizer()) {
            return null;
        }

        return $organizer->getUuidAsString();
    }

    public function getExposedRouteName(): string
    {
        return 'app_citizen_action_event_show';
    }
}
