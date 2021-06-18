<?php

namespace Tests\App\Controller\Legislatives;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group legislatives
 */
class HomeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testLegislativesCandidatesDirectory()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $candidates = $crawler->filter('.legislatives_candidate');

        // Check the order of candidates
        $this->assertSame(16, $candidates->count());
        $this->assertSame('Alban Martin', $candidates->first()->filter('h1')->text());
        $this->assertSame('Paul Arty', $candidates->eq(1)->filter('h1')->text());
        $this->assertSame('Michel Patulacci', $candidates->eq(2)->filter('h1')->text());
        $this->assertSame('Josiane Dupuis', $candidates->eq(3)->filter('h1')->text());
        $this->assertSame('Nathan Enquillé', $candidates->eq(4)->filter('h1')->text());
        $this->assertSame('Michelle Dumoulin', $candidates->eq(5)->filter('h1')->text());
        $this->assertSame('Pierre Etchebest', $candidates->eq(6)->filter('h1')->text());
        $this->assertSame('Monique Albert', $candidates->eq(7)->filter('h1')->text());
        $this->assertSame('Etienne de Monté-Cristo', $candidates->eq(8)->filter('h1')->text());
        $this->assertSame('Valérie Langlade', $candidates->eq(9)->filter('h1')->text());
        $this->assertSame('Isabelle Piémontaise', $candidates->eq(10)->filter('h1')->text());
        $this->assertSame('Estelle Antonov', $candidates->eq(11)->filter('h1')->text());
        $this->assertSame('Jacques Arditi', $candidates->eq(12)->filter('h1')->text());
        $this->assertSame('Albert Bérégovoy', $candidates->eq(13)->filter('h1')->text());
        $this->assertSame('Franck de Lavalle', $candidates->eq(14)->filter('h1')->text());
        $this->assertSame('Emmanuelle Parfait', $candidates->last()->filter('h1')->text());

        $crawler = $this->client->click($crawler->selectLink('Alban Martin')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Check profile information of the first candidate
        $profile = $crawler->filter('#candidate-profile');
        $description = $crawler->filter('#candidate-description');
        $links = $profile->filter('a');

        $this->assertSame(1, $profile->filter('#candidate-profile-picture')->count());
        $this->assertSame('Alban Martin', $profile->filter('h1')->text());
        $this->assertSame("Troisième circonscription de l'Ain", $profile->filter('#candidate-district-name')->text());
        $this->assertSame('https://albanmartin.en-marche-dev.fr', $links->first()->attr('href'));
        $this->assertSame('https://twitter.com/albanmartin-fake', $links->eq(1)->attr('href'));
        $this->assertSame('https://www.facebook.com/albanmartin-fake', $links->eq(2)->attr('href'));
        $this->assertSame('https://albanmartin.en-marche-dev.fr/dons', $links->last()->attr('href'));
        $this->assertSame(4, $description->filter('p')->count());

        $crawler = $this->client->click($crawler->selectLink('Retour à la liste des candidats')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Emmanuelle Parfait')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Check profile information of the last candidate
        $profile = $crawler->filter('#candidate-profile');
        $description = $crawler->filter('#candidate-description');

        $this->assertSame(1, $profile->filter('#candidate-profile-picture')->count());
        $this->assertSame('Emmanuelle Parfait', $profile->filter('h1')->text());
        $this->assertSame('Onzième circonscription des Français établis hors de France', $profile->filter('#candidate-district-name')->text());
        $this->assertSame(0, $profile->filter('.candidate_links')->count());
        $this->assertSame(2, $description->filter('p')->count());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('legislatives_host'));
    }
}
