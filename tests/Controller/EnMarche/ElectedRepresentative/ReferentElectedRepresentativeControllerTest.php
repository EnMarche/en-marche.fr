<?php

namespace Tests\App\Controller\EnMarche\ElectedRepresentative;

use App\DataFixtures\ORM\LoadElectedRepresentativeData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ReferentElectedRepresentativeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $this->assertCount(5, $crawler->filter('tbody tr.referent__item'));
        $this->assertCount(2, $crawler->filter('.status.status__1'));
        $this->assertStringContainsString('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('Conseiller(e) municipal(e) (NC)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('Clichy (92024)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('Maire', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringNotContainsString('Président(e) d\'EPCI', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('PS (2016)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('Non', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertStringContainsString('Conseiller(e) municipal(e) (DIV)', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertStringContainsString('Rouen (76540)', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertStringContainsString(' G.s (2018) PS (2014 à 2018)', preg_replace('/\s+/', ' ', $crawler->filter('tbody tr.referent__item')->eq(1)->filter('td')->eq(4)->text()));
        $this->assertStringContainsString('Non', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertStringContainsString('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertStringContainsString('Oui', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertStringContainsString('LOBELL', $crawler->filter('tbody tr.referent__item')->eq(3)->text());
    }

    public function testFilterElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $this->assertCount(5, $crawler->filter('tbody tr.referent__item'));

        // filter by lastname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = 'BOU';

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by firstname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = 'BOU';
        $form['f[firstName]'] = 'Delphine';

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by labels
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = '';
        $form['f[firstName]'] = '';
        $form['f[labels]'] = ['PS', 'LaREM'];

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by politicalFunctions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[labels]'] = ['PS', 'LaREM'];
        $form['f[politicalFunctions]'] = ['mayor'];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by mandates
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[labels]'] = [];
        $form['f[politicalFunctions]'] = [];
        $form['f[mandates]'] = ['membre_EPCI', 'depute'];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('LOBELL André', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by gender
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[mandates]'] = [];
        $form['f[gender]'] = 'female';

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[gender]'] = 'unknown';

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter not adherents
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[gender]'] = '';
        $form['f[contactType]'] = 'other';

        $crawler = $this->client->submit($form);

        $this->assertCount(3, $crawler->filter('tbody tr.referent__item'));
    }

    public function testListElectedRepresentativesForParis()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $text = $crawler->filter('tbody')->text();

        $this->assertCount(4, $crawler->filter('tr.referent__item'));

        $this->assertStringContainsString('PARIS Département', $text);
        $this->assertStringContainsString('PARIS Arrondissement', $text);
        $this->assertStringContainsString('PARISS Circonscription', $text);
        $this->assertStringContainsString('Métropole du Grand Paris', $text); // DUFOUR Michelle's mandate
    }

    public function testShowElectedRepresentative()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_1_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString('Michelle DUFOUR', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertStringContainsString('François Hollande', $crawler->filter('.elected-representative-sponsorships tbody tr')->eq(0)->text());
        $this->assertStringContainsString('Emmanuel Macron', $crawler->filter('.elected-representative-sponsorships tbody tr')->eq(1)->text());
        $this->assertCount(1, $crawler->filter('.elected-representative-mandates tbody tr'));
        $this->assertStringContainsString('Conseiller(e) municipal(e)', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertStringContainsString('REM', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertStringContainsString('Soutien officiel', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertStringContainsString('23 juillet 2019', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertStringContainsString(' Autre membre (2019) Président(e) d\'EPCI (2015)', preg_replace('/\s+/', ' ', $crawler->filter('.elected-representative-mandates tbody tr')->eq(0)->filter('td')->eq(5)->text()));
        $this->assertCount(1, $crawler->filter('.elected-representative-labels tbody tr'));
        $this->assertStringNotContainsString('Aucune', $crawler->filter('.elected-representative-labels tbody tr')->text());
        $this->assertCount(0, $crawler->filter('.sponsorships tbody tr'));
        $this->assertCount(1, $crawler->filter('.elected-representative-candidacies tbody tr'));
        $this->assertStringContainsString('Aucune', $crawler->filter('.elected-representative-candidacies tbody tr')->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_2_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString('Delphine BOUILLOUX', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertCount(5, $crawler->filter('.elected-representative-social-networks a'));

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_3_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString('Daniel BOULON', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertCount(1, $crawler->filter('.elected-representative-mandates tbody tr'));
        $this->assertCount(2, $crawler->filter('.elected-representative-labels tbody tr'));
        $this->assertCount(0, $crawler->filter('.sponsorships tbody tr'));
        $this->assertCount(1, $crawler->filter('.elected-representative-candidacies tbody tr'));
        $this->assertStringContainsString('Membre d\'EPCI', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertStringContainsString('DIV', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertStringContainsString('Pas soutenu', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertStringContainsString('11 janvier 2017', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
