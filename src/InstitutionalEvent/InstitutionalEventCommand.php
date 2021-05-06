<?php

namespace App\InstitutionalEvent;

use App\Address\Address;
use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\InstitutionalEvent;
use App\Entity\Event\InstitutionalEventCategory;
use App\Event\BaseEventCommand;
use App\Validator\DateRange;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @DateRange(
 *     startDateField="beginAt",
 *     endDateField="finishAt",
 *     interval="3 days",
 *     messageDate="committee.event.invalid_finish_date"
 * )
 */
class InstitutionalEventCommand extends BaseEventCommand
{
    /**
     * @Assert\Count(
     *     min=1,
     *     max=50,
     *     minMessage="institutional_event.invitations.min",
     *     maxMessage="institutional_event.invitations.max"
     * )
     */
    private $invitations;

    public function __construct(
        ?Adherent $author,
        UuidInterface $uuid = null,
        Address $address = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null,
        InstitutionalEvent $event = null,
        string $timezone = GeoCoder::DEFAULT_TIME_ZONE,
        ?string $visioUrl = null,
        array $invitations = []
    ) {
        parent::__construct($author, $uuid, $address, $beginAt, $finishAt, $event, $timezone, $visioUrl);

        $this->invitations = $invitations;
    }

    public static function createFromInstitutionalEvent(InstitutionalEvent $event): self
    {
        $command = new self(
            $event->getOrganizer(),
            $event->getUuid(),
            self::getAddressModelFromEvent($event),
            $event->getBeginAt(),
            $event->getFinishAt(),
            $event,
            $event->getTimeZone(),
            $event->getVisioUrl(),
            $event->getInvitations()
        );

        $command->category = $event->getCategory();

        return $command;
    }

    /**
     * @return InstitutionalEventCategory|null
     */
    public function getCategory(): ?BaseEventCategory
    {
        return parent::getCategory();
    }

    protected function getCategoryClass(): string
    {
        return InstitutionalEventCategory::class;
    }

    public function getInvitations(): array
    {
        return $this->invitations;
    }

    public function setInvitations(array $invitations): void
    {
        $this->invitations = $invitations;
    }
}
