<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Repository\EventRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class EventRepositoryTest extends SqliteWebTestCase
{
    /**
     * @var EventRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testCountEvents()
    {
        $this->assertSame(14, $this->repository->countElements());
    }

    public function testFindUpcomingEvents()
    {
        $this->assertCount(8, $this->repository->findUpcomingEvents());
    }

    public function testCountUpcomingEvents()
    {
        $this->assertSame(8, $this->repository->countUpcomingEvents());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getEventRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
