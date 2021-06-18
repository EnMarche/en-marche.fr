<?php

namespace Tests\App\Repository;

use App\Entity\TonMacronChoice;
use App\Repository\TonMacronChoiceRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class TonMacronChoiceRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var TonMacronChoiceRepository
     */
    private $repository;

    public function testGetMailIntroductionAndConclusion()
    {
        $introduction = $this->repository->findMailIntroduction();

        $this->assertInstanceOf(TonMacronChoice::class, $introduction);
        $this->assertSame('8a2fdb59-357f-4e74-9aeb-c2b064d31064', $introduction->getUuid()->toString());

        $conclusion = $this->repository->findMailConclusion();

        $this->assertInstanceOf(TonMacronChoice::class, $conclusion);
        $this->assertSame('31276b63-a4f3-4994-aca8-ed4ca78c173e', $conclusion->getUuid()->toString());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getTonMacronChoiceRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}
