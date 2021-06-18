<?php

namespace Tests\App\Controller\EnMarche;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\Entity\DeputyManagedUsersMessage;
use App\Repository\DeputyManagedUsersMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group deputy
 */
class DeputyControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    /**
     * @var DeputyManagedUsersMessageRepository
     */
    private $deputyMessageRepository;

    public function testSendMailSuccessful()
    {
        $deputyEmail = 'deputy-ch-li@en-marche-dev.fr';
        $this->authenticateAsAdherent($this->client, $deputyEmail);

        $this->client->request(Request::METHOD_GET, '/espace-depute/utilisateurs/message');
        $subject = 'Message from your deputy';
        $content = 'Content of a deputy message.';
        $data = [];
        $data['deputy_message']['subject'] = $subject;
        $data['deputy_message']['content'] = $content;
        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('http://'.$this->getParameter('app_host').'/espace-depute/utilisateurs/message', $this->client->getRequest()->getUri());

        $deputyMessages = $this
            ->deputyMessageRepository
            ->createQueryBuilder('m')
            ->innerJoin('m.from', 'a')
            ->addSelect('a')
            ->getQuery()
            ->getResult()
        ;

        $this->assertCount(1, $deputyMessages);

        /* @var DeputyManagedUsersMessage */
        $message = reset($deputyMessages);
        $deputy = $this->getAdherentRepository()->findOneByEmail($deputyEmail);

        $this->assertSame($deputyEmail, $message->getFrom()->getEmailAddress());
        $this->assertSame($subject, $message->getSubject());
        $this->assertSame($content, $message->getContent());
        $this->assertSame($deputy->getManagedDistrict()->getId(), $message->getDistrict()->getId());
        $this->assertSame(0, $message->getOffset());
    }

    public function testDeputyCanCreateAdherentMessage(): void
    {
        $this->authenticateAsAdherent($this->client, 'deputy-ch-li@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-depute/messagerie/creer');
        $this->client->submit($crawler->selectButton('Suivant')->form(['adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertTrue($this->client->getResponse()->isRedirection());

        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $crawler = $this->client->followRedirect();

        static::assertSame(
            'Filtrer par',
            trim($crawler->filter('.manager__filters__subtitle')->text())
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->deputyMessageRepository = $this->manager->getRepository(DeputyManagedUsersMessage::class);
    }

    protected function tearDown(): void
    {
        $this->deputyMessageRepository = null;

        parent::tearDown();
    }
}
