<?php

namespace Tests\App\Controller\EnMarche\EventManager;

use App\DataFixtures\ORM\LoadCoalitionData;
use App\Mailer\Message\EventRegistrationConfirmationMessage;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class CoalitionEventManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListCoalitionEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/evenements-de-coalitions');

        $this->assertCount(13, $crawler->filter('tbody tr.event__item'));
    }

    public function testListCauseEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/evenements-de-causes');

        $this->assertCount(6, $crawler->filter('tbody tr.event__item'));
    }

    public function testListMyCoalitionEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/mes-evenements');

        $this->assertCount(4, $crawler->filter('tbody tr.event__item'));
    }

    public function testCreateCoalitionEventFailed(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coalition/evenements/creer');

        $data = [];

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);
        $this->assertSame(7, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-name-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être nulle.',
            $this->client->getCrawler()->filter('#event-category-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-coalition-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-description-field > .form__errors > li')->text());
        $this->assertSame('L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#event-address-address-field > .form__errors > li')->text());
        $this->assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#event-address > .form__errors > li')->text());
        $this->assertSame('Veuillez sélectionner un pays.',
            $this->client->getCrawler()->filter('#event-address-country-field > .form__errors > li')->text());
    }

    public function testCreateEventSuccessful(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coalition/evenements/creer');

        $data = [];
        $data['coalition_event']['name'] = 'Nouveau événement des coalitions';
        $data['coalition_event']['category'] = $this->getEventCategoryIdForName('Événement innovant');
        $data['coalition_event']['coalition'] = $this->getCoalitionRepository()->findOneBy(['uuid' => LoadCoalitionData::COALITION_1_UUID])->getId();
        $data['coalition_event']['beginAt'] = '2023-06-14 16:15';
        $data['coalition_event']['finishAt'] = '2023-06-15 23:00';
        $data['coalition_event']['address']['address'] = '92 boulevard victor hugo';
        $data['coalition_event']['address']['cityName'] = 'clichy';
        $data['coalition_event']['address']['postalCode'] = '92110';
        $data['coalition_event']['address']['country'] = 'FR';
        $data['coalition_event']['description'] = 'Nouveau événement';
        $data['coalition_event']['timeZone'] = 'Europe/Paris';

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);

        $this->assertSame('L\'événement « Nouveau événement des coalitions » a bien été créé.', $this->client->getCrawler()->filter('.box-success h2')->text());

        $this->assertCountMails(0, EventRegistrationConfirmationMessage::class, 'jacques.picard@en-marche.fr');
    }

    public function canAccessCoalitionEventEditEventPage(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request('GET', '/espace-coalition/evenements/'.date('Y-m-d', strtotime('+17 days')).'-evenement-economique/modifier');

        $this->isSuccessful($this->client->getResponse());
    }

    public function canAccessCauseEventEditEventPage(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request('GET', '/espace-coalition/evenements/'.date('Y-m-d', strtotime('+2 days')).'-evenement-culturel-1-de-la-cause-culturelle-1/modifier');

        $this->isSuccessful($this->client->getResponse());
    }
}
