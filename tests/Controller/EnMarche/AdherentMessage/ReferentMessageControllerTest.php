<?php

namespace Tests\App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

class ReferentMessageControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    public function testReferentCanCreateMessageAndChangeFilterSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->click(
            $this->client->request('GET', '/')->selectLink('Espace référent')->link()
        );
        $crawler = $this->client->followRedirect();

        $crawler = $this->client->click($crawler->selectLink('Aux adhérents')->link());
        $crawler = $this->client->click($crawler->selectLink('+ Nouveau message')->link());

        $this->client->submit($crawler->selectButton('Suivant →')->form(['adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $crawler = $this->client->followRedirect();

        self::assertSame(
            'Filtrer par',
            trim($crawler->filter('h4.manager__filters__subtitle')->text())
        );

        self::assertCount(7, $crawler->filter('select#referent_filter_referentTags option[selected="selected"]'));

        $this->client->submit($crawler->selectButton('Filtrer')->form([
            'referent_filter[referentTags]' => [13],
        ]));

        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $crawler = $this->client->followRedirect();

        self::assertSame('Les filtres ont bien été appliqués.', $crawler->filter('div.flash__inner')->text());
    }

    public function testReferentCanVisualizeMessageSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $referentMessage = $this->client->getContainer()->get('doctrine')->getManager()
            ->getRepository(ReferentAdherentMessage::class)
            ->findOneBy(['status' => 'draft'])
        ;

        $uuid = $referentMessage->getUuid()->toString();
        $crawler = $this->client->request('GET',
            sprintf('/espace-referent/messagerie/%s/visualiser', $uuid)
        );

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $buttons = $crawler->filter('div.text--center a.btn--ghosting--blue');

        self::assertSame(2, $buttons->count());
        self::assertSame('Editer le message', $buttons->eq(0)->text());
        self::assertSame('Modifier les filtres', $buttons->eq(1)->text());

        self::assertSame(sprintf('/espace-referent/messagerie/%s/modifier', $uuid), $buttons->eq(0)->attr('href'));
        self::assertSame(sprintf('/espace-referent/messagerie/%s/filtrer', $uuid), $buttons->eq(1)->attr('href'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
