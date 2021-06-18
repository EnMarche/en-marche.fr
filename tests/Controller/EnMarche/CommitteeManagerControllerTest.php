<?php

namespace Tests\App\Controller\EnMarche;

use App\Committee\CommitteeManager;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Entity\CommitteeMembership;
use App\Entity\Event\CommitteeEvent;
use App\Mailer\Message\CommitteeMessageNotificationMessage;
use App\Mailer\Message\EventNotificationMessage;
use App\Mailer\Message\EventRegistrationConfirmationMessage;
use App\Repository\CommitteeFeedItemRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\EventRepository;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group committeeManager
 */
class CommitteeManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var EventRepository */
    private $committeeEventRepository;

    /* @var CommitteeFeedItemRepository */
    private $committeeFeedItemRepository;

    /* @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;

    public function testCommitteeFollowerIsNotAllowedToEditCommitteeInformation()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request('GET', sprintf('%s/editer', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCommitteeHostCanEditCommitteeInformation()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'name' => 'F',
                'description' => 'F',
                'address' => [
                    'address' => '',
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'facebookPageUrl' => 'yo',
                'twitterNickname' => '@!!',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(8, $crawler->filter('#edit-committee-form .form__errors > li')->count());
        $this->assertSame('Vous ne pouvez pas changer la ville de votre comité.', $crawler->filter('#committee_address_errors > li.form__error')->eq(0)->text());
        $this->assertSame("Cette valeur n'est pas un code postal français valide.", $crawler->filter('#committee_address_errors > li.form__error')->eq(1)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#committee_address_errors > li.form__error')->eq(2)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#committee_address_address_errors > li.form__error')->text());
        $this->assertSame('Vous devez saisir au moins 2 caractères.', $crawler->filter('#field-name > .form__errors > li')->text());
        $this->assertSame('Votre texte de description est trop court. Il doit compter 5 caractères minimum.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());
        $this->assertSame('Un identifiant Twitter ne peut contenir que des lettres, des chiffres et des underscores.', $crawler->filter('#field-twitter-nickname > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee, but with address in another city
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'name' => 'clichy est En Marche !',
                'description' => 'Comité français En Marche ! de la ville de Clichy',
                'address' => [
                    'country' => 'FR',
                    'address' => '92-98 boulevard Victor Hugo',
                    'postalCode' => '92110',
                    'city' => '92110-92024',
                    'cityName' => '',
                ],
                'facebookPageUrl' => 'https://www.facebook.com/EnMarcheClichy',
                'twitterNickname' => '@nvenmarche',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('#edit-committee-form .form__errors > li')->count());
        $this->assertSame('Vous ne pouvez pas changer la ville de votre comité.', $crawler->filter('#committee_address_errors > li.form__error')->eq(0)->text());

        // Submit the committee form with valid data to create committee
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'name' => 'Nouveau comité En Marche !',
                'description' => 'Nouveau comité français En Marche !',
                'address' => [
                    'country' => 'FR',
                    'address' => '62 avenue des Champs-Élysées',
                    'postalCode' => '75008',
                    'city' => '75008-75108',
                    'cityName' => 'Paris 8e',
                ],
                'facebookPageUrl' => 'https://www.facebook.com/NouveauEnMarche',
                'twitterNickname' => '@nvenmarche',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee edit page again
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Les informations du comité ont été mises à jour avec succès.');
        $this->assertSame('Nouveau comité En Marche !', $crawler->filter('#committee_name')->attr('value'));
        $this->assertSame('62 avenue des Champs-Élysées', $crawler->filter('#committee_address_address')->attr('value'));
        $this->assertSame('75008', $crawler->filter('#committee_address_postalCode')->attr('value'));
        $this->assertSame('75008-75108', $crawler->filter('#committee_address_city')->attr('value'));
        $this->assertSame('Paris 8e', $crawler->filter('#committee_address_cityName')->attr('value'));
        $this->assertSame('France', $crawler->filter('#committee_address_country option:selected')->text());
        $this->assertSame('https://www.facebook.com/NouveauEnMarche', $crawler->filter('#committee_facebookPageUrl')->attr('value'));
        $this->assertSame('nvenmarche', $crawler->filter('#committee_twitterNickname')->attr('value'));
    }

    public function testCommitteeHostCannotEditNoneditableCommitteeName()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Dammarie-les-Lys"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee form with new name
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'name' => 'Nouveau nom',
                'description' => 'Comité français En Marche !',
                'address' => [
                    'country' => 'FR',
                    'address' => '824 Avenue du Lys',
                    'postalCode' => '92110',
                    'city' => '92110-92024',
                    'cityName' => '',
                ],
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee edit page again
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        // Committee name has not been changed
        $this->assertSame('En Marche Dammarie-les-Lys', $crawler->filter('#committee_name')->attr('value'));
        $this->assertSame('Comité français En Marche !', $crawler->filter('#committee_description')->text());
    }

    public function testCommitteeHostCannotEditCompletelyAddressOfPendingCommittee()
    {
        $this->client->followRedirects();

        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');
        $this->client->request(Request::METHOD_GET, '/comites/en-marche-marseille-3');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCommitteeFollowerIsNotAllowedToPublishNewEvent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request('GET', sprintf('%s/evenements/ajouter', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCommitteeHostCanPublishNewEvent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());

        $crawler = $this->client->click($crawler->selectLink('+ Créer un événement')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $eventCategory = $this->getEventCategoryIdForName(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE003']);

        // Submit the committee event form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'event_command' => [
                'name' => 'F',
                'description' => 'F',
                'category' => $eventCategory,
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'beginAt' => '2022-03-02 14:30',
                'finishAt' => '2022-03-01 19:00',
                'capacity' => 'zero',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(7, $crawler->filter('#committee-event-form .form__errors > li')->count());
        $this->assertSame('Vous devez saisir au moins 5 caractères.', $crawler->filter('#committee-event-name-field .form__error')->text());
        $this->assertSame('Vous devez saisir au moins 10 caractères.', $crawler->filter('#committee-event-description-field .form__error')->text());
        $this->assertSame('Cette valeur n\'est pas valide.', $crawler->filter('#committee-event-capacity-field .form__error')->text());
        $this->assertSame("Cette valeur n'est pas un code postal français valide.", $crawler->filter('#committee-event-address > .form__errors > .form__error')->eq(0)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#committee-event-address > .form__errors > li')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#committee-event-address-address-field > .form__errors > li')->text());
        $this->assertSame("La date de fin de l'événement doit être postérieure à la date de début.", $crawler->filter('#committee-event-finishat-field > .form__errors > li')->text());

        // Submit the committee form with valid data to create the new committee event
        $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'event_command' => [
                'name' => " ♻ débat sur l'agriculture écologique ♻ ",
                'description' => " ♻ Cette journée sera consacrée à un grand débat sur la question de l'agriculture écologique. ♻ ",
                'category' => $eventCategory,
                'address' => [
                    'address' => '6 rue Neyret',
                    'country' => 'FR',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
                ],
                'beginAt' => '2022-03-02 09:30',
                'finishAt' => '2022-03-02 19:00',
                'capacity' => '1500',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertInstanceOf(CommitteeEvent::class, $event = $this->committeeEventRepository->findOneBySlug('2022-03-02-debat-sur-lagriculture-ecologique'));
        $this->assertSame("Débat sur l'agriculture écologique", $event->getName());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $event->getDescription());
        $this->assertFalse($event->isForLegislatives());
        $this->assertCountMails(1, EventNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, EventNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(1, EventNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(0, EventNotificationMessage::class, 'carl999@example.fr');

        $eventItem = $this->committeeFeedItemRepository->findMostRecentFeedEvent(LoadCommitteeData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(CommitteeFeedItem::class, $eventItem);
        $this->assertInstanceOf(CommitteeEvent::class, $eventItem->getEvent());

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Le nouvel événement a bien été créé et publié sur la page du comité.');
        $this->assertSame('Débat sur l\'agriculture écologique - Lyon 1er, 02/03/2022 | La République En Marche !', $crawler->filter('title')->text());
        $this->assertSame('Débat sur l\'agriculture écologique - Lyon 1er, 02/03/2022', $crawler->filter('.committee-event-name')->text());
        $this->assertSame('Organisé par Gisele Berthoux du comité En Marche Paris 8', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        $this->assertSame('Mercredi 2 mars 2022, 9h30', $crawler->filter('.committee-event-date')->text());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er', $crawler->filter('.committee-event-address')->text());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $crawler->filter('.committee-event-description')->text());

        $this->assertCountMails(1, EventRegistrationConfirmationMessage::class, 'gisele-berthoux@caramail.com');
    }

    public function testCommitteeHostCanPublishNewEventWithTimeZone()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $crawler = $this->client->click($crawler->selectLink('+ Créer un événement')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $eventCategory = $this->getEventCategoryIdForName(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE003']);

        $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'event_command' => [
                'name' => " ♻ débat sur l'agriculture écologique à Singapore",
                'description' => " ♻ Cette journée sera consacrée à un grand débat sur la question de l'agriculture écologique. ♻ ",
                'category' => $eventCategory,
                'address' => [
                    'address' => '6 rue Neyret',
                    'country' => 'FR',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
                ],
                'beginAt' => '2022-03-02 09:30',
                'finishAt' => '2022-03-02 19:00',
                'capacity' => '1500',
                'timeZone' => 'Asia/Singapore',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertInstanceOf(CommitteeEvent::class, $event = $this->committeeEventRepository->findOneBySlug('2022-03-02-debat-sur-lagriculture-ecologique-a-singapore'));
        $this->assertSame("Débat sur l'agriculture écologique à Singapore", $event->getName());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $event->getDescription());
        $this->assertFalse($event->isForLegislatives());
        $this->assertCountMails(1, EventNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, EventNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(1, EventNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(0, EventNotificationMessage::class, 'carl999@example.fr');

        $eventItem = $this->committeeFeedItemRepository->findMostRecentFeedEvent(LoadCommitteeData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(CommitteeFeedItem::class, $eventItem);
        $this->assertInstanceOf(CommitteeEvent::class, $eventItem->getEvent());

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Le nouvel événement a bien été créé et publié sur la page du comité.');
        $this->assertSame('Débat sur l\'agriculture écologique à Singapore - Lyon 1er, 02/03/2022 | La République En Marche !', $crawler->filter('title')->text());
        $this->assertSame('Débat sur l\'agriculture écologique à Singapore - Lyon 1er, 02/03/2022', $crawler->filter('.committee-event-name')->text());
        $this->assertSame('Organisé par Gisele Berthoux du comité En Marche Paris 8', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        $this->assertSame('Mercredi 2 mars 2022, 9h30 UTC +08:00', $crawler->filter('.committee-event-date')->text());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er', $crawler->filter('.committee-event-address')->text());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $crawler->filter('.committee-event-description')->text());

        $this->assertCountMails(1, EventRegistrationConfirmationMessage::class, 'gisele-berthoux@caramail.com');
    }

    public function testAuthenticatedCommitteeHostCanPostMessages()
    {
        $this->markTestSkipped('Skipped temporary, need to implement this feature with a new Message form');

        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $committeeUrl = $this->client->getRequest()->getPathInfo();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertFalse($this->seeFlashMessage($crawler));
        $this->assertCountTimelineMessages($crawler, 9);

        $crawler = $this->client->submit($crawler->selectButton('committee_feed_message[send]')->form([
            'committee_feed_message' => ['subject' => 'bonsoir', 'content' => 'yo'],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler, ['Le message doit contenir au moins 10 caractères.']));
        $this->assertFalse($this->seeFlashMessage($crawler));

        $this->client->submit($crawler->selectButton('committee_feed_message[send]')->form([
            'committee_feed_message' => ['subject' => 'bonsoir', 'content' => 'Bienvenue !'],
        ]));

        $this->assertClientIsRedirectedTo($committeeUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertTrue($this->seeFlashMessage($crawler, 'Votre message a bien été envoyé.'));
        $this->assertCountTimelineMessages($crawler, 9, 'Message should not be published');

        $message = $this->committeeFeedItemRepository->findMostRecentFeedMessage(LoadCommitteeData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame('Bienvenue !', $message->getContent());

        $mail = $this->getEmailRepository()->findMostRecentMessage(CommitteeMessageNotificationMessage::class);
        $this->assertMailCountRecipients(
            $this->getCommitteeSubscribersCount(
                $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)
            ),
            $mail
        );

        $this->client->submit($crawler->selectButton('committee_feed_message[send]')->form([
            'committee_feed_message' => [
                'subject' => 'Bonsoir',
                'content' => 'Première publication !',
                'published' => '1',
            ],
        ]));

        $this->assertClientIsRedirectedTo($committeeUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertTrue($this->seeFlashMessage($crawler, 'Votre message a bien été publié.'));
        $this->assertSeeCommitteeTimelineMessage($crawler, 0, 'Gisele Berthoux', 'co-animateur', 'Première publication !');
    }

    /**
     * @dataProvider provideFollowerCredentials
     */
    public function testAuthenticatedFollowerCannotSeeCommitteeMembers(string $username)
    {
        // Authenticate as a committee follower
        $this->authenticateAsAdherent($this->client, $username);
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $this->client->request(Request::METHOD_GET, sprintf('%s/membres', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideFollowerCredentials(): array
    {
        return [
            'follower 1' => ['carl999@example.fr'],
            'follower 2' => ['luciole1989@spambox.fr'],
        ];
    }

    public function testAuthenticatedHostCanSeeCommitteeMembers()
    {
        // Authenticate as the committee host
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertTrue($this->seeMembersList($crawler, 5));
        self::assertSame('Jacques P.', trim($crawler->filter('.member-name')->eq(0)->text()));
        self::assertCount(0, $crawler->filter('.member-name img.b__nudge--left-nano'));
        self::assertCount(0, $crawler->filter('.member-phone'));
        self::assertSame('75008', $crawler->filter('.member-postal-code')->eq(0)->text());
        self::assertSame('Paris 8e', $crawler->filter('.member-city-name')->eq(0)->text());
        self::assertSame('12/01/2017', $crawler->filter('.member-subscription-date')->eq(0)->text());
        self::assertCount(4, $crawler->filter('.member-status .em-tooltip'));
        self::assertSame('Abonné Email', $crawler->filter('.member-status .em-tooltip .em-tooltip--content p')->eq(0)->text());
    }

    public function testAuthenticatedProvisionalSupervisorCanSeeCommitteeMembers()
    {
        // Authenticate as the committee provisional supervisor
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-comite-de-evry/membres');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertTrue($this->seeMembersList($crawler, 7));
        self::assertSame('Francis B.', trim($crawler->filter('.member-name')->eq(0)->text()));
        self::assertCount(0, $crawler->filter('.member-name img.b__nudge--left-nano'));
        self::assertCount(0, $crawler->filter('.member-phone'));
        self::assertSame('77000', $crawler->filter('.member-postal-code')->eq(0)->text());
        self::assertSame('Melun', $crawler->filter('.member-city-name')->eq(0)->text());
        self::assertCount(6, $crawler->filter('.member-status .em-tooltip'));
        self::assertSame('Abonné Email', $crawler->filter('.member-status .em-tooltip .em-tooltip--content p')->eq(0)->text());
    }

    public function testAuthenticatedSupervisorCanFilterCommitteeMembers()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8/membres');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(4, $crawler->filter('table tbody tr'));
        $this->assertCount(1, $crawler->filter('.filter__row:contains("Certifié")'));
        $this->assertCount(1, $crawler->filter('.filter__row:contains("A choisi son comité de vote")'));

        // filter by gender
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[gender]' => 'female',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(2, $crawler->filter('table tbody tr'));
        $this->assertSame('Gisele Berthoux', trim($crawler->filter('.member-name')->eq(0)->text()));
        $this->assertSame('Lucie Olivera', trim($crawler->filter('.member-name')->eq(1)->text()));

        // filter by subscribed
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[gender]' => '',
            'filter[subscribed]' => 1,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(3, $crawler->filter('table tbody tr'));
        $this->assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));
        $this->assertSame('Gisele Berthoux', trim($crawler->filter('.member-name')->eq(1)->text()));
        $this->assertSame('Lucie Olivera', trim($crawler->filter('.member-name')->eq(2)->text()));

        // filter by certified
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[subscribed]' => '',
            'filter[certified]' => 1,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('table tbody tr'));
        $this->assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));

        // filter by votersOnly
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[votersOnly]' => 1,
            'filter[subscribed]' => '',
            'filter[certified]' => '',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(2, $crawler->filter('table tbody tr'));
        $this->assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));
        $this->assertSame('Gisele Berthoux', trim($crawler->filter('.member-name')->eq(1)->text()));
    }

    public function testAuthenticatedProvisionalSupervisorDoesnotSeeSomeFilters()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-dammarie-les-lys/membres');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(0, $crawler->filter('.filter__row:contains("Certifié")'));
        $this->assertCount(0, $crawler->filter('.filter__row:contains("A choisi son comité de vote")'));
    }

    public function testAuthenticatedSupervisorCanSeeMoreInfoAboutCommitteeMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertTrue($this->seeMembersList($crawler, 5));
        self::assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));
        self::assertCount(1, $crawler->filter('.member-name img.b__nudge--left-nano'));
        self::assertSame('+33 1 87 26 42 36', trim($crawler->filter('.member-phone')->eq(0)->text()));
        self::assertSame('75008', $crawler->filter('.member-postal-code')->eq(0)->text());
        self::assertSame('Paris 8e', $crawler->filter('.member-city-name')->eq(0)->text());
        self::assertSame('12/01/2017', $crawler->filter('.member-subscription-date')->eq(0)->text());
        self::assertCount(8, $crawler->filter('.member-status .em-tooltip'));
        self::assertSame('Vote dans ce comité', $crawler->filter('.member-status .em-tooltip .em-tooltip--content p')->eq(0)->text());
        self::assertSame('Abonné Email', $crawler->filter('.member-status .em-tooltip .em-tooltip--content p')->eq(1)->text());
    }

    public function testAuthenticatedCommitteeSupervisorCanPromoteNewHostsAmongMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame(2, $crawler->filter('.promote-host-link')->count());
        $this->assertSame(1, $crawler->filter('.demote-host-link')->count());

        $crawler = $this->client->click($crawler->filter('.promote-host-link')->link());

        $this->client->submit($crawler->selectButton("Oui, promouvoir l'adhérent")->form());
        $crawler = $this->client->followRedirect();

        // no more available places for a new host, that's why no link to promote
        $this->assertSame(0, $crawler->filter('.promote-host-link')->count());
        $this->assertSame(2, $crawler->filter('.demote-host-link')->count());
        $this->seeFlashMessage($crawler, 'Le membre a été promu animateur du comité avec succès.');

        $crawler = $this->client->click($crawler->filter('.demote-host-link')->link());

        $this->client->submit($crawler->selectButton('Oui, définir comme simple membre')->form());
        $crawler = $this->client->followRedirect();

        $this->assertSame(2, $crawler->filter('.promote-host-link')->count());
        $this->assertSame(1, $crawler->filter('.demote-host-link')->count());
        $this->seeFlashMessage($crawler, 'Le membre a été redéfini simple membre du comité avec succès.');
    }

    public function testAuthenticatedCommitteeHostCannotPromoteNewHostsAmongMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertSame(0, $crawler->filter('.promote-host-link')->count());
    }

    public function testCommitteeExportMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, $this->client->getRequest()->getPathInfo().'?export=1');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(5, $this->transformToArray($this->client->getResponse()->getContent()));
    }

    public function testAllowToCreateCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'martine.lindt@gmail.com');
        $crawler = $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertStringContainsString('Vous devez être certifiée', $crawler->filter('.committee__warning')->first()->text());

        $this->client->request('POST', '/espace-adherent/creer-mon-comite');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->logout($this->client);

        /** @var CommitteeManager $committeeManager */
        $committeeManager = $this->get(CommitteeManager::class);

        $adherent = $this->getAdherentRepository()->findOneByEmail('martine.lindt@gmail.com');
        $committee = $this->getCommitteeRepository()->findOneByName('En Marche - Comité de Berlin');

        $committeeManager->changePrivilege(
            $adherent,
            $committee,
            CommitteeMembership::COMMITTEE_FOLLOWER
        );

        $this->authenticateAsAdherent($this->client, 'martine.lindt@gmail.com');
        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
    }

    private function getCommitteeSubscribersCount(Committee $committee): int
    {
        return $this
            ->committeeMembershipRepository
            ->findForHostEmail($committee)
            ->getCommitteesNotificationsSubscribers()
            ->count()
        ;
    }

    private function seeMembersList(Crawler $crawler, int $count): bool
    {
        // Header row is part of the count
        return $count === \count($crawler->filter('table tr'));
    }

    private function seeMessageForm(Crawler $crawler, array $errorMessages = []): bool
    {
        if ($errorMessages) {
            $errors = $crawler->filter('form[name="committee_feed_message"] .form__error');

            $this->assertCount(\count($errorMessages), $errors);

            foreach ($errorMessages as $i => $errorMessage) {
                $this->assertSame($errorMessage, trim($errors->eq($i)->text()));
            }
        } else {
            $this->assertCount(0, $crawler->filter('form[name="committee_feed_message"] .form__errors'));
        }

        return 1 === \count($crawler->filter('form[name="committee_feed_message"]'));
    }

    private function assertCountTimelineMessages(Crawler $crawler, int $nb, string $message = '')
    {
        $this->assertSame($nb, $crawler->filter('.committee__timeline__message')->count(), $message);
    }

    private function transformToArray(string $encodedData): array
    {
        $tmpHandle = tmpfile();
        fwrite($tmpHandle, $encodedData);
        $metaDatas = stream_get_meta_data($tmpHandle);
        $tmpFilename = $metaDatas['uri'];

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tmpFilename);

        return $spreadsheet->getActiveSheet()->toArray();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeEventRepository = $this->getEventRepository();
        $this->committeeFeedItemRepository = $this->getCommitteeFeedItemRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();
    }

    protected function tearDown(): void
    {
        $this->committeeMembershipRepository = null;
        $this->committeeFeedItemRepository = null;
        $this->committeeEventRepository = null;

        parent::tearDown();
    }
}
