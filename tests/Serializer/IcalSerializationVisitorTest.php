<?php

namespace Tests\App\Serializer;

use App\Serializer\IcalSerializationVisitor;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use PHPUnit\Framework\TestCase;

class IcalSerializationVisitorTest extends TestCase
{
    protected $propertyNamingStrategy;
    /**
     * @var IcalSerializationVisitor
     */
    protected $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propertyNamingStrategy = $this->createMock(PropertyNamingStrategyInterface::class);
        $this->visitor = new IcalSerializationVisitor($this->propertyNamingStrategy);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->propertyNamingStrategy = null;
        $this->visitor = null;
    }

    /**
     * Ensure a NULL value is returned when no data has been provided.
     */
    public function testGetResultEmptyData()
    {
        $this->assertNull($this->visitor->getResult());
    }

    /**
     * Ensure the output is a string.
     *
     * TODO Check generated content
     */
    public function testGetResult()
    {
        $eventData = ['VEVENT' => []];
        $this->visitor->setRoot($eventData);

        $this->assertIsString($this->visitor->getResult());
    }
}
