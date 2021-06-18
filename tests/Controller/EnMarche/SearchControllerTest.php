<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\Event\CommitteeEvent;
use App\Search\SearchParametersFilter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class SearchControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideQuery
     */
    public function testIndex($query)
    {
        $this->client->request(Request::METHOD_GET, '/recherche', $query);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    /**
     * @dataProvider providerPathSearchPage
     */
    public function testAccessSearchPage(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function providerPathSearchPage(): array
    {
        return [
            ['/evenements'],
            ['/comites'],
            ['/recherche'],
        ];
    }

    public function provideQuery(): \Generator
    {
        yield 'No criteria' => [[]];
        yield 'Search committees' => [[SearchParametersFilter::PARAMETER_TYPE => SearchParametersFilter::TYPE_COMMITTEES]];
        yield 'Search events' => [[SearchParametersFilter::PARAMETER_TYPE => SearchParametersFilter::TYPE_EVENTS]];
    }

    public function testListAllEvents()
    {
        /** @var Paginator $evenets */
        $events = $this->getRepository(CommitteeEvent::class)->paginate();

        $this->client->request(Request::METHOD_GET, '/tous-les-evenements/3');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request(Request::METHOD_GET, '/tous-les-evenements/1');

        $this->assertSame($events->count(), $crawler->filter('div.search__results__row')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="prev"]')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="next"]')->count());
        $this->assertSame(1, $crawler->filter('.listing__paginator li')->count());
        $this->assertSame('/tous-les-evenements', $crawler->filter('.listing__paginator li a')->attr('href'));
        $this->assertSame('1', trim($crawler->filter('.listing__paginator li a')->text()));
    }

    public function testListEventsByCategory()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(5, $crawler->filter('div.search__results__row')->count());

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/categorie/conference-debat');

        $this->assertSame(1, $crawler->filter('div.search__results__row')->count());
        $this->assertSame('Conférence-débat', $crawler->filter('.search__results__info .search__results__tag div')->text());
        $this->assertSame('Réunion de réflexion évryenne', trim($crawler->filter('.search__results__info .search__results__meta h2 a')->text()));

        $this->client->request(Request::METHOD_GET, '/evenements/categorie/inexistante');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/evenements', $this->client);
    }

    public function testListAllCommittee()
    {
        $this->client->request(Request::METHOD_GET, '/tous-les-comites/3');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request(Request::METHOD_GET, '/tous-les-comites/1');

        $this->assertSame(13, $crawler->filter('.search__committee__box')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="prev"]')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="next"]')->count());
        $this->assertSame(1, $crawler->filter('.listing__paginator li')->count());
        $this->assertSame('/tous-les-comites', $crawler->filter('.listing__paginator li a')->attr('href'));
        $this->assertSame('1', trim($crawler->filter('.listing__paginator li a')->text()));
    }
}
