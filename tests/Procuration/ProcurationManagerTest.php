<?php

namespace Tests\App\Procuration;

use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Procuration\ProcurationManager;
use App\Repository\ProcurationRequestRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group procuration
 */
class ProcurationManagerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var ProcurationRequestRepository
     */
    private $procurationRequestRepository;

    /**
     * @var ProcurationManager
     */
    private $procurationManager;

    /**
     * @dataProvider provideMatchingProcurationProxies
     */
    public function testGetMatchingProcurationProxies(string $requestEmail, array $expectedMatchingProxies): void
    {
        /** @var ProcurationRequest $request */
        $request = $this->procurationRequestRepository->findByEmailAddress($requestEmail)[0];

        $matchingProxies = $this->procurationManager->getMatchingProcurationProxies($request);

        $this->assertSameSize($expectedMatchingProxies, $matchingProxies);

        for ($i = 0; $i < \count($expectedMatchingProxies); ++$i) {
            $matchingProxy = $matchingProxies[$i];
            $expectedMatchingProxy = $expectedMatchingProxies[$i];

            $this->assertArrayHasKey('data', $matchingProxy);
            $this->assertArrayHasKey('score', $matchingProxy);

            /** @var ProcurationProxy $proxy */
            $proxy = $matchingProxy['data'];

            $this->assertInstanceOf(ProcurationProxy::class, $proxy);
            $this->assertSame($expectedMatchingProxy['email'], $proxy->getEmailAddress());
            // Ensure Request & Proxy are voting in the same city
            $this->assertSame($request->getVoteCountry(), $proxy->getVoteCountry());
            $this->assertSame($request->getVoteCity(), $proxy->getVoteCity());
            $this->assertSame($request->getVoteCityName(), $proxy->getVoteCityName());

            /** @var string $score */
            $score = $matchingProxy['score'];

            $this->assertTrue(is_numeric($score));
            $this->assertSame($expectedMatchingProxy['score'], (int) $score);
        }
    }

    public function provideMatchingProcurationProxies(): \Generator
    {
        yield ['jeanmichel.gastro@example.es', [
            [
                'email' => 'jeanmarc.gastro@example.es',
                'score' => 7,
            ],
        ]];

        yield ['fleurpare@armyspy.com', [
            [
                'email' => 'jm.carbonneau@example.fr',
                'score' => 2,
            ],
            [
                'email' => 'maxime.michaux@example.fr',
                'score' => 2,
            ],
        ]];

        yield ['aurelie.baume@example.gb ', []];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->procurationRequestRepository = $this->getProcurationRequestRepository();
        $this->procurationManager = $this->get(ProcurationManager::class);
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->procurationRequestRepository = null;
        $this->procurationManager = null;

        parent::tearDown();
    }
}
