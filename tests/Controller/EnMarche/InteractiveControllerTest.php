<?php

namespace Tests\App\Controller\EnMarche;

use App\Controller\EnMarche\InteractiveController;
use App\Interactive\MyEuropeProcessor;
use App\Interactive\MyEuropeProcessorHandler;
use App\Repository\EmailRepository;
use App\Repository\MyEuropeChoiceRepository;
use App\Repository\MyEuropeInvitationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class InteractiveControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public const MY_EUROPE_PATH = '/mon-europe';
    public const MY_EUROPE_RESTART_PATH = '/mon-europe/recommencer';

    /* @var MyEuropeChoiceRepository */
    private $MyEuropeChoiceRepository;

    /* @var MyEuropeInvitationRepository */
    private $MyEuropeInvitationRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testMyEuropeAction()
    {
        $this->assertCount(0, $this->emailRepository->findAll());

        $myEurope = new MyEuropeProcessor();

        $crawler = $this->client->request(Request::METHOD_GET, self::MY_EUROPE_PATH);

        $this->assertEquals($myEurope, $this->getCurrentMyEurope());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="my_europe[fill_info]"]'));

        $this->client->submit($crawler->filter('form[name="my_europe"]')->form([
            'my_europe[friendFirstName]' => $myEurope->friendFirstName = 'Mylène',
            'my_europe[friendAge]' => '26',
            'my_europe[friendGender]' => $myEurope->friendGender = 'female',
        ]));

        $myEurope->friendAge = 26;
        $myEurope->marking = MyEuropeProcessor::STATE_NEEDS_FRIEND_CASES;
        $myEurope->messageSubject = InteractiveController::MESSAGE_SUBJECT;

        $this->assertEquals($myEurope, $this->getCurrentMyEurope());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::MY_EUROPE_PATH, $this->client);
    }

    public function testRestartMyEuropeAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::MY_EUROPE_PATH);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="my_europe"]')->form([
            'my_europe[friendFirstName]' => 'Mylène',
            'my_europe[friendAge]' => '26',
            'my_europe[friendGender]' => 'female',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::MY_EUROPE_PATH, $this->client);

        $this->client->followRedirect();
        $this->client->request(Request::METHOD_GET, self::MY_EUROPE_RESTART_PATH);

        $this->assertNull($this->client->getRequest()->getSession()->get(MyEuropeProcessorHandler::SESSION_KEY));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->MyEuropeChoiceRepository = $this->getMyEuropeChoiceRepository();
        $this->MyEuropeInvitationRepository = $this->getMyEuropeInvitationRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->MyEuropeInvitationRepository = null;
        $this->MyEuropeChoiceRepository = null;

        parent::tearDown();
    }

    private function getMyEuropeInvitationHandler(): MyEuropeProcessorHandler
    {
        return $this->get(MyEuropeProcessorHandler::class);
    }

    private function getCurrentMyEurope(): MyEuropeProcessor
    {
        return $this->getMyEuropeInvitationHandler()->start($this->client->getRequest()->getSession());
    }
}
