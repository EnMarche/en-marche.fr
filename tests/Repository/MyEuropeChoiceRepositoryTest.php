<?php

namespace Tests\App\Repository;

use App\Entity\MyEuropeChoice;
use App\Repository\MyEuropeChoiceRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class MyEuropeChoiceRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var MyEuropeChoiceRepository
     */
    private $repository;

    public function testGetMailIntroductionAndConclusion()
    {
        $introduction = $this->repository->findMailIntroduction();

        $this->assertInstanceOf(MyEuropeChoice::class, $introduction);
        $this->assertSame('28ceb6d3-ec64-4a58-99a4-71357600d07c', $introduction->getUuid()->toString());

        $conclusion = $this->repository->findMailConclusion();

        $this->assertInstanceOf(MyEuropeChoice::class, $conclusion);
        $this->assertSame('3d735d18-348c-4d02-8046-7976f86e5ecc', $conclusion->getUuid()->toString());

        $common = $this->repository->findMailCommon();

        $this->assertInstanceOf(MyEuropeChoice::class, $common);
        $this->assertSame('a642dbc7-aba5-49e4-877a-06bc1ef23168', $common->getUuid()->toString());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMyEuropeChoiceRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}
