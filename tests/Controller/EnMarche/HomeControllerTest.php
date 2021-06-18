<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group home
 */
class HomeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testIndex(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->isSuccessful($this->client->getResponse());

        // Articles
        // $this->assertSame(1, $crawler->filter('html:contains("« Je viens échanger, comprendre et construire. »")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Tribune de Richard Ferrand")')->count());

        // Live links
        $this->assertSame(1, $crawler->filter('html:contains("Guadeloupe")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Le candidat du travail")')->count());
    }

    public function testHealth(): void
    {
        $this->client->request(Request::METHOD_GET, '/health');

        $this->isSuccessful($this->client->getResponse());
    }

    /**
     * @dataProvider provideSitemaps
     */
    public function testSitemaps(string $page): void
    {
        $this->client->request(Request::METHOD_GET, $page);

        $this->isSuccessful($this->client->getResponse());
    }

    /**
     * @dataProvider provideEmptySitemaps
     */
    public function testEmptySitemaps(string $page): void
    {
        $this->client->request(Request::METHOD_GET, $page);

        $this->assertClientIsRedirectedTo('/sitemap.xml', $this->client, false, true);
    }

    public function provideSitemaps(): array
    {
        return [
            ['/sitemap.xml'],
            ['/sitemap_main_1.xml'],
            ['/sitemap_content_1.xml'],
            ['/sitemap_committees_1.xml'],
            ['/sitemap_events_1.xml'],
            ['/sitemap_images_1.xml'],
        ];
    }

    public function provideEmptySitemaps(): array
    {
        return [
            ['/sitemap_committees_42.xml'],
            ['/sitemap_events_42.xml'],
        ];
    }

    public function testDynamicRedirections(): void
    {
        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-301/?test=123');

        $this->assertClientIsRedirectedTo('/evenements', $this->client, false, true);

        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-302');

        $this->assertClientIsRedirectedTo('/comites', $this->client);

        $this->client->request(Request::METHOD_GET, '/dynamic-redirection/');

        $this->assertClientIsRedirectedTo('/articles', $this->client, false, true);

        $this->client->request(Request::METHOD_GET, '/dynamic-redirection');

        $this->assertClientIsRedirectedTo('/articles', $this->client, false, true);
    }

    /**
     * @dataProvider provideUrlsAndRedirections
     */
    public function testRemoveTrailingSlashAction(string $uri, string $redirectUri)
    {
        $this->client->request(Request::METHOD_GET, $uri);

        $this->assertClientIsRedirectedTo($redirectUri, $this->client, true, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function provideUrlsAndRedirections(): \Generator
    {
        yield 'Emmanuel Macron' => ['/emmanuel-macron/', '/emmanuel-macron'];
        yield 'Le mouvement' => ['/le-mouvement/', '/le-mouvement'];
        yield 'Actualités' => ['/articles/actualites/', '/articles/actualites'];
        yield 'Inscription' => ['/adhesion/', '/adhesion'];
        yield 'Inscription with parameters' => ['/adhesion/?param1=value1&param2=value2', '/adhesion?param1=value1&param2=value2'];
    }
}
