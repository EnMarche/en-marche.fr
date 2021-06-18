<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

abstract class AbstractGroupControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use RegistrationTrait;

    public function testAnonymousUserCanFollowGroupWhileLoginIn()
    {
        $groupUrl = $this->getGroupUrl();
        $crawler = $this->client->request('GET', $groupUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $connectLink = $crawler->filter('#committee-login-link');

        $this->assertCount(1, $connectLink);

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'benjyd@aol.com',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertClientIsRedirectedTo($groupUrl, $this->client);
    }

    public function testAnonymousUserCanFollowGroupWhileRegistering()
    {
        $groupUrl = $this->getGroupUrl();
        $crawler = $this->client->request('GET', $groupUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($crawler->selectLink('adhérez')->link());

        $this->assertClientIsRedirectedTo('/adhesion', $this->client);

        $this->register($this->client, $this->client->followRedirect(), $groupUrl);
    }

    abstract protected function getGroupUrl(): string;
}
