<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use App\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class TerritorialCouncilControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testNotMemberOfATerritorialCouncil()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
        $crawler = $this->client->request('GET', '/');
        self::assertCount(0, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $this->client->request('GET', '/conseil-territorial');
        self::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testMembers()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request('GET', '/');

        self::assertCount(1, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $crawler = $this->client->click($crawler->selectLink('Mes instances')->link());
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites#instances', $crawler->getUri());

        $crawler = $this->client->click($crawler->selectLink('Voir')->link());

        $crawler = $this->client->click($crawler->selectLink('Membres')->link());
        $members = $crawler->filter('.instance-page__members .instance-page__member');
        self::assertCount(8, $members);
        self::assertStringContainsString('Jacques Picard', $members->first()->text());
        self::assertStringContainsString('Lucie Olivera', $members->eq(1)->text());
        self::assertStringContainsString('Gisele Berthoux', $members->eq(2)->text());

        self::assertCount(1, $crawler->filter('.instance-page__aside h5:contains("Président du Conseil territorial")'));
    }

    public function testSeeMessages()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/conseil-territorial');

        $this->isSuccessful($this->client->getResponse());

        $messages = $crawler->filter('.instance-page__feed__item');
        $buttons = $crawler->filter('.instance-page__feed__item .list__links--row');

        self::assertCount(10, $messages);
        self::assertCount(0, $buttons);
    }

    public function testSeeMessagesWithButtons()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/conseil-territorial');

        $this->isSuccessful($this->client->getResponse());

        $messages = $crawler->filter('.instance-page__feed__item');
        $buttons = $crawler->filter('.instance-page__feed__item .list__links--row');

        self::assertCount(10, $messages);
        self::assertCount(10, $buttons);
    }

    public function testDeleteEditMessageDenied()
    {
        $adherent = $this->getAdherent(LoadAdherentData::REFERENT_2_UUID);
        $message = $this->getTerritorialCouncilFeedItemRepository()->findBy(['author' => $adherent], null, 1)[0];

        $this->client->request(Request::METHOD_GET, '/conseil-territorial/messages/'.$message->getId().'/modifier');
        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $this->client->request(Request::METHOD_GET, '/conseil-territorial/messages/'.$message->getId().'/supprimer');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->client->request(Request::METHOD_DELETE, '/conseil-territorial/messages/'.$message->getId().'/supprimer');
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testEditMessage()
    {
        $adherent = $this->getAdherent(LoadAdherentData::REFERENT_2_UUID);
        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $message = $this->getTerritorialCouncilFeedItemRepository()->findBy(['author' => $adherent], null, 1, 2)[0];
        $crawler = $this->client->request(Request::METHOD_GET, '/conseil-territorial/messages/'.$message->getId().'/modifier');

        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('feed_item_save')->form();
        $this->assertSame($message->getContent(), $form->get('feed_item[content]')->getValue());

        $form->setValues(['feed_item[content]' => $message->getContent().' test']);
        $this->client->submit($form);
        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);

        $this->client->followRedirect();

        self::assertStringContainsString($message->getContent().' test', $this->client->getResponse()->getContent());
    }

    public function testDeleteMessage()
    {
        $adherent = $this->getAdherent(LoadAdherentData::REFERENT_2_UUID);
        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $message = $this->getTerritorialCouncilFeedItemRepository()->findBy(['author' => $adherent], null, 1)[0];
        $crawler = $this->client->request(Request::METHOD_GET, '/conseil-territorial');

        $this->isSuccessful($this->client->getResponse());

        self::assertStringContainsString($message->getContent(), $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('delete_entity_delete')->form();
        $this->client->submit($form);
        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);

        $this->client->followRedirect();
        self::assertStringNotContainsString($message->getContent(), $this->client->getResponse()->getContent());
    }

    public function testCanApply()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/conseil-territorial');

        $this->isSuccessful($this->client->getResponse());

        self::assertCount(1, $crawler->filter('.btn:contains("Je candidate en binôme")'));
        self::assertCount(0, $crawler->filter('.btn--disabled:contains("Je candidate en binôme")'));
    }

    public function testCannotApply()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/conseil-territorial');

        $this->isSuccessful($this->client->getResponse());

        self::assertCount(1, $crawler->filter('.btn:contains("Je candidate en binôme")'));
        self::assertCount(1, $crawler->filter('.btn--disabled:contains("Je candidate en binôme")'));
    }
}
