<?php

namespace Tests\App\Controller\Admin;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Mailer\Message\CommitteeApprovalConfirmationMessage;
use App\Mailer\Message\CommitteeApprovalReferentMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdminCommitteeCRUDControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    private $committeeRepository;

    public function testApproveCommitteeAction(): void
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadCommitteeData::COMMITTEE_2_UUID);

        $this->assertFalse($committee->isApproved());

        $this->authenticateAsAdmin($this->client);

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/admin/app/committee/%s/approve', $committee->getId()));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Suivant')->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Confirmer')->form());

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertMessageIsDispatched(CreateStaticSegmentCommand::class);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->get('doctrine.orm.entity_manager')->clear();

        $committee = $this->committeeRepository->findOneByUuid(LoadCommitteeData::COMMITTEE_2_UUID);

        $this->assertTrue($committee->isApproved());
        $this->assertCountMails(1, CommitteeApprovalConfirmationMessage::class, 'benjyd@aol.com');
        $this->assertCountMails(1, CommitteeApprovalReferentMessage::class, 'referent@en-marche-dev.fr');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeRepository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->committeeRepository = null;

        parent::tearDown();
    }
}
