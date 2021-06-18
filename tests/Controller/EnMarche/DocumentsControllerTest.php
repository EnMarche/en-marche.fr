<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group documents
 */
class DocumentsControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testDocumentsIndexAsAnonymous()
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testDocumentsIndexAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Adhérents")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Animateurs")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    public function testDocumentsIndexAsHost()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Adhérents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Animateurs")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    public function testDocumentsIndexAsReferent()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Adhérents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Animateurs")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    public function testDocumentsDirectory()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $link = $crawler->filter('ul.documents__tree li a:contains("dir2")');
        $this->assertCount(1, $link);

        $crawler = $this->client->click($link->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Adhérents")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Animateurs")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("subdir")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    public function testDocumentsDirectoryUnauthorized()
    {
        $documentsRoot = '/espace-adherent/documents/dossier';

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/dir3');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/dir4');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/dir4');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/dir3');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testDocumentsRead()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $link = $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")');
        $this->assertCount(1, $link);

        $this->client->click($link->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function testDocumentsReadUnauthorized()
    {
        $documentsRoot = '/espace-adherent/documents/telecharger';

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/document-host-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/document-host-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/document-referent-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/document-host-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->logout($this->client);
    }
}
