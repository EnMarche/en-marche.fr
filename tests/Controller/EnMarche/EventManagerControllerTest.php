<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadCommitteeEventData;
use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Entity\Event\CommitteeEvent;
use App\Mailer\Message\EventCancellationMessage;
use App\Mailer\Message\EventContactMembersMessage;
use App\Mailer\Message\EventUpdateMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group eventManager
 */
class EventManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideHostProtectedPages
     */
    public function testAnonymousUserCannotEditEvent($path)
    {
        $this->client->request('GET', $path);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    /**
     * @dataProvider provideCancelledInaccessiblePages
     */
    public function testRegisteredAdherentUserCannotFoundPagesOfCancelledEvent($path)
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $this->redirectionEventNotPublishTest($path);
    }

    /**
     * @dataProvider provideHostProtectedPages
     */
    public function testRegisteredAdherentUserCannotEditEvent($path)
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');
        $this->client->request('GET', $path);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideHostProtectedPages(): array
    {
        $slug = date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne';

        return [
            ['/evenements/'.$slug.'/modifier'],
            ['/evenements/'.$slug.'/inscrits'],
        ];
    }

    public function provideCancelledInaccessiblePages(): array
    {
        $slug = date('Y-m-d', strtotime('+60 days')).'-reunion-de-reflexion-parisienne-annule';

        return [
            ['/evenements/'.$slug.'/modifier'],
            ['/evenements/'.$slug.'/inscription'],
            ['/evenements/'.$slug.'/annuler'],
        ];
    }

    public function testOrganizerCanEditEvent()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne/modifier');

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'event_command' => [
                'name' => 'écologie, débatons-en !',
                'description' => 'Cette journée sera consacrée à un grand débat sur la question écologique.',
                'category' => $this->getEventCategoryIdForName(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE003']),
                'address' => [
                    'address' => '6 rue Neyret',
                    'country' => 'FR',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
                    'cityName' => '',
                ],
                'beginAt' => '2022-03-02 09:30',
                'finishAt' => '2022-03-02 19:00',
                'capacity' => '1500',
                'timeZone' => 'Europe/Zurich',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertCountMails(1, EventUpdateMessage::class);

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'L\'événement a bien été modifié.');
        self::assertSame('Écologie, débatons-en ! - Lyon 1er, 02/03/2022 | La République En Marche !', $crawler->filter('title')->text());
        self::assertSame('Écologie, débatons-en ! - Lyon 1er, 02/03/2022', $crawler->filter('.committee-event-name')->text());
        self::assertSame('Organisé par Jacques Picard du comité En Marche Paris 8', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        self::assertMatchesRegularExpression('#Mercredi 2 mars 2022, 9h30 UTC \+0(1|2):00#', $crawler->filter('.committee-event-date')->text());
        self::assertSame('6 rue Neyret, 69001 Lyon 1er', $crawler->filter('.committee-event-address')->text());
        self::assertSame('Cette journée sera consacrée à un grand débat sur la question écologique.', $crawler->filter('.committee-event-description')->text());
    }

    public function testOrganizerCanCancelEvent()
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/'.date('Y-m-d', strtotime('+10 days')).'-reunion-de-reflexion-dammarienne/annuler');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Oui, annuler l\'événement')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'L\'événement a bien été annulé.');

        $messages = $this->getEmailRepository()->findMessages(EventCancellationMessage::class);
        /** @var EventCancellationMessage $message */
        $message = array_shift($messages);

        // Two mails have been sent
        $this->assertCount(2, $message->getRecipients());
    }

    public function testCommitteeHostCanEditEvent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne/modifier');

        $this->isSuccessful($this->client->getResponse());
    }

    public function testOrganizerCanSeeRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $this->assertCount(3, $crawler->filter('tbody > tr'));
    }

    public function testOrganizerCanExportRegistrationsWithWrongUuids()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $exportUrl = $this->client->getRequest()->getPathInfo().'/exporter';

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $crawler->filter('#members-export-token')->attr('value'),
            'exports' => json_encode(['wrong_uuid']),
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testOrganizerCanExportRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $token = $crawler->filter('#members-export-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $exportUrl = $this->client->getRequest()->getPathInfo().'/exporter';

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        // Try to illegally export an adherent data
        $uuids[] = Uuid::uuid4();

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode([]),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testOrganizerCannotPrintRegistrationsWithWrongUuids()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $printUrl = $this->client->getRequest()->getPathInfo().'/imprimer';

        $this->client->request(Request::METHOD_POST, $printUrl, [
            'token' => $crawler->filter('#members-print-token')->attr('value'),
            'prints' => json_encode(['wrong_uuid']),
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testOrganizerCanPrintRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $token = $crawler->filter('#members-print-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $printUrl = $this->client->getRequest()->getPathInfo().'/imprimer';

        $this->client->request(Request::METHOD_POST, $printUrl, [
            'token' => $token,
            'prints' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/pdf'
            )
        );
    }

    public function testOrganizerCanContactRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $token = $crawler->filter('#members-contact-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $membersUrl = $this->client->getRequest()->getPathInfo();
        $contactUrl = $membersUrl.'/contacter';

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());

        // Try to post with an empty subject and an empty message
        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'subject' => ' ',
            'message' => ' ',
        ]);

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $crawler->filter('.subject .form__errors > .form__error')->text()
        );

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $crawler->filter('.message .form__errors > .form__error')->text()
        );

        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'subject' => 'Bonsoir',
            'message' => 'Hello!',
        ]);

        $this->assertClientIsRedirectedTo($membersUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeFlashMessage($crawler, 'Félicitations, votre message a bien été envoyé aux inscrits sélectionnés.');

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(EventContactMembersMessage::class));

        // Try to illegally contact an adherent
        $uuids[] = Uuid::uuid4();

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, json_decode($crawler->filter('input[name="contacts"]')->attr('value'), true));

        // Force the contact form with foreign uuid
        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => json_encode($uuids),
            'subject' => 'Bonsoir',
            'message' => 'Hello!',
        ]);

        $this->assertClientIsRedirectedTo($membersUrl, $this->client);
    }

    public function testExportIcalEvent()
    {
        $this->client->request('GET', '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne/ical');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testExportIcalForeignEvent()
    {
        $uuid = LoadCommitteeEventData::EVENT_12_UUID;
        /** @var CommitteeEvent $event */
        $event = $this->getEventRepository()->findOneBy(['uuid' => $uuid]);

        $this->client->request(Request::METHOD_GET, sprintf('/evenements/%s/ical', $event->getSlug()));
        $this->isSuccessful($response = $this->client->getResponse());
        self::assertSame(sprintf('attachment; filename=%s.ics', $event->getSlug()), $response->headers->get('Content-Disposition'));
        self::assertSame('text/calendar; charset=UTF-8', $response->headers->get('Content-Type'));

        $beginAt = preg_quote($event->getLocalBeginAt()->format('Ymd\THis'), '/');
        $finishAt = preg_quote($event->getLocalFinishAt()->format('Ymd\THis'), '/');
        $uuid = preg_quote($uuid, '/');
        $icalRegex = <<<CONTENT
BEGIN\:VCALENDAR
VERSION\:2\.0
PRODID\:\-\/\/Sabre\/\/Sabre VObject 4\.[0-9]+\.[0-9]+\/\/EN
CALSCALE\:GREGORIAN
ORGANIZER\:Pierre KIROULE
BEGIN\:VEVENT
UID\:$uuid
DTSTAMP\:\\d{8}T\\d{6}Z
SUMMARY\:Meeting de New York City
DESCRIPTION\:Ouvert aux français de New York\.
DTSTART;TZID=America\/New_York\:$beginAt
DTEND;TZID=America\/New_York\:$finishAt
LOCATION\:226 W 52nd St\\\\, 10019 New York\\\\, \États\-Unis
END\:VEVENT
END\:VCALENDAR
CONTENT;
        $icalRegex = str_replace("\n", "\r\n", $icalRegex); // Returned content contains CRLF

        $this->assertMatchesRegularExpression(sprintf('/%s/', $icalRegex), $response->getContent());
    }

    private function redirectionEventNotPublishTest($url)
    {
        $this->client->request(Request::METHOD_GET, $url);

        $this->assertClientIsRedirectedTo('/evenements', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }
}
