<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\Event\EventRegistration;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

abstract class AbstractEventControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use RegistrationTrait;

    public function testAnonymousUserCanRegisterToEventWhileLoginIn()
    {
        $eventUrl = $this->getEventUrl();
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $registrationLink = $crawler->filter('.register-event');

        $eventRegistrationUrl = "$eventUrl/inscription";

        $this->assertSame($eventRegistrationUrl, $registrationLink->attr('href'));

        $crawler = $this->client->click($registrationLink->link());

        $this->assertCount(1, $connectLink = $crawler->selectLink('Connectez-vous'));

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'benjyd@aol.com',
            '_login_password' => 'secret!12345',
        ]));

        $this->assertClientIsRedirectedTo($eventRegistrationUrl, $this->client);
    }

    public function testAnonymousUserCanRegisterToEventWhileRegistering()
    {
        $eventUrl = $this->getEventUrl();
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $registrationLink = $crawler->filter('.register-event');

        $eventRegistrationUrl = "$eventUrl/inscription";

        self::assertSame($eventRegistrationUrl, $registrationLink->attr('href'));

        $crawler = $this->client->click($registrationLink->link());

        $this->assertCount(1, $connectLink = $crawler->selectLink('Connectez-vous'));

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $registerLink = $crawler->selectLink('S’inscrire'));

        $this->register($this->client, $this->client->click($registerLink->link()), $eventRegistrationUrl);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->client->followRedirect();

        $redirectUrl = $this->client->getResponse()->headers->get('location');
        $registration = $this->manager->getRepository(EventRegistration::class)->findOneBy([
            'emailAddress' => 'paul@dupont.tld',
        ]);

        $this->assertInstanceOf(EventRegistration::class, $registration);
        $this->assertSame($eventUrl.'/confirmation?registration='.$registration->getUuid(), $redirectUrl);
    }

    abstract protected function getEventUrl(): string;
}
