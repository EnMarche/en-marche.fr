<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group summary
 */
class SummaryControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function provideSummarySlug(): \Generator
    {
        yield ['carl-mirabeau'];

        yield ['jacques-picard'];
    }

    /**
     * @dataProvider provideSummarySlug
     */
    public function testUnpublishedSummaryAreNotFound(string $slug)
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $this->client->request(Request::METHOD_GET, '/membre/'.$slug);

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testNoAccessToSUmmaryIfNotLogged()
    {
        $this->client->request(Request::METHOD_GET, '/membre/lucie-olivera');

        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testAccessAndDisplaySummaryPage()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/membre/lucie-olivera');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame(1, $crawler->filter('.summary-contact-email')->count());
        $this->assertSame(2, $crawler->filter('.cv__experience h3')->count());
        $this->assertSame('CDI BIO-INFORMATICIEN', $crawler->filter('.cv__experience h3')->eq(0)->text());
        $this->assertSame('Institut KNURE', $crawler->filter('.cv__experience h4')->eq(0)->text());
        $this->assertSame('CDI PROFESSEUR', $crawler->filter('.cv__experience h3')->eq(1)->text());
        $this->assertSame('Univérsité Lyon 1', $crawler->filter('.cv__experience h4')->eq(1)->text());
        $this->assertSame(3, $crawler->filter('.cv__languages div')->count());
        $this->assertSame(1, $crawler->filter('.cv__languages div:contains("Français - Langue maternelle")')->count());
        $this->assertSame(1, $crawler->filter('.cv__languages div:contains("Anglais - Maîtrise parfaite")')->count());
        $this->assertSame(1, $crawler->filter('.cv__languages div:contains("Espagnol - Bonne maîtrise")')->count());
        $this->assertSame(4, $crawler->filter('.cv__skills li')->count());
        $this->assertSame(1, $crawler->filter('.cv__skills li:contains("Software")')->count());
        $this->assertSame(1, $crawler->filter('.cv__skills li:contains("Analyze")')->count());
        $this->assertSame(1, $crawler->filter('.cv__skills li:contains("Mathématiques")')->count());
        $this->assertSame(1, $crawler->filter('.cv__skills li:contains("Statistique")')->count());
        $this->assertSame(4, $crawler->filter('.cv__training div')->count());
        $this->assertSame(1, $crawler->filter('.cv__training h3:contains("DIPLÔME D\'INGÉNIEUR - BIO-INFORMATIQUE")')->count());
        $this->assertSame(1, $crawler->filter('.cv__training h3:contains("DUT GÉNIE BIOLOGIQUE - BIO-INFORMATIQUE")')->count());
        $this->assertSame(1, $crawler->filter('.cv__training__desc:contains("Master en Bio-Informatique")')->count());
        $this->assertSame(1, $crawler->filter('.cv__training__desc:contains("Génie biologique option Bio-Informatique")')->count());
        $this->assertSame('mailto:luciole1989@spambox.fr', $crawler->filter('.summary-contact-email a')->attr('href'));
        $this->assertSame('https://www.facebook.com/lucie-olivera-fake', $crawler->filter('.summary-contact-facebook a')->attr('href'));
        $this->assertSame('https://twitter.com/lucie-olivera-fake', $crawler->filter('.summary-contact-twitter a')->attr('href'));

        $this->assertSame(0, $crawler->filter('.cv__skills--modify:contains("Modifier")')->count());
    }
}
