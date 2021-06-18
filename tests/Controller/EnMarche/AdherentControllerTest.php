<?php

namespace Tests\App\Controller\EnMarche;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\SubscriptionType;
use App\Entity\Unregistration;
use App\Mailer\Message\AdherentContactMessage;
use App\Mailer\Message\AdherentTerminateMembershipMessage;
use App\Mailer\Message\CommitteeCreationConfirmationMessage;
use App\Repository\CommitteeRepository;
use App\Repository\EmailRepository;
use App\Repository\UnregistrationRepository;
use App\Subscription\SubscriptionTypeEnum;
use Cake\Chronos\Chronos;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group adherent
 */
class AdherentControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testMyEventsPageIsProtected(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/mes-evenements');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testAuthenticatedAdherentCanSeeHisUpcomingAndPastEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $crawler = $this->client->click($crawler->selectLink('Mes événements')->link());

        // first page
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(5, $crawler->filter('#upcoming-events article')->count());
        $titles = $crawler->filter('#upcoming-events h2');
        $this->assertSame('Meeting de New York City', trim($titles->first()->text()));
        $this->assertSame('Réunion de réflexion parisienne', trim($titles->eq(1)->text()));
        $this->assertSame('Événement culturel 1', trim($titles->eq(2)->text()));
        $this->assertSame('Événement culturel 1 de la cause culturelle 1', trim($titles->eq(3)->text()));
        $this->assertSame('Réunion de réflexion dammarienne', trim($titles->eq(4)->text()));

        $this->assertSame(5, $crawler->filter('#past-events article')->count());
        $titles = $crawler->filter('#past-events h2');
        $this->assertSame('Meeting de Singapour', trim($titles->first()->text()));
        $this->assertSame('Grand débat parisien', trim($titles->eq(1)->text()));
        $this->assertSame('Événement à Paris 1', trim($titles->eq(2)->text()));
        $this->assertSame('Événement à Paris 2', trim($titles->eq(3)->text()));
        $this->assertSame('Marche Parisienne', trim($titles->eq(4)->text()));

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites?page=2&type=past_events#events');
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites?page=2&type=past_events#events', $crawler->getUri());
        $titles = $crawler->filter('#past-events h2');
        $this->assertSame(2, $crawler->filter('#past-events article')->count());
        $this->assertSame('Grand Meeting de Paris', trim($titles->first()->text()));
        $this->assertSame('Grand Meeting de Marseille', trim($titles->last()->text()));
    }

    /**
     * @dataProvider provideProfilePage
     */
    public function testProfileActionIsSecured(string $profilePage): void
    {
        $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function provideProfilePage(): \Generator
    {
        yield 'Mon compte' => ['/parametres/mon-compte'];
        yield 'Mes informations personnelles' => ['/parametres/mon-compte/modifier'];
        yield 'Mes dons' => ['/parametres/mon-compte/mes-dons'];
        yield 'Mes centres d\'intérêt' => ['/espace-adherent/mon-compte/centres-d-interet'];
        yield 'Modifier mon profil' => ['/espace-adherent/mon-profil'];
        yield 'Notifications' => ['/parametres/mon-compte/preferences-des-emails'];
        yield 'Mot de passe' => ['/parametres/mon-compte/changer-mot-de-passe'];
    }

    public function testProfileActionIsAccessibleForAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('.settings .settings-menu ul li.active a'));
        $this->assertSame('Nom Carl Mirabeau', $crawler->filter('.settings__username')->text());
        $this->assertSame('Adhérent depuis novembre 2016.', $crawler->filter('.settings__membership')->text());
        $this->assertSame('Mon compte', $crawler->filter('.settings h2')->text());
    }

    public function testProfileActionIsAccessibleForInactiveAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'thomas.leclerc@example.ch');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('.settings .settings-menu ul li.active a'));
        $this->assertSame('Nom Thomas Leclerc', $crawler->filter('.settings__username')->text());
        $this->assertSame('Non adhérent.', $crawler->filter('.settings__membership')->text());
        $this->assertSame('Mon compte', $crawler->filter('.settings h2')->text());
    }

    public function testProfileActionIsNotAccessibleForDisabledAdherent(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'michelle.dufour@example.ch',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStringContainsString('Pour vous connecter vous devez confirmer votre adhésion. Si vous n\'avez pas reçu le mail de validation, vous pouvez cliquer ici pour le recevoir à nouveau.', $crawler->filter('#auth-error')->text());
    }

    public function testEditAdherentProfile(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');
        $oldLatitude = $adherent->getLatitude();
        $oldLongitude = $adherent->getLongitude();
        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '06');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '06');
        $histories73Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '73');
        $histories73Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '73');

        $this->assertCount(6, $histories73Subscriptions);
        $this->assertCount(0, $histories73Unsubscriptions);
        $this->assertCount(0, $histories06Subscriptions);
        $this->assertCount(0, $histories06Unsubscriptions);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/modifier');

        $inputPattern = 'input[name="adherent_profile[%s]"]';
        $optionPattern = 'select[name="adherent_profile[%s]"] option[selected="selected"]';

        self::assertSame('male', $crawler->filter(sprintf($optionPattern, 'gender'))->attr('value'));
        self::assertSame('Carl', $crawler->filter(sprintf($inputPattern, 'firstName'))->attr('value'));
        self::assertSame('Mirabeau', $crawler->filter(sprintf($inputPattern, 'lastName'))->attr('value'));
        self::assertSame('122 rue de Mouxy', $crawler->filter(sprintf($inputPattern, 'address][address'))->attr('value'));
        self::assertSame('73100', $crawler->filter(sprintf($inputPattern, 'address][postalCode'))->attr('value'));
        self::assertSame('73100-73182', $crawler->filter(sprintf($inputPattern, 'address][city'))->attr('value'));
        self::assertSame('France', $crawler->filter(sprintf($optionPattern, 'address][country'))->text());
        self::assertSame('01 11 22 33 44', $crawler->filter(sprintf($inputPattern, 'phone][number'))->attr('value'));
        self::assertSame('Retraité', $crawler->filter(sprintf($optionPattern, 'position'))->text());
        self::assertSame('1950-07-08', $crawler->filter(sprintf($inputPattern, 'birthdate'))->attr('value'));
        self::assertCount(2, $adherent->getReferentTags());
        self::assertAdherentHasReferentTag($adherent, '73');
        self::assertAdherentHasReferentTag($adherent, 'CIRCO_73004');

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'gender' => 'male',
                'firstName' => '',
                'lastName' => '',
                'nationality' => '',
                'address' => [
                    'address' => '',
                    'country' => 'FR',
                    'postalCode' => '',
                    'city' => '10102-45029',
                    'cityName' => '',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'position' => 'student',
            ],
        ]));

        $errors = $crawler->filter('.em-form--error');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertSame(6, $errors->count());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(2)->text());
        self::assertSame('L\'adresse est obligatoire.', $errors->eq(3)->text());
        self::assertSame('Veuillez renseigner un code postal.', $errors->eq(4)->text());
        self::assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.', $errors->eq(5)->text());

        // Submit the profile form with too long input
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'nationality' => 'FR',
                'address' => [
                    'address' => 'Une adresse de 150 caractères, ça peut arriver.Une adresse de 150 caractères, ça peut arriver.Une adresse de 150 caractères, ça peut arriver.Oui oui oui.',
                    'country' => 'FR',
                    'postalCode' => '0600000000000000',
                    'city' => '06000-6088',
                    'cityName' => 'Nice, France',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
                'position' => 'student',
                'birthdate' => '1985-10-27',
            ],
        ]));

        $errors = $crawler->filter('.em-form--error');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertSame(5, $errors->count());
        self::assertSame('L\'adresse ne peut pas dépasser 150 caractères.', $errors->eq(0)->text());
        self::assertSame('Le code postal doit contenir moins de 15 caractères.', $errors->eq(1)->text());
        self::assertSame('Cette valeur n\'est pas un code postal français valide.', $errors->eq(2)->text());
        self::assertSame('Cette valeur n\'est pas un code postal français valide.', $errors->eq(3)->text());
        self::assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.', $errors->eq(4)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'address' => [
                    'address' => '9 rue du Lycée',
                    'country' => 'FR',
                    'postalCode' => '06000',
                    'city' => '06000-6088',
                    'cityName' => 'Nice',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
                'position' => 'student',
                'birthdate' => '1985-10-27',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/modifier', $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeFlashMessage($crawler, 'Vos informations ont été mises à jour avec succès.');

        // We need to reload the manager reference to get the updated data
        /** @var Adherent $adherent */
        $adherent = $this->client->getContainer()->get('doctrine')->getManager()->getRepository(Adherent::class)->findOneByEmail('carl999@example.fr');

        self::assertSame('female', $adherent->getGender());
        self::assertSame('Jean Dupont', $adherent->getFullName());
        self::assertSame('9 rue du Lycée', $adherent->getAddress());
        self::assertSame('06000', $adherent->getPostalCode());
        self::assertSame('Nice', $adherent->getCityName());
        self::assertSame('401020304', $adherent->getPhone()->getNationalNumber());
        self::assertSame('student', $adherent->getPosition());
        $this->assertNotNull($newLatitude = $adherent->getLatitude());
        $this->assertNotNull($newLongitude = $adherent->getLongitude());
        $this->assertNotSame($oldLatitude, $newLatitude);
        $this->assertNotSame($oldLongitude, $newLongitude);
        self::assertCount(2, $adherent->getReferentTags());
        self::assertAdherentHasReferentTag($adherent, '06');
        self::assertAdherentHasReferentTag($adherent, 'CIRCO_06001');

        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '06');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '06');
        $histories73Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '73');
        $histories73Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '73');

        $this->assertCount(6, $histories73Subscriptions);
        $this->assertCount(6, $histories73Unsubscriptions);
        $this->assertCount(6, $histories06Subscriptions);
        $this->assertCount(0, $histories06Unsubscriptions);
    }

    public function testCertifiedAdherentCanNotEditFields(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/modifier');

        $disabledFields = $crawler->filter('form[name="adherent_profile"] input[disabled="disabled"], form[name="adherent_profile"] select[disabled="disabled"]');
        self::assertCount(5, $disabledFields);
        self::assertEquals('adherent_profile[firstName]', $disabledFields->eq(0)->attr('name'));
        self::assertEquals('adherent_profile[lastName]', $disabledFields->eq(1)->attr('name'));
        self::assertEquals('adherent_profile[gender]', $disabledFields->eq(2)->attr('name'));
        self::assertEquals('adherent_profile[customGender]', $disabledFields->eq(3)->attr('name'));
        self::assertEquals('adherent_profile[birthdate]', $disabledFields->eq(4)->attr('name'));
    }

    public function testEditAdherentInterests(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/centres-d-interet');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $checkBoxPattern = 'form[name="app_adherent_pin_interests"] .checkb-cols input[type="checkbox"][name="app_adherent_pin_interests[interests][]"]';

        $this->assertCount(18, $checkboxes = $crawler->filter($checkBoxPattern));

        $interests = $this->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);
        $interestsLabels = array_values($interests);

        foreach ($checkboxes as $i => $checkbox) {
            self::assertSame($interestsValues[$i], $checkbox->getAttribute('value'));
            self::assertSame($interestsLabels[$i], $crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->text());
        }

        $interests = $this->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);

        $chosenInterests = [
            4 => $interestsValues[4],
            8 => $interestsValues[8],
        ];

        $this->client->submit($crawler->selectButton('Enregistrer')->form(), [
            'app_adherent_pin_interests' => [
                'interests' => $chosenInterests,
            ],
        ]);

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/centres-d-interet', $this->client);

        /* @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');

        self::assertSame(array_values($chosenInterests), $adherent->getInterests());

        $crawler = $this->client->followRedirect();

        $this->assertCount(18, $checkboxes = $crawler->filter($checkBoxPattern));

        foreach ($checkboxes as $i => $checkbox) {
            if (isset($chosenInterests[$i])) {
                self::assertSame('checked', $checkbox->getAttribute('checked'));
            } else {
                $this->assertEmpty($crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->attr('checked'));
            }
        }
    }

    public function testAdherentChangePassword(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/changer-mot-de-passe');

        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[old_password]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[password][first]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[password][second]"]'));

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('adherent_change_password[submit]')->form(), [
            'adherent_change_password' => [
                'old_password' => '',
                'password' => [
                    'first' => '',
                    'second' => '',
                ],
            ],
        ]);

        $errors = $crawler->filter('.em-form--error');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame(2, $errors->count());
        self::assertSame('Le mot de passe est invalide.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('adherent_change_password[submit]')->form(), [
            'adherent_change_password' => [
                'old_password' => 'secret!12345',
                'password' => [
                    'first' => 'heaneaheah',
                    'second' => 'heaneaheah',
                ],
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/changer-mot-de-passe', $this->client);
    }

    public function testAdherentSetEmailNotifications(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');

        $this->assertFalse($adherent->hasSubscribedLocalHostEmails());
        $this->assertNotEmpty($adherent->getSubscriptionTypeCodes());

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/preferences-des-emails');
        $subscriptions = $crawler->filter('input[name="adherent_email_subscription[subscriptionTypes][]"]');

        $this->assertCount(8, $subscriptions);

        // Submit the emails subscription form with invalid data
        // We need to use a POST request because the crawler does not
        // accept any invalid choice, thus cannot submit invalid form
        $crawler = $this->client->request(Request::METHOD_POST, '/parametres/mon-compte/preferences-des-emails', [
            'adherent_email_subscription' => [
                'subscriptionTypes' => ['heah'],
                '_token' => $crawler->filter('input[name="adherent_email_subscription[_token]"]')->attr('value'),
            ],
        ]);

        $errors = $crawler->filter('.form__errors > li');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame(1, $errors->count());
        self::assertSame('Cette valeur n\'est pas valide.', $errors->eq(0)->text());

        // Submit the emails subscription form with valid data
        Chronos::setTestNow('+1 day');
        $this->client->submit($crawler->selectButton('adherent_email_subscription[submit]')->form(), [
            'adherent_email_subscription' => [
                'subscriptionTypes' => $this->getSubscriptionTypesFormValues([
                    SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                    SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                    SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                    SubscriptionTypeEnum::REFERENT_EMAIL,
                    SubscriptionTypeEnum::DEPUTY_EMAIL,
                ]),
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/preferences-des-emails', $this->client);

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');
        $histories = $this->findEmailSubscriptionHistoryByAdherent($adherent);
        $historiesHost = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
        $historiesReferents = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::REFERENT_EMAIL);

        $this->assertCount(9, $histories);
        $this->assertCount(1, $historiesHost);
        $this->assertCount(1, $historiesReferents);
        self::assertSame('subscribe', $historiesHost[0]->getAction());
        self::assertSame('subscribe', $historiesReferents[0]->getAction());
        $this->assertTrue($adherent->hasSubscribedLocalHostEmails());
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::REFERENT_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::DEPUTY_EMAIL));

        // Unsubscribe from 'subscribed_emails_local_host' and 'subscribed_emails_referents'
        Chronos::setTestNow('+1 week');
        $this->client->submit($crawler->selectButton('adherent_email_subscription[submit]')->form(), [
            'adherent_email_subscription' => [
                'subscriptionTypes' => $this->getSubscriptionTypesFormValues([
                    SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                    SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                ]),
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/preferences-des-emails', $this->client);

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');
        $histories = $this->findEmailSubscriptionHistoryByAdherent($adherent);
        $historiesHost = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
        $historiesReferents = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::REFERENT_EMAIL);

        $this->assertCount(12, $histories);
        $this->assertCount(2, $historiesHost);
        $this->assertCount(2, $historiesReferents);
        self::assertSame('unsubscribe', $historiesHost[0]->getAction());
        self::assertSame('unsubscribe', $historiesReferents[0]->getAction());

        Chronos::setTestNow('+2 weeks'); // To make sure the date order of the SQL query is correct
        // Re-subscribe to 'subscribed_emails_local_host' and 'subscribed_emails_referents'
        $this->client->submit($crawler->selectButton('adherent_email_subscription[submit]')->form(), [
            'adherent_email_subscription' => [
                'subscriptionTypes' => $this->getSubscriptionTypesFormValues([
                    SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                    SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                    SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                    SubscriptionTypeEnum::REFERENT_EMAIL,
                    SubscriptionTypeEnum::DEPUTY_EMAIL,
                ]),
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/preferences-des-emails', $this->client);

        $this->manager->clear();

        $histories = $this->findEmailSubscriptionHistoryByAdherent($adherent);
        $historiesHost = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
        $historiesReferents = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::REFERENT_EMAIL);

        $this->assertCount(15, $histories);
        $this->assertCount(3, $historiesHost);
        $this->assertCount(3, $historiesReferents);
        self::assertSame('subscribe', $historiesHost[0]->getAction());
        self::assertSame('subscribe', $historiesReferents[0]->getAction());
        Chronos::setTestNow();
    }

    /**
     * @return EmailSubscriptionHistory[]
     */
    public function findEmailSubscriptionHistoryByAdherent(
        Adherent $adherent,
        string $action = null,
        string $referentTagCode = null
    ): array {
        $qb = $this
            ->getEmailSubscriptionHistoryRepository()
            ->createQueryBuilder('history')
            ->where('history.adherentUuid = :adherentUuid')
            ->setParameter('adherentUuid', $adherent->getUuid())
            ->orderBy('history.date', 'DESC')
        ;

        if ($action) {
            $qb
                ->andWhere('history.action = :action')
                ->setParameter('action', $action)
            ;
        }

        if ($referentTagCode) {
            $qb
                ->leftJoin('history.referentTags', 'tag')
                ->andWhere('tag.code = :code')
                ->setParameter('code', $referentTagCode)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return EmailSubscriptionHistory[]
     */
    public function findAllEmailSubscriptionHistoryByAdherentAndType(
        Adherent $adherent,
        string $subscriptionType
    ): array {
        return $this
            ->getEmailSubscriptionHistoryRepository()
            ->createQueryBuilder('history')
            ->join('history.subscriptionType', 'subscriptionType')
            ->where('history.adherentUuid = :adherentUuid')
            ->andWhere('subscriptionType.code = :type')
            ->orderBy('history.date ', 'DESC')
            ->setParameter('adherentUuid', $adherent->getUuid())
            ->setParameter('type', $subscriptionType)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @dataProvider provideCommitteesHostsAdherentsCredentials
     */
    public function testAdherentsNotAllowedToCreateNewCommittees(string $emailAddress, string $warning): void
    {
        $this->authenticateAsAdherent($this->client, $emailAddress);
        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertSame(0, $crawler->selectLink('Créer un comité')->count());

        // Try to cheat the system with a direct URL access.
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString($warning, $crawler->filter('.committee__warning')->first()->text());
    }

    public function testProvisionalSupervisorOfRefusedCommitteeCanCreateAnotherOne(): void
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');

        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertSame(0, $crawler->selectLink('Créer un comité')->count());

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(0, $crawler->filter('.committee__warning'));
    }

    public function provideCommitteesHostsAdherentsCredentials(): array
    {
        return [
            'Benjamin Duroc is a provisional supervisor of a pending committee' => [
                'benjyd@aol.com',
                'Vous avez déjà un comité en attente de validation.',
            ],
            'Jacques Picard is already the owner of an existing committee' => [
                'jacques.picard@en-marche.fr',
                'Les parlementaires et les animateurs ne peuvent pas créer de comité.',
            ],
            'Gisèle Berthoux\'s profile is not certified' => [
                'gisele-berthoux@caramail.com',
                'Vous devez être certifié',
            ],
            'Deputy has an active parliamentary mandate' => [
                'deputy@en-marche-dev.fr',
                'Les parlementaires et les animateurs ne peuvent pas créer de comité.',
            ],
            'Lolodie Dutemps is minor' => [
                'lolodie.dutemps@hotnix.tld',
                'Vous devez être majeur pour créer un comité.',
            ],
        ];
    }

    /**
     * @dataProvider provideRegularAdherentsCredentials
     */
    public function testRegularAdherentCanCreateOneNewCommittee(string $emailAddress, string $phone): void
    {
        $this->client->followRedirects();

        $this->authenticateAsAdherent($this->client, $emailAddress);
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame($phone, $crawler->filter('#create_committee_phone_number')->attr('value'));

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Envoyer ma demande')->form([
            'create_committee' => [
                'name' => 'F',
                'description' => 'F',
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(9, $crawler->filter('#create-committee-form .form__errors > li')->count());
        $this->assertSame('Cette valeur n\'est pas un code postal français valide.', $crawler->filter('#create_committee_address_errors > li.form__error')->eq(0)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#create_committee_address_errors > li.form__error')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#create_committee_address_address_errors > li.form__error')->text());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $crawler->filter('#create_committee_phone_errors > li.form__error')->text());
        $this->assertSame('Vous devez saisir au moins 2 caractères.', $crawler->filter('#create_committee_name_errors > li.form__error')->text());
        $this->assertSame('Votre texte de description est trop court. Il doit compter 5 caractères minimum.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame('Vous devez accepter les règles de confidentialité.', $crawler->filter('#field-confidentiality-terms > .form__errors > li')->text());
        $this->assertSame("Vous devez accepter d'être contacté(e) par la plateforme En Marche !", $crawler->filter('#field-contacting-terms > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee
        $crawler = $this->client->submit($crawler->selectButton('Envoyer ma demande')->form([
            'create_committee[name]' => 'lyon est En Marche !',
            'create_committee[description]' => 'Comité français En Marche ! de la ville de Lyon',
            'create_committee[address][country]' => 'FR',
            'create_committee[address][address]' => '6 rue Neyret',
            'create_committee[address][postalCode]' => '69001',
            'create_committee[address][city]' => '69001-69381',
            'create_committee[address][cityName]' => '',
            'create_committee[phone][country]' => 'FR',
            'create_committee[phone][number]' => '0478457898',
            'create_committee[acceptConfidentialityTerms]' => true,
            'create_committee[acceptContactingTerms]' => true,
        ]));

        $this->assertInstanceOf(Committee::class, $committee = $this->committeeRepository->findMostRecentCommittee());
        $this->assertSame('Lyon est En Marche !', $committee->getName());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(CommitteeCreationConfirmationMessage::class, $emailAddress));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals('http://'.$this->getParameter('app_host').'/parametres/mes-activites', $this->client->getRequest()->getUri());
        $this->seeFlashMessage($crawler, 'Votre comité a été créé avec succès. Il ne manque plus que la validation d\'un référent.');
    }

    public function provideRegularAdherentsCredentials(): array
    {
        return [
            ['damien.schmidt@example.ch', '01 11 22 33 45'],
            ['adherent-male-a@en-marche-dev.fr', '06 99 00 88 00'],
        ];
    }

    public function testDocumentsActionSecured(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testDocumentsActionIsAccessibleAsAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Documents', $this->client->getResponse()->getContent());
    }

    public function testContactActionSecured(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testContactActionForAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Contacter Michelle Dufour', $this->client->getResponse()->getContent());

        $this->client->submit($crawler->selectButton('Envoyer')->form([
            'g-recaptcha-response' => 'dummy',
            'contact_message' => [
                'content' => 'A message I would like to send to Miss Dufour',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Votre message a bien été envoyé.');

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(AdherentContactMessage::class));
    }

    public function testContactActionWithInvalidUuid(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/wrong-uuid');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID, [
            'id' => 'wrong-uuid',
            'from' => 'event',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID, [
            'id' => 'wrong-uuid',
            'from' => 'committee',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    /**
     * @dataProvider dataProviderCannotTerminateMembership
     */
    public function testCannotTerminateMembership(string $email): void
    {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(0, $crawler->filter('.settings_unsubscribe'));

        $this->client->request(Request::METHOD_GET, '/mon-compte/desadherer');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function dataProviderCannotTerminateMembership(): \Generator
    {
        yield 'Host' => ['gisele-berthoux@caramail.com'];
        yield 'Referent' => ['referent@en-marche-dev.fr'];
        yield 'BoardMember' => ['carl999@example.fr'];
        yield 'CommitteeCandidate' => ['adherent-female-36@en-marche-dev.fr'];
        yield 'TerritorialCouncilCandidate' => ['senatorial-candidate@en-marche-dev.fr'];
    }

    /**
     * @dataProvider provideAdherentCredentials
     */
    public function testAdherentTerminatesMembership(
        string $userEmail,
        string $uuid,
        string $committee,
        int $nbFollowers
    ): void {
        /** @var Adherent $adherent */
        $adherentBeforeUnregistration = $this->getAdherentRepository()->findOneByEmail($userEmail);
        $referentTagsBeforeUnregistration = $adherentBeforeUnregistration->getReferentTags()->toArray(); // It triggers the real SQL query instead of lazy-load

        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(1, $crawler->filter('.settings__delete_account'));

        $crawler = $this->client->click($crawler->selectLink('Supprimer définitivement ce compte')->link());
        $this->assertEquals('http://'.$this->getParameter('app_host').'/parametres/mon-compte/desadherer', $this->client->getRequest()->getUri());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->submit($crawler->selectButton('Je confirme la suppression de mon adhésion')->form([
            'unregistration' => [],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(1, $errors->count());
        $this->assertSame('Afin de confirmer la suppression de votre compte, veuillez sélectionner la raison pour laquelle vous quittez le mouvement.', $errors->eq(0)->text());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/comites/%s', $committee));
        $this->assertStringContainsString("$nbFollowers adhérents", $crawler->filter('.committee__infos')->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/desadherer');
        $reasons = Unregistration::REASONS_LIST_ADHERENT;
        $reasonsValues = array_values($reasons);
        $chosenReasons = [
            1 => $reasonsValues[1],
            3 => $reasonsValues[3],
        ];

        $crawler = $this->client->submit($crawler->selectButton('Je confirme la suppression de mon adhésion')->form([
            'unregistration' => [
                'reasons' => $chosenReasons,
                'comment' => 'Je me désinscris',
            ],
        ]));

        $this->assertEquals('http://'.$this->getParameter('app_host').'/parametres/mon-compte/desadherer', $this->client->getRequest()->getUri());

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(0, $errors->count());
        $this->assertSame('Votre adhésion et votre compte En Marche ont bien été supprimés et vos données personnelles effacées de notre base.', trim($crawler->filter('#is_not_adherent h1')->eq(0)->text()));

        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(AdherentTerminateMembershipMessage::class, $userEmail));

        $this->client->getContainer()->get('test.'.RemoveAdherentAndRelatedDataCommandHandler::class)(
            new RemoveAdherentAndRelatedDataCommand(Uuid::fromString($uuid))
        );

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/comites/%s', $committee));
        --$nbFollowers;

        $this->assertStringContainsString("$nbFollowers adhérents", $crawler->filter('.committee__infos')->text());

        /** @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findOneByEmail($userEmail);

        $this->assertNull($adherent);

        /** @var Unregistration $unregistration */
        $unregistration = $this->get(UnregistrationRepository::class)->findOneByUuid($uuid);
        $mailHistorySubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherentBeforeUnregistration, 'subscribe');
        $mailHistoryUnsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherentBeforeUnregistration, 'unsubscribe');

        $this->assertSame(\count($mailHistorySubscriptions), \count($mailHistoryUnsubscriptions));
        $this->assertSame(array_values($chosenReasons), $unregistration->getReasons());
        $this->assertSame('Je me désinscris', $unregistration->getComment());
        $this->assertSame($adherentBeforeUnregistration->getRegisteredAt()->format('Y-m-d H:i:s'), $unregistration->getRegisteredAt()->format('Y-m-d H:i:s'));
        $this->assertSame((new \DateTime())->format('Y-m-d'), $unregistration->getUnregisteredAt()->format('Y-m-d'));
        $this->assertSame($adherentBeforeUnregistration->getUuid()->toString(), $unregistration->getUuid()->toString());
        $this->assertSame($adherentBeforeUnregistration->getPostalCode(), $unregistration->getPostalCode());
        $this->assertEquals($referentTagsBeforeUnregistration, $unregistration->getReferentTags()->toArray());
    }

    public function provideAdherentCredentials(): array
    {
        return [
            'adherent 1' => ['michel.vasseur@example.ch', LoadAdherentData::ADHERENT_13_UUID, 'en-marche-suisse', 3],
            'adherent 2' => ['cedric.lebon@en-marche-dev.fr', LoadAdherentData::ADHERENT_19_UUID, 'en-marche-comite-de-evry', 6],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeRepository = $this->getCommitteeRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->committeeRepository = null;

        parent::tearDown();
    }

    private function getSubscriptionTypesFormValues(array $codes): array
    {
        return array_map(static function (SubscriptionType $type) use ($codes) {
            return \in_array($type->getCode(), $codes, true) ? $type->getId() : false;
        }, $this->getSubscriptionTypeRepository()->findByCodes(SubscriptionTypeEnum::ADHERENT_TYPES));
    }
}
