<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class FormationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testFormationHomepageIsProtected(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-formation');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testFormationFaqIsProtected(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-formation/faq');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testAuthenticatedUserSeeHisFormation(): void
    {
        $this->authenticateAsAdherent($this->client, 'lolodie.dutemps@hotnix.tld');
        $this->client->request(Request::METHOD_GET, '/espace-formation');

        $this->assertStringContainsString('Première visite ?', $content = $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Premier article du premier axe', $content);
        $this->assertStringContainsString('Deuxième article du premier axe', $content);
        $this->assertStringContainsString('Premier article du deuxième axe', $content);
        $this->assertStringContainsString('Deuxième article du deuxième axe', $content);
    }

    public function testMunicipalChiefCanAccessFormationSection(): void
    {
        $this->authenticateAsAdherent($this->client, 'municipal-chief@en-marche-dev.fr');
        $this->client->request(Request::METHOD_GET, '/espace-formation');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testIfArticlesAreClickableInFormationHomepage(): void
    {
        $this->authenticateAsAdherent($this->client, 'lolodie.dutemps@hotnix.tld');
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-formation');

        $link = $crawler
            ->filter('a:contains("Premier article du premier axe")')
            ->eq(0)
            ->link()
        ;

        $this->client->click($link);

        $this->assertStringContainsString('An exhibit of Markdown', $this->client->getResponse()->getContent());
    }
}
