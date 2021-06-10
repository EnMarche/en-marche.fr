<?php

namespace Tests\App\Controller\EnMarche;

use App\Mailer\Message\BoardMemberContactAdherentsMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group boardMember
 */
class BoardMemberControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testUnauthorizeToAccessOnBoardMemberArea()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche/message');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes/message');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/message/2');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_POST, '/espace-membres-conseil/list/boardmember');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_DELETE, '/espace-membres-conseil/list/boardmember/2');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testIndexBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Referent', $crawler->filter('h1')->text());
    }

    public function testSearchBoardMember()
    {
        $this->authenticateAsBoardMember();

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $resultRow = $crawler->filter('.spaces__results__row');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Tous les résultats (8)', $crawler->filter('h2')->first()->text());
        $this->assertSame(8, $resultRow->count());
        $this->assertSame('Carl Mirabeau', $resultRow->eq(0)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, M, Mouxy/', $resultRow->eq(0)->filter('li')->eq(1)->filter('div')->eq(1)->text());
        $this->assertSame('Laura Deloche', $resultRow->eq(1)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, F, Rouen/', $resultRow->eq(1)->filter('li')->eq(1)->filter('div')->eq(1)->text());
        $this->assertSame('Martine Lindt', $resultRow->eq(2)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, F, Berlin/', $resultRow->eq(2)->filter('li')->eq(1)->filter('div')->eq(1)->text());
        $this->assertSame('Élodie Dutemps', $resultRow->eq(3)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, F, Singapour/', $resultRow->eq(3)->filter('li')->eq(1)->filter('div')->eq(1)->text());
        $this->assertSame('Député PARIS I', $resultRow->eq(4)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, M, Paris/', $resultRow->eq(4)->filter('li')->eq(1)->filter('div')->eq(1)->text());
        $this->assertSame('Député PARIS II', $resultRow->eq(5)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, M, Paris/', $resultRow->eq(5)->filter('li')->eq(1)->filter('div')->eq(1)->text());
        $this->assertSame('Député CHLI FDESIX', $resultRow->eq(6)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, M, Paris/', $resultRow->eq(6)->filter('li')->eq(1)->filter('div')->eq(1)->text());
        $this->assertSame('Referent Referent', $resultRow->eq(7)->filter('li')->eq(1)->filter('.text--bold')->first()->text());
        $this->assertMatchesRegularExpression('/\d+, M, Melun/', $resultRow->eq(7)->filter('li')->eq(1)->filter('div')->eq(1)->text());

        // Gender
        $this->client->submit($this->client->getCrawler()->selectButton('Rechercher')->form(['g' => 'male']));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $results = $this->client->getCrawler()->filter('.spaces__results__row');
        $this->assertCount(5, $results);
        $this->assertStringContainsString('Carl Mirabeau', $results->first()->text());
        $this->assertStringContainsString('Député PARIS I', $results->eq(1)->text());
        $this->assertStringContainsString('Député PARIS II', $results->eq(2)->text());
        $this->assertStringContainsString('Député CHLI FDESIX', $results->eq(3)->text());
        $this->assertStringContainsString('Referent Referent', $results->eq(4)->text());

        // Age
        $this->client->submit($this->client->getCrawler()->selectButton('Rechercher')->form([
            'g' => null,
            'amin' => 43,
            'amax' => 1 + (int) $resultRow->eq(1)->filter('li')->eq(1)->filter('div')->eq(1)->text(),
        ]));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $results = $this->client->getCrawler()->filter('.spaces__results__row');
        $this->assertCount(2, $results);
        $this->assertStringContainsString('Laura Deloche', $results->first()->text());

        // Name
        $this->client->submit($this->client->getCrawler()->selectButton('Rechercher')->form([
            'amin' => null,
            'amax' => null,
            'f' => 'Martine',
            'l' => 'Lindt',
        ]));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $results = $this->client->getCrawler()->filter('.spaces__results__row');
        $this->assertCount(1, $results);
        $this->assertStringContainsString('Martine Lindt', $results->first()->text());

        // Postal Code
        $this->client->submit($this->client->getCrawler()->selectButton('Rechercher')->form([
            'f' => null,
            'l' => null,
            'p' => '368645',
        ]));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $results = $this->client->getCrawler()->filter('.spaces__results__row');
        $this->assertCount(1, $results);
        $this->assertStringContainsString('Élodie Dutemps', $results->first()->text());

        // Area
        $form = $this->client->getCrawler()->selectButton('Rechercher')->form();
        $form['a[0]']->tick();
        $form['p'] = null;
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $results = $this->client->getCrawler()->filter('.spaces__results__row');
        $this->assertCount(3, $results);
        $this->assertStringContainsString('Laura Deloche', $results->first()->text());
        $this->assertStringContainsString('Député PARIS I', $results->eq(1)->text());
        $this->assertStringContainsString('Referent Referent', $results->eq(2)->text());

        // Role
        $form = $this->client->getCrawler()->selectButton('Rechercher')->form();
        $form['a[0]']->untick();
        $form['r[2]']->tick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $results = $this->client->getCrawler()->filter('.spaces__results__row');
        $this->assertCount(1, $results);
        $this->assertStringContainsString('Referent Referent', $results->first()->text());
    }

    public function testSavedProfilBoardMember()
    {
        $this->authenticateAsBoardMember();

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes');
        $members = $crawler->filter('.spaces__results__row');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(4, $members);
        $this->assertStringContainsString('Carl Mirabeau', $members->first()->text());
        $this->assertMatchesRegularExpression('/\d+, M, Mouxy/', $members->first()->text());
        $this->assertStringContainsString('Laura Deloche', $members->eq(1)->text());
        $this->assertMatchesRegularExpression('/\d+, F, Rouen/', $members->eq(1)->text());
        $this->assertStringContainsString('Martine Lindt', $members->eq(2)->text());
        $this->assertMatchesRegularExpression('/\d+, F, Berlin/', $members->eq(2)->text());
        $this->assertStringContainsString('Élodie Dutemps', $members->eq(3)->text());
        $this->assertMatchesRegularExpression('/\d+, F, Singapour/', $members->eq(3)->text());
        $this->assertStringContainsString('4 profils sauvegardés', $crawler->filter('h2')->eq(1)->text());

        // Statistics
        $stats = $crawler->filter('#saved_board_members_statistics');
        $this->assertStringContainsString('75% femmes / 25% hommes', $stats->html());
        $this->assertStringContainsString('39 ans de moyenne d\'âge', $stats->html());
        $this->assertStringContainsString('25% Métropole / 0% DOM-TOM / 75% Étranger', $stats->html());
    }

    public function testSendMessageToSearchResult()
    {
        $this->authenticateAsBoardMember();

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Rechercher')->form(['g' => 'male']));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($this->client->getCrawler()->selectLink('Envoyer un message à ces 5 personnes')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form([
            'board_member_message' => [
                'subject' => 'Sujet',
                'content' => 'Message from search',
            ],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertCount(1, $this->getEmailRepository()->findMessages(BoardMemberContactAdherentsMessage::class));
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'carl999@example.fr');
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'referent@en-marche-dev.fr');
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'jemarche@en-marche.fr');
    }

    public function testSendMessageToSavedMembers()
    {
        $this->authenticateAsBoardMember();

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($this->client->getCrawler()->selectLink('Envoyer un message à ces 4 personnes')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertStringContainsString('4 membres du Conseil', $this->client->getResponse()->getContent());
        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form([
            'board_member_message' => [
                'subject' => 'Sujet',
                'content' => 'Message for my special members',
            ],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertCount(1, $this->getEmailRepository()->findMessages(BoardMemberContactAdherentsMessage::class));
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'carl999@example.fr');
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'laura@deloche.com');
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'martine.lindt@gmail.com');
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'lolodie.dutemps@hotnix.tld');
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'jemarche@en-marche.fr');
    }

    public function testSendMessageToMember()
    {
        $this->authenticateAsBoardMember();

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($this->client->getCrawler()->filter('.spaces__results__row')->eq(1)->selectLink('Envoyer un message')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertStringContainsString('Un membre du Conseil', $this->client->getResponse()->getContent());
        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form([
            'board_member_message' => [
                'subject' => 'Sujet',
                'content' => 'Message for one member',
            ],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertCount(1, $this->getEmailRepository()->findMessages(BoardMemberContactAdherentsMessage::class));
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'laura@deloche.com');
        $this->assertCountMails(1, BoardMemberContactAdherentsMessage::class, 'jemarche@en-marche.fr');
    }

    private function authenticateAsBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'kiroule.p@blabla.tld');
    }

    public function testSaveBoardMemberOnList()
    {
        $this->authenticateAsBoardMember();

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');
        $idBoardMemberToAdd = $crawler
            ->filter('.spaces__results__row')
            ->eq(4)
            ->filter('.btn-add-member-list')
            ->attr('data-memberid')
        ;

        $this->client->request(Request::METHOD_POST, '/espace-membres-conseil/list/boardmember', [
            'boardMemberId' => $idBoardMemberToAdd,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $this->client->request(Request::METHOD_POST, '/espace-membres-conseil/list/boardmember', [
            'boardMember' => 1234,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());

        $this->client->request(Request::METHOD_POST, '/espace-membres-conseil/list/boardmember', [
            'boardMemberId' => 99999,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testDeleteBoardMemberOnList()
    {
        $this->authenticateAsBoardMember();
        $this->client->request(Request::METHOD_DELETE, '/espace-membres-conseil/list/boardmember/2');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->request(Request::METHOD_DELETE, '/espace-membres-conseil/list/boardmember/9999');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
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
