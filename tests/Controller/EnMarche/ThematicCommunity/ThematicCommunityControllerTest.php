<?php

namespace Tests\App\Controller\EnMarche\ThematicCommunity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ControllerTestTrait;

class ThematicCommunityControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testDisplayCommunitiesAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertCount(2, $crawler->filter('#thematic-community .list .list--item'));

        $this->assertEquals('Pour la santé', $crawler->filter('#thematic-community .list .list--item')->first()->filter('h2')->text());
        $this->assertEquals('Je m\'inscris', $crawler->filter('#thematic-community .list .list--item')->first()->filter('a')->text());

        $this->assertEquals('Pour l\'école', $crawler->filter('#thematic-community .list .list--item')->eq(1)->filter('h2')->text());
        $this->assertEquals('Modifier mes préférences', $crawler->filter('#thematic-community .list .list--item')->eq(1)->filter('a')->text());
    }

    public function testJoinAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques');

        $crawler = $this->client->click($crawler->selectLink('Je m\'inscris')->link());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertStringNotContainsString('Informations personnelles', $crawler->filter('h2.subtitle')->text());

        $crawler = $this->client->submit($crawler->selectButton('Je m\'inscris')->form([
            'thematic_community_membership[hasJob]' => 1,
            'thematic_community_membership[job]' => 'Médecin',
            'thematic_community_membership[association]' => 0,
            'thematic_community_membership[motivations]' => ['information', 'thinking'],
        ]));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('http://test.enmarche.code/communautes-thematiques', $crawler->getUri());
        $this->assertEquals(
            'Nous vous avons envoyé un email à l\'adresse "referent@en-marche-dev.fr". Veuillez cliquer sur le lien contenu dans cet email pour confirmer votre inscription à la communauté.',
            trim($crawler->filter('.flash.flash--info')->text())
        );

        $this->assertEquals('Modifier mes préférences', $crawler->filter('#thematic-community .list .list--item')->first()->filter('a')->text());

        $crawler = $this->client->click($crawler->selectLink('Modifier mes préférences')->link());

        $this->assertMatchesRegularExpression('#http://test.enmarche.code/communautes-thematiques/adhesion/.{36}/modifier#', $crawler->getUri());
    }

    public function testICannotEditAMembershipThatIsNotMine()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/communautes-thematiques/adhesion/be8b1edb-b958-4054-bcc9-903ff39062dd/modifier');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request(Request::METHOD_GET, '/communautes-thematiques/adhesion/2420524f-f52e-46af-a945-327c48788d37/modifier');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testICanLeaveACommunityAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques/adhesion/be8b1edb-b958-4054-bcc9-903ff39062dd/modifier');
        $this->assertStringContainsString('Quitter cette communauté', $crawler->filter('#thematic-community')->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques/adhesion/be8b1edb-b958-4054-bcc9-903ff39062dd/quitter');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('http://test.enmarche.code/communautes-thematiques', $crawler->getUri());

        $this->assertEquals('Vous ne faites plus partie de la communauté Ecole.', trim($crawler->filter('.flash.flash--info')->text()));
    }

    public function testDisplayCommunityiesAsNotLoggedInUser()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertCount(2, $crawler->filter('#thematic-community .list .list--item'));

        $this->assertEquals('Pour la santé', $crawler->filter('#thematic-community .list .list--item')->first()->filter('h2')->text());
        $this->assertEquals('Je m\'inscris', $crawler->filter('#thematic-community .list .list--item')->first()->filter('a')->text());

        $this->assertEquals('Pour l\'école', $crawler->filter('#thematic-community .list .list--item')->eq(1)->filter('h2')->text());
        $this->assertEquals('Je m\'inscris', $crawler->filter('#thematic-community .list .list--item')->eq(1)->filter('a')->text());
    }

    public function testJoinAsContact()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques');

        $crawler = $this->client->click($crawler->selectLink('Je m\'inscris')->link());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertStringContainsString('Vous êtes adhérent LaREM ?', $crawler->filter('#thematic-community article.account-block')->text());
        $this->assertStringContainsString('Informations personnelles', $crawler->filter('h2.subtitle')->text());

        $crawler = $this->client->submit($crawler->selectButton('Je m\'inscris')->form([
            'thematic_community_membership[lastName]' => 'Richard',
            'thematic_community_membership[firstName]' => 'Pierre',
            'thematic_community_membership[gender]' => 'male',
            'thematic_community_membership[email]' => 'pierre.richard@oneblackshoe.com',
            'thematic_community_membership[phone][country]' => 'FR',
            'thematic_community_membership[phone][number]' => '606060606',
            'thematic_community_membership[position]' => 'employed',
            'thematic_community_membership[birthDate]' => '1934-08-16',
            'thematic_community_membership[postAddress][address]' => 'Rue des tests',
            'thematic_community_membership[postAddress][postalCode]' => '75007',
            'thematic_community_membership[postAddress][cityName]' => 'Paris',
            'thematic_community_membership[postAddress][country]' => 'FR',
            'thematic_community_membership[hasJob]' => 1,
            'thematic_community_membership[job]' => 'Médecin',
            'thematic_community_membership[association]' => 0,
            'thematic_community_membership[motivations]' => ['information', 'thinking'],
        ]));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('http://test.enmarche.code/communautes-thematiques', $crawler->getUri());
        $this->assertEquals(
            'Nous vous avons envoyé un email à l\'adresse "pierre.richard@oneblackshoe.com". Veuillez cliquer sur le lien contenu dans cet email pour confirmer votre inscription à la communauté.',
            trim($crawler->filter('.flash.flash--info')->text())
        );

        $this->assertEquals('Je m\'inscris', $crawler->filter('#thematic-community .list .list--item')->first()->filter('a')->text());
    }

    public function testContactCannotEditAMembership()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques/adhesion/2420524f-f52e-46af-a945-327c48788d37/modifier');
        $this->assertEquals('http://test.enmarche.code/connexion', $crawler->getUri());
    }

    public function testJoinAsContactButIHaveAnAdherentAccount()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques');

        $crawler = $this->client->click($crawler->selectLink('Je m\'inscris')->link());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->submit($crawler->selectButton('Je m\'inscris')->form([
            'thematic_community_membership[lastName]' => 'Do not',
            'thematic_community_membership[firstName]' => 'Matter',
            'thematic_community_membership[gender]' => 'male',
            'thematic_community_membership[email]' => 'referent@en-marche-dev.fr',
            'thematic_community_membership[phone][country]' => 'FR',
            'thematic_community_membership[phone][number]' => '606060606',
            'thematic_community_membership[position]' => 'employed',
            'thematic_community_membership[birthDate]' => '1934-08-16',
            'thematic_community_membership[postAddress][address]' => 'Rue des tests',
            'thematic_community_membership[postAddress][postalCode]' => '75007',
            'thematic_community_membership[postAddress][cityName]' => 'Paris',
            'thematic_community_membership[postAddress][country]' => 'FR',
            'thematic_community_membership[hasJob]' => 1,
            'thematic_community_membership[job]' => 'Médecin',
            'thematic_community_membership[association]' => 0,
            'thematic_community_membership[motivations]' => ['information', 'thinking'],
        ]));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('http://test.enmarche.code/communautes-thematiques', $crawler->getUri());
        $this->assertEquals(
            'Nous vous avons envoyé un email à l\'adresse "referent@en-marche-dev.fr". Veuillez cliquer sur le lien contenu dans cet email pour confirmer votre inscription à la communauté.',
            trim($crawler->filter('.flash.flash--info')->text())
        );
    }

    public function testContactWithSameMailAlreadyJoined()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/communautes-thematiques');

        $crawler = $this->client->click($crawler->selectLink('Je m\'inscris')->link());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->submit($crawler->selectButton('Je m\'inscris')->form([
            'thematic_community_membership[lastName]' => 'Do not',
            'thematic_community_membership[firstName]' => 'Matter',
            'thematic_community_membership[gender]' => 'male',
            'thematic_community_membership[email]' => 'john.peter@contact.com',
            'thematic_community_membership[phone][country]' => 'FR',
            'thematic_community_membership[phone][number]' => '606060606',
            'thematic_community_membership[position]' => 'employed',
            'thematic_community_membership[birthDate]' => '1934-08-16',
            'thematic_community_membership[postAddress][address]' => 'Rue des tests',
            'thematic_community_membership[postAddress][postalCode]' => '75007',
            'thematic_community_membership[postAddress][cityName]' => 'Paris',
            'thematic_community_membership[postAddress][country]' => 'FR',
            'thematic_community_membership[hasJob]' => 1,
            'thematic_community_membership[job]' => 'Médecin',
            'thematic_community_membership[association]' => 0,
            'thematic_community_membership[motivations]' => ['information', 'thinking'],
        ]));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('http://test.enmarche.code/communautes-thematiques', $crawler->getUri());
        $this->assertEquals(
            'Cette adresse email est déjà enregistrée dans cette communauté.',
            trim($crawler->filter('.flash.flash--error')->text())
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
        $this->client->followRedirects();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
