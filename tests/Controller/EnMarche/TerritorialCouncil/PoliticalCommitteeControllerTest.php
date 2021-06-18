<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Report\Report;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class PoliticalCommitteeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testTerritorialCouncilMemberButNotMemberOfAPoliticalCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');
        $crawler = $this->client->request('GET', '/');
        self::assertCount(1, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $crawler = $this->client->click($crawler->selectLink('Mes instances')->link());
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites#instances', $crawler->getUri());

        self::assertCount(1, $crawler->filter('#territorial_council'));
        self::assertCount(0, $crawler->filter('#political_committee'));

        $this->client->request('GET', '/comite-politique');
        self::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testMemberOfInactivePoliticalCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request('GET', '/');
        self::assertCount(1, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $crawler = $this->client->click($crawler->selectLink('Mes instances')->link());
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites#instances', $crawler->getUri());

        self::assertCount(1, $crawler->filter('#territorial_council'));
        self::assertCount(0, $crawler->filter('#political_committee'));

        $this->client->request('GET', '/comite-politique');
        self::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testMembers()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request('GET', '/');

        self::assertCount(1, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $crawler = $this->client->click($crawler->selectLink('Mes instances')->link());
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites#instances', $crawler->getUri());

        self::assertCount(1, $crawler->filter('article#territorial_council'));
        self::assertCount(1, $crawler = $crawler->filter('article#political_committee'));

        $crawler = $this->client->click($crawler->selectLink('Voir')->link());
        self::assertEquals('http://test.enmarche.code/comite-politique', $crawler->getUri());

        self::assertCount(1, $crawler->filter('.instance-page__aside h5:contains("Président du Comité politique")'));
    }

    public function testSeeMessages()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/comite-politique');

        $this->isSuccessful($this->client->getResponse());

        $messages = $crawler->filter('.instance-page__feed__item');
        $buttons = $crawler->filter('.instance-page__feed__item .list__links--row');

        self::assertCount(10, $messages);
        self::assertCount(0, $buttons);
    }

    public function testSeeMessagesWithButtons()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/comite-politique');

        $this->isSuccessful($this->client->getResponse());

        $messages = $crawler->filter('.instance-page__feed__item');
        $buttons = $crawler->filter('.instance-page__feed__item .list__links--row');

        self::assertCount(10, $messages);
        self::assertCount(10, $buttons);
    }

    public function testDeleteEditMessageDenied()
    {
        $adherent = $this->getAdherent(LoadAdherentData::REFERENT_2_UUID);
        $message = $this->getTerritorialCouncilFeedItemRepository()->findBy(['author' => $adherent], null, 1, 2)[0];

        $this->client->request(Request::METHOD_GET, '/comite-politique/messages/'.$message->getId().'/modifier');
        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $this->client->request(Request::METHOD_GET, '/comite-politique/messages/'.$message->getId().'/supprimer');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->client->request(Request::METHOD_DELETE, '/comite-politique/messages/'.$message->getId().'/supprimer');
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testEditMessage()
    {
        $adherent = $this->getAdherent(LoadAdherentData::REFERENT_2_UUID);
        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $message = $this->getPoliticalCommitteeFeedItemRepository()->findBy(['author' => $adherent], null, 1, 2)[0];
        $crawler = $this->client->request(Request::METHOD_GET, '/comite-politique/messages/'.$message->getId().'/modifier');

        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('feed_item_save')->form();
        $this->assertSame($message->getContent(), $form->get('feed_item[content]')->getValue());

        $form->setValues(['feed_item[content]' => $message->getContent().' test']);
        $this->client->submit($form);
        $this->assertClientIsRedirectedTo('/comite-politique', $this->client);

        $this->client->followRedirect();

        self::assertStringContainsString($message->getContent().' test', $this->client->getResponse()->getContent());
    }

    public function testDeleteMessage()
    {
        $adherent = $this->getAdherent(LoadAdherentData::REFERENT_2_UUID);
        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $message = $this->getPoliticalCommitteeFeedItemRepository()->findBy(['author' => $adherent], null, 1)[0];
        $crawler = $this->client->request(Request::METHOD_GET, '/comite-politique');

        $this->isSuccessful($this->client->getResponse());

        self::assertStringContainsString($message->getContent(), $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('delete_entity_delete')->form();
        $this->client->submit($form);
        $this->assertClientIsRedirectedTo('/comite-politique', $this->client);

        $this->client->followRedirect();
        self::assertStringNotContainsString($message->getContent(), $this->client->getResponse()->getContent());
    }

    public function testSeeOfficialReports()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/comite-politique/proces-verbaux');

        $this->isSuccessful($this->client->getResponse());

        $reports = $crawler->filter('.official-reports article');

        self::assertCount(2, $reports);
        self::assertStringContainsString('Deuxième PV 75', $reports->eq(0)->text());
        self::assertStringContainsString('Ajouté par Referent Referent', $reports->eq(0)->text());
        self::assertStringContainsString('le 15 octobre 2020', $reports->eq(0)->text());
        self::assertStringContainsString('version 2', $reports->eq(0)->text());
        self::assertStringContainsString('Modifié le 20 octobre 2020', $reports->eq(0)->text());
        self::assertStringContainsString('Test PV 75 1', $reports->eq(1)->text());
        self::assertStringContainsString('Ajouté par Referent Referent', $reports->eq(1)->text());
        self::assertStringContainsString('le 10 octobre 2020', $reports->eq(1)->text());
    }

    public function testCannotDownloadOfficialReport()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        /** @var Report $report */
        $report = $this->getOfficialReportRepository()->findOneBy(['name' => 'Test PV 75 1']);

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/comite-politique/proces-verbaux/%s', $report->getUuid())
        );

        static::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
}
