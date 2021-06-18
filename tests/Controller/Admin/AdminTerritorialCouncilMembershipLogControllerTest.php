<?php

namespace Tests\App\Controller\Admin;

use App\Controller\Admin\AdminTerritorialCouncilMembershipLogController;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdminTerritorialCouncilMembershipLogControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private $tcMembershipLogRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tcMembershipLogRepository = $this->getRepository(TerritorialCouncilMembershipLog::class);
    }

    protected function tearDown(): void
    {
        $this->tcMembershipLogRepository = null;

        parent::tearDown();
    }

    public function testCannotSetResolvedIfIncorrectStatus(): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => false]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), 'incorrect_status')
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    /**
     * @dataProvider provideStatus
     */
    public function testCannotSetResolvedIfNotValidStatus(string $status, bool $isResolved): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => !$isResolved]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), $status)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    /**
     * @dataProvider provideStatus
     */
    public function testCannotSetResolvedIfNotValidToken(string $status, bool $isResolved): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => $isResolved]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), $status)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function provideStatus(): iterable
    {
        yield [AdminTerritorialCouncilMembershipLogController::STATUS_RESOLVED, false];
        yield [AdminTerritorialCouncilMembershipLogController::STATUS_UNRESOLVED, true];
    }
}
