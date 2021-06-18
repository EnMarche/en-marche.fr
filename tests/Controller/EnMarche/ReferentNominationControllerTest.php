<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class ReferentNominationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testOurReferentsDirectory()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/nos-referents');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $referents = $crawler->filter('.legislatives_candidate');

        // Check the order of candidates
        $this->assertSame(2, $referents->count());
        $this->assertSame('Nicolas Bordes', $referents->first()->filter('h1')->text());
        $this->assertSame('Jean Dupont', $referents->eq(1)->filter('h1')->text());

        $crawler = $this->client->click($crawler->selectLink('Nicolas Bordes')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        // Check profile information of the first candidate
        $profile = $crawler->filter('#candidate-profile');
        $description = $crawler->filter('#candidate-description');
        $links = $profile->filter('a');

        $this->assertSame(1, $profile->filter('#candidat-profile-picture')->count());
        $this->assertSame('Nicolas Bordes', $profile->filter('h1')->text());
        $this->assertSame("Côte d'Or", $profile->filter('#candidate-district-name')->text());
        $this->assertSame('https://twitter.com/nyko24', $links->first()->attr('href'));
        $this->assertSame('https://www.facebook.com/nyko24', $links->eq(1)->attr('href'));
        $this->assertSame(4, $description->filter('p')->count());

        $this->client->click($crawler->selectLink('Retour à la liste des référents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }
}
