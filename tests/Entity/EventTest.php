<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\EventCategory;
use App\Entity\PostAddress;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class EventTest extends TestCase
{
    /**
     * @dataProvider provideNotFinishedEventDate
     */
    public function testEventIsNotConsideredFinished(string $timeZone, string $date)
    {
        $address = $this->createMock(PostAddress::class);

        $event = new CommitteeEvent(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            $this->createMock(EventCategory::class),
            '',
            $address,
            '',
            $date
        );
        $event->setTimeZone($timeZone);

        $this->assertFalse($event->isFinished());
    }

    public static function provideNotFinishedEventDate(): array
    {
        return [
            ['Europe/Paris', '+3 hours'],
            ['Europe/Paris', '+24 hours'],
            ['America/New_York', '-1 hours'],
            ['America/Indiana/Indianapolis', '-3 hours'],
            ['America/Los_Angeles', '-6 hours'],
        ];
    }

    /**
     * @dataProvider provideFinishedEventDate
     */
    public function testEventIsConsideredFinished(string $country, string $date)
    {
        $address = $this->createMock(PostAddress::class);
        $address->expects($this->any())->method('getCountry')->willReturn($country);

        $event = new CommitteeEvent(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            $this->createMock(EventCategory::class),
            '',
            $address,
            '',
            $date
        );

        $this->assertTrue($event->isFinished());
    }

    public static function provideFinishedEventDate(): array
    {
        return [
            ['FR', '-2 hours'],
            ['FR', '-24 hours'],
            ['RU', '-5 hour'],
            ['AU', '-3 hours'],
            ['AU', '-11 hours'],
            ['SG', '-8 hours'],
            ['US', '-12 hours'],
            ['US', '-24 hours'],
        ];
    }

    public function testIsFull()
    {
        $event = new CommitteeEvent(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            $this->createMock(EventCategory::class),
            '',
            $this->createMock(PostAddress::class),
            '',
            '',
            2,
            false,
            null,
            0,
            null
        );
        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertTrue($event->isFull());

        $event = new CommitteeEvent(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            $this->createMock(EventCategory::class),
            '',
            $this->createMock(PostAddress::class),
            '',
            '',
            null,
            false,
            null,
            10000000,
            null
        );
        $this->assertFalse($event->isFull());
    }
}
