<?php

namespace Tests\App\Controller\EnMarche\Designation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group voting-platform
 */
class SupervisorDesignationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testProvisionalSupervisorCannotSeeDesignationsOfHisCommittee(): void
    {
        self::authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-comite-de-evry/editer');

        $this->isSuccessful($this->client->getResponse());

        $this->assertCount(0, $crawler->selectLink('Désignations'));

        $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-comite-de-evry/designations');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testProvisionalSupervisorCannotSeeDesignationsOfHisCommittee2(): void
    {
        self::authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-comite-de-evry/designations');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testSupervisorCanSeeAllDesignationsOfHisCommittee(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8/editer');

        $this->isSuccessful($this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Désignations')->link());

        $this->assertStringEndsWith('/espace-animateur/en-marche-paris-8/designations', $crawler->getUri());

        $this->assertStringContainsString('Désignation du binôme d’adhérents siégeant au Conseil territorial', $this->client->getResponse()->getContent());
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testSupervisorCanSeeSubscribedVotersCommittee(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-paris-8/designations');
        $link = $crawler->selectLink('Voir les détails')->link();
        $crawler = $this->client->click($link);

        $this->isSuccessful($this->client->getResponse());

        $overviewCards = $crawler->filter('.manager-overview__case');
        $this->assertCount(3, $overviewCards);

        // Voter box
        $votersBox = $overviewCards->eq(0);
        $this->assertStringContainsString('11', trim($votersBox->filter('.manager-overview__case--value')->text()));
        $this->assertStringContainsString('Inscrits', trim($votersBox->filter('.manager-overview__case--title')->text()));

        // Vote box
        $votesBox = $overviewCards->eq(1);
        $this->assertStringContainsString('72.73%', trim($votesBox->filter('.manager-overview__case--value')->text()));
        $this->assertStringContainsString('Participants', trim($votesBox->filter('.manager-overview__case--title')->text()));

        // Candidate box
        $candidateBox = $overviewCards->eq(2);
        $this->assertStringContainsString('5Femme', trim($candidateBox->filter('.manager-overview__case--value')->text()));
        $this->assertStringContainsString('5Homme', trim($candidateBox->filter('.manager-overview__case--value')->text()));
        $this->assertStringContainsString('Candidatures', trim($candidateBox->filter('.manager-overview__case--title')->text()));

        $crawler = $this->client->click($crawler->selectLink('Liste d\'émargement')->link());

        $this->assertStringContainsString('Liste des inscrits / émargements', $crawler->filter('.datagrid__pre-table')->eq(1)->text());
        $this->assertMatchesRegularExpression('#/liste-emargement/[\d\w-]{36}#', $crawler->getUri());
        $this->assertCount(11, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertStringContainsString('Bob Assesseur', $crawler->filter('.datagrid__table-manager tbody')->text());
    }

    public function testSupervisorCanSeeCandidateResultsPage(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-paris-8/designations');
        $crawler = $this->client->click($crawler->selectLink('Voir les détails')->link());
        $crawler = $this->client->click($crawler->selectLink('Résultats Femme')->link());

        $this->isSuccessful($this->client->getResponse());
        $this->assertMatchesRegularExpression('#/resultats/[\d\w-]{36}\?code=female#', $crawler->getUri());

        $this->assertStringContainsString('Résultats par scrutin : Femme', $crawler->filter('.datagrid__pre-table')->eq(1)->text());

        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Résultats Homme')->link());

        $this->isSuccessful($this->client->getResponse());
        $this->assertMatchesRegularExpression('#/resultats/[\d\w-]{36}\?code=male#', $crawler->getUri());

        $this->assertStringContainsString('Résultats par scrutin : Homme', $crawler->filter('.datagrid__pre-table')->eq(1)->text());

        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testSupervisorCanSeeVoteListPage(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-paris-8/designations');
        $crawler = $this->client->click($crawler->selectLink('Voir les détails')->link());
        $crawler = $this->client->click($crawler->selectLink('Bulletins dépouillés')->link());

        $this->isSuccessful($this->client->getResponse());
        $this->assertMatchesRegularExpression('#/bulletins/[\d\w-]{36}#', $crawler->getUri());

        $this->assertStringContainsString('Liste des bulletins dépouillés', $crawler->filter('.datagrid__pre-table')->eq(1)->text());

        $this->assertCount(8, $crawler->filter('.datagrid__table-manager tbody tr'));
    }
}
