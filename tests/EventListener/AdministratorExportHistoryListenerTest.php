<?php

namespace Tests\App\EventListener;

use App\Entity\Reporting\AdministratorExportHistory;
use Doctrine\ORM\EntityRepository;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class AdministratorExportHistoryListenerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var EntityRepository
     */
    private $historyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyRepository = $this->getRepository(AdministratorExportHistory::class);
    }

    protected function tearDown(): void
    {
        $this->historyRepository = null;

        parent::tearDown();
    }

    public function testCreateHistory()
    {
        self::assertEmpty($this->historyRepository->findAll());

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $parameters = [
            'filter' => [
                'city' => [
                    'value' => 'Bordeaux',
                ],
            ],
            'format' => 'csv',
        ];

        $this->client->request('GET', '/admin/app/adherent/export', $parameters);

        $histories = $this->historyRepository->findAll();
        self::assertCount(1, $histories);

        /** @var AdministratorExportHistory $history */
        $history = reset($histories);
        self::assertSame('admin@en-marche-dev.fr', $history->getAdministrator()->getEmailAddress());
        self::assertSame('admin_app_adherent_export', $history->getRouteName());
        self::assertSame($parameters, $history->getParameters());
    }
}
