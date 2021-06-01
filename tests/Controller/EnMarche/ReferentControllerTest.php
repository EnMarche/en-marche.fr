<?php

namespace Tests\App\Controller\EnMarche;

use App\Address\GeoCoder;
use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\DataFixtures\ORM\LoadDelegatedAccessData;
use App\Entity\Event\InstitutionalEvent;
use App\Entity\Geo\Zone;
use App\Entity\ReferentManagedUsersMessage;
use App\Mailer\Message\EventRegistrationConfirmationMessage;
use App\Mailer\Message\InstitutionalEventInvitationMessage;
use App\Repository\ReferentManagedUsersMessageRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group referent
 */
class ReferentControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    /**
     * @var ReferentManagedUsersMessageRepository
     */
    private $referentMessageRepository;

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAdherentNotReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsAccessibleAsReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testChangeOfPageAccessInformationToReferentSpace($path)
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $adherent = $this->getAdherentRepository()->findOneByEmail('referent@en-marche-dev.fr');
        $accessInformation = $this->getReferentSpaceAccessInformationRepository()->findByAdherent($adherent);

        $this->assertNull($accessInformation);

        $this->client->request(Request::METHOD_GET, $path);
        $this->manager->clear();
        $accessInformation = $this->getReferentSpaceAccessInformationRepository()->findByAdherent($adherent);

        $this->assertNotNull($accessInformation);
        $this->assertNotNull($accessInformation->getLastDate());
        $this->assertNotNull($accessInformation->getPreviousDate());
    }

    public function testCreateEventFailed()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements/creer');

        $data = [];

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);
        $this->assertSame(5, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#committee-event-name-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#committee-event-description-field > .form__errors > li')->text());
        $this->assertSame('L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#committee-event-address-address-field > .form__errors > li')->text());
        $this->assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#committee-event-address > .form__errors > li')->text());
        $this->assertSame('Veuillez sélectionner un pays.',
            $this->client->getCrawler()->filter('#committee-event-address-country-field > .form__errors > li')->text());
    }

    public function testCreateEventSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements/creer');

        $data = [];
        $data['event_command']['name'] = 'premier événement';
        $data['event_command']['category'] = $this->getEventCategoryIdForName('Événement innovant');
        $data['event_command']['beginAt'] = '2023-06-14 16:15';
        $data['event_command']['finishAt'] = '2023-06-15 23:00';
        $data['event_command']['address']['address'] = 'Pilgerweg 58';
        $data['event_command']['address']['cityName'] = 'Kilchberg';
        $data['event_command']['address']['postalCode'] = '8802';
        $data['event_command']['address']['country'] = 'CH';
        $data['event_command']['description'] = 'Premier événement en Suisse';
        $data['event_command']['capacity'] = 100;
        $data['event_command']['timeZone'] = 'Europe/Zurich';

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);

        $this->assertSame('L\'événement « Premier événement » a bien été créé.', $this->client->getCrawler()->filter('.box-success h2')->text());

        $this->assertSame(
            'Votre événement est en ligne mais pas encore diffusé. Partagez-le par message en cliquant ci-dessous.',
            trim($this->client->getCrawler()->filter('.box-success .alert--tips')->text())
        );

        $this->assertCountMails(1, EventRegistrationConfirmationMessage::class, 'referent@en-marche-dev.fr');
    }

    public function testCreateInstitutionalEventSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements-institutionnels/creer');

        $data = [];
        $data['institutional_event']['name'] = 'Un événement institutionnel en Suisse';
        $data['institutional_event']['category'] = $this->getInstitutionalEventCategoryIdByName('Comité politique');
        $data['institutional_event']['beginAt'] = '2023-06-14 16:00';
        $data['institutional_event']['finishAt'] = '2023-06-15 23:00';
        $data['institutional_event']['address']['address'] = 'Pilgerweg 58';
        $data['institutional_event']['address']['cityName'] = 'Kilchberg';
        $data['institutional_event']['address']['postalCode'] = '8802';
        $data['institutional_event']['address']['country'] = 'CH';
        $data['institutional_event']['description'] = 'Premier événement institutionel en Suisse';
        $data['institutional_event']['timeZone'] = 'Europe/Zurich';
        $data['institutional_event']['invitations'] = 'jean@exemple.fr;michel@exemple.fr;marcel@exemple.fr';

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Créer cette réunion privée')->form(), $data
        );

        /** @var InstitutionalEvent $event */
        $event = $this->getInstitutionalEventRepository()->findOneBy(
            ['name' => 'Un événement institutionnel en Suisse']
        );

        $this->assertInstanceOf(InstitutionalEvent::class, $event);
        $this->assertClientIsRedirectedTo('/espace-referent/evenements-institutionnels', $this->client);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString(
            'Le nouvel événement institutionnel a bien été créé.',
            $this->client->getCrawler()->filter('div.flash--info')->html()
        );

        $this->assertCountMails(1, InstitutionalEventInvitationMessage::class, 'referent@en-marche-dev.fr');
    }

    public function testCreateInstitutionalEventFailed()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements-institutionnels/creer');

        // Submit the form with empty values
        $this->client->submit($this->client->getCrawler()
            ->selectButton('Créer cette réunion privée')->form(), [])
        ;

        $this->assertSame(6, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#institutional_event-name-field > .form__errors > li')->text()
        );

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#institutional_event-description-field > .form__errors > li')->text()
        );

        $this->assertSame("L'adresse est obligatoire.",
            $this->client->getCrawler()->filter(
                '#institutional_event-address-address-field > .form__errors > li')->text()
        );

        $this->assertSame('Veuillez sélectionner un pays.',
            $this->client->getCrawler()->filter('#institutional_event-address-country-field > .form__errors > li')->text()
        );

        $this->assertSame('Vous devez saisir au moins une adresse email.',
            $this->client->getCrawler()->filter('#institutional_event-invitations-field > .form__errors > li')->text()
        );

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#institutional_event-description-field > .form__errors > li')->text()
        );
    }

    /**
     * @dataProvider provideUsers
     */
    public function testSearchUserToSendMail($user)
    {
        $this->authenticateAsAdherent($this->client, $user);

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        self::assertSame(4, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();

        /* @var ChoiceFormField $zonesField */
        $zonesField = $form->get('f[zones]');
        $zonesField->disableValidation()->setValue([
            $this->getRepository(Zone::class)->findOneBy([
                'type' => Zone::DEPARTMENT,
                'code' => '77', // Seine-et-Marne
            ])->getId(),
        ]);

        $this->client->submit($form);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        self::assertSame(4, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();

        /* @var ChoiceFormField $zonesField */
        $zonesField = $form->get('f[zones]');
        $zonesField->disableValidation()->setValue([
            $this->getRepository(Zone::class)->findOneBy([
                'type' => Zone::CITY,
                'code' => '77288', // Melun
            ])->getId(),
        ]);

        $this->client->submit($form);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        // Gender
        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'gender' => 'male',
            ],
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(3, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'gender' => 'female',
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        // Firstname
        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'gender' => 'male',
                'firstName' => 'Mich',
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(2, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        // Lastname
        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'lastName' => 'ou',
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(3, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());
    }

    public function testFilterAdherents()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');

        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));

        // filter hosts
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = ['CommitteeHosts'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(2, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertStringContainsString('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());
        $this->assertStringContainsString('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter supervisors
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = ['CommitteeSupervisors'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(0, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertStringContainsString('Brioul Francis', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter newsletter subscriptions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(5, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('77000', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertStringContainsString('8802', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());

        // exclude
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = ['CitizenProjectHosts', 'CommitteeSupervisors', 'CommitteeHosts'];

        $this->client->submit($form);

        $this->assertCount(3, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('8802', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertStringContainsString('8057', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());

        // filter adherents in no committee
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[isCommitteeMember]'] = 0;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(1, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('Michelle', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());

        // filter adherents in committees
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[isCommitteeMember]'] = 1;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(4, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(3, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--adherent'));
        $this->assertStringContainsString('Francis', $this->client->getCrawler()->filter('tbody tr.referent__item--host')->first()->text());
        $this->assertStringContainsString('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item--host')->eq(1)->text());
        $this->assertStringContainsString('Michel', $this->client->getCrawler()->filter('tbody tr.referent__item--adherent')->text());

        // filter adherents in CP
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[isCommitteeMember]'] = '';
        $form['f[includeRoles]'] = ['CitizenProjectHosts'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(0, $this->client->getCrawler()->filter('.status.status__1'));

        // filter certified adherents
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = [];
        $form['f[isCertified]'] = 1;

        $this->client->submit($form);

        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr .adherent-name > img'));
        $this->assertStringContainsString('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertStringContainsString('Dufour', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());
    }

    public function testReferentCanCreateAdherentMessageSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-referent/messagerie/creer');
        $this->client->submit($crawler->selectButton('Suivant')->form(['adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertTrue($this->client->getResponse()->isRedirection());

        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $crawler = $this->client->followRedirect();

        $crawlerOptions = $crawler->filter('#referent_filter_referentTags option');

        self::assertCount(7, $crawlerOptions);
        self::assertSame('Suisse', $crawlerOptions->getNode(0)->nodeValue);
        self::assertSame('Espagne', $crawlerOptions->getNode(1)->nodeValue);
        self::assertSame('Département 92', $crawlerOptions->getNode(2)->nodeValue);
        self::assertSame('Département 76', $crawlerOptions->getNode(3)->nodeValue);
        self::assertSame('Département 77', $crawlerOptions->getNode(4)->nodeValue);
        self::assertSame('Département 13', $crawlerOptions->getNode(5)->nodeValue);
        self::assertSame('Département 59', $crawlerOptions->getNode(6)->nodeValue);

        $crawler = $this->client->submit(
            $crawler->selectButton('Filtrer')->form([
                'referent_filter' => [
                    'referentTags' => 13,
                ],
            ])
        );
        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        self::assertCount(0, $crawler->filter('.form .form__errors'));
    }

    public function testAdherentWithDelegatedAccessSeeAdherents()
    {
        $this->authenticateAsAdherent($this->client, 'senateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/espace-partage/%s', LoadDelegatedAccessData::ACCESS_UUID_7));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-referent/utilisateurs', $this->client);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(0, $this->client->getCrawler()->filter('tbody tr .adherent-name > img'));
    }

    public function providePages()
    {
        return [
            ['/espace-referent/utilisateurs'],
            ['/espace-referent/elus'],
            ['/espace-referent/evenements'],
            ['/espace-referent/comites'],
            ['/espace-referent/projets-citoyens'],
            ['/espace-referent/evenements/creer'],
        ];
    }

    public function provideUsers(): iterable
    {
        yield ['referent@en-marche-dev.fr'];
    }

    /**
     * @return string Date in the format "Jeudi 14 juin 2018, 9h00"
     */
    private function formatEventDate(\DateTime $date, $timeZone = GeoCoder::DEFAULT_TIME_ZONE): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $timeZone,
            \IntlDateFormatter::GREGORIAN,
            'EEEE d LLLL Y, H');

        return ucfirst(strtolower($formatter->format($date).'h00'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->referentMessageRepository = $this->manager->getRepository(ReferentManagedUsersMessage::class);

        $this->disableRepublicanSilence();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->referentMessageRepository = null;

        parent::tearDown();
    }
}
