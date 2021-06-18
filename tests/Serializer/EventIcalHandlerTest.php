<?php

namespace Tests\App\Serializer;

use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\MunicipalEvent;
use App\Serializer\EventICalHandler;
use App\Serializer\IcalSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class EventIcalHandlerTest extends TestCase
{
    /**
     * @var EventICalHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new EventICalHandler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->handler = null;
    }

    public function testGetSubscribingMethods()
    {
        $result = EventICalHandler::getSubscribingMethods();

        $this->assertCount(2, $result);

        $this->assertEquals(CommitteeEvent::class, $result[0]['type']);
        $this->assertEquals('ical', $result[0]['format']);
        $this->assertEquals(GraphNavigator::DIRECTION_SERIALIZATION, $result[0]['direction']);
        $this->assertEquals('serialize', $result[0]['method']);

        $this->assertEquals(MunicipalEvent::class, $result[1]['type']);
        $this->assertEquals('ical', $result[1]['format']);
        $this->assertEquals(GraphNavigator::DIRECTION_SERIALIZATION, $result[1]['direction']);
        $this->assertEquals('serialize', $result[1]['method']);
    }

    /**
     * Since the model does not require an organizer, contact informations should not be available.
     */
    public function testSerializeNoOrganizer()
    {
        $visitor = $this->createMock(IcalSerializationVisitor::class);
        $committeeEvent = $this->createMock(CommitteeEvent::class);
        $uuid = $this->createMock(UuidInterface::class);
        $startDate = $this->createMock('\DateTime');
        $endDate = $this->createMock('\DateTime');

        $committeeEvent->expects($this->once())
                       ->method('getUuid')
                       ->will($this->returnValue($uuid))
        ;
        $committeeEvent->expects($this->once())
                       ->method('getLocalBeginAt')
                       ->will($this->returnValue($startDate))
        ;
        $committeeEvent->expects($this->once())
                       ->method('getLocalFinishAt')
                       ->will($this->returnValue($endDate))
        ;
        $committeeEvent->expects($this->once())
                       ->method('getOrganizer')
                       ->will($this->returnValue(null))
        ;

        $visitor->expects($this->once())
                ->method('setRoot')
        ;

        $this->handler->serialize($visitor, $committeeEvent);
    }
}
