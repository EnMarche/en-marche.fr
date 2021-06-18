<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class BiographyControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCanSeeOurOrganization(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/notre-organisation');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertStringContainsString(
            'CASTANER Christophe',
            $crawler->filter('#biography .executive-office-leaders > ul li:nth-child(1)')->text()
        );

        $this->assertStringContainsString(
            "Secrétaire d'État auprès du Premier ministre, chargé des Relations avec le Parlement.",
            $crawler->filter('#biography .executive-office-leaders > ul li:nth-child(1) .description')->text()
        );

        $this->assertStringContainsString(
            'P. Pierre',
            $crawler->filter('#biography .executive-office-leaders > ul li:nth-child(2)')->text()
        );

        $this->assertStringContainsString(
            'ABADIE Caroline',
            $crawler->filter('#biography .executive-office-members > ul li:nth-child(1)')->text()
        );

        $this->assertStringContainsString(
            'AGAMENNONE Béatrice',
            $crawler->filter('#biography .executive-office-members > ul li:nth-child(2)')->text()
        );

        $this->assertStringContainsString(
            'AVIA Laëtitia',
            $crawler->filter('#biography .executive-office-members > ul li:nth-child(3)')->text()
        );
    }

    public function testAnonymousUserCanSeeBiographyProfile(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET, '/le-mouvement/notre-organisation/christophe-castaner'
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertStringContainsString('Christophe CASTANER', $crawler->filter('#biography .profile-header h1')->text());

        $this->assertStringContainsString(
            'Délégué général du mouvement',
            $crawler->filter('#biography .profile-header span')->text()
        );

        $this->assertCount(4, $crawler->filter('#biography .profile-header .social-networks a'));

        $this->assertStringContainsString(
            'Christophe Castaner, né le 3 janvier 1966 à Ollioules, est un juriste et homme politique français.',
            $crawler->filter('#biography .profile-content p')->text()
        );
    }
}
