<?php

namespace Tests\App\Referent;

use App\Entity\Adherent;
use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Entity\ReferentTag;
use App\Referent\ReferentTagManager;
use App\Repository\ReferentTagRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class ReferentTagManagerTest extends AbstractKernelTestCase
{
    /**
     * @var ReferentTagManager
     */
    private $referentTagManager;

    /**
     * @var ReferentTagRepository
     */
    private $referentTagRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referentTagManager = $this->get(ReferentTagManager::class);
        $this->referentTagRepository = $this->getRepository(ReferentTag::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->referentTagManager = null;
        $this->referentTagRepository = null;
    }

    /**
     * @param string[]|array $favoriteCities
     * @param string[]|array $expectedTagCodes
     *
     * @dataProvider provideApplicationRequestsReferentTags
     */
    public function testAssignApplicationRequestReferentTags(array $favoriteCities, array $expectedTagCodes): void
    {
        $applicationRequest = $this->createMock(ApplicationRequest::class);

        $applicationRequest->method('getFavoriteCities')->willReturn($favoriteCities);

        $applicationRequest
            ->expects($this->exactly(\count($expectedTagCodes)))
            ->method('addReferentTag')
            ->will($this->returnValueMap(array_map(function (string $expectedTagCode) {
                return [$this->referentTagRepository->findOneByCode($expectedTagCode)];
            }, $expectedTagCodes)))
        ;

        $this->referentTagManager->assignApplicationRequestReferentTags($applicationRequest);
    }

    /**
     * @dataProvider providesTestIsUpdateNeeded
     */
    public function testIsUpdateNeeded(bool $isUpdateNeeded, string $postalCode, array $referentCodes): void
    {
        $adherent = $this->createAdherentMock($postalCode, $referentCodes);

        $this->assertSame($isUpdateNeeded, $this->referentTagManager->isUpdateNeeded($adherent));
    }

    public function providesTestIsUpdateNeeded(): array
    {
        return [
            [false, '73100', ['73']],
            [true, '75001', ['75']],
            [false, '75001', ['75', '75001']],
            [true, '75001', ['75', '75001', 'France']],
        ];
    }

    public function provideApplicationRequestsReferentTags(): array
    {
        return [
            [['11069'], ['11']],
            [['75101'], ['75101', '75']],
            [['11069', '75101'], ['11', '75001', '75']],
            [['11069', '75101'], ['11', '75001', '75']],
            [['70295', '2A004'], ['70', '2A', '20']],
            [['70295', '75101', '2A004'], ['70', '75001', '75', '2A', '20']],
        ];
    }

    /**
     * @return Adherent|MockObject
     */
    private function createAdherentMock(string $postalCode, array $referentCodes): Adherent
    {
        return $this->createConfiguredMock(Adherent::class, [
            'getCountry' => 'FR',
            'getReferentTagsCodes' => $referentCodes,
            'getPostalCode' => $postalCode,
        ]);
    }
}
