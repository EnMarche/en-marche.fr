<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group map
 */
class MapControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommitteesMap()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des comités")')->count());
        $this->assertStringContainsString('63 adhérents', $crawler->filter('#counter-adherents')->text());
        $this->assertStringContainsString('13 comités', $crawler->filter('#counter-committees')->text());
        $this->assertStringContainsString('20 événements', $crawler->filter('#counter-events')->text());
    }

    public function testCommitteesEventsMap()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des événements")')->count());
        $this->assertStringContainsString('Tous (10)', $crawler->filter('.events-map-categories--all')->first()->text());
    }
}
