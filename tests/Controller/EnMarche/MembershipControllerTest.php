<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\Coalition\CauseFollower;
use App\Mailer\Message\AdherentAccountActivationMessage;
use App\Repository\AdherentActivationTokenRepository;
use App\Repository\AdherentRepository;
use App\Repository\EmailRepository;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group membership
 */
class MembershipControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var AdherentActivationTokenRepository
     */
    private $activationTokenRepository;

    /**
     * @var EmailRepository
     */
    private $emailRepository;

    /**
     * @dataProvider provideEmailAddress
     */
    public function testCannotCreateMembershipAccountWithSomeoneElseEmailAddress(string $emailAddress): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription-utilisateur');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['user_registration']['emailAddress']['first'] = $emailAddress;
        $data['user_registration']['emailAddress']['second'] = $emailAddress;
        $data['user_registration']['nationality'] = 'FR';
        $crawler = $this->client->submit($crawler->selectButton('Créer mon compte')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
//        $this->assertValidationErrors(['data.emailAddress'], $this->client->getContainer());
        $errors = $crawler->filter('.form__error');
        $this->assertSame('Cette adresse e-mail existe déjà.', $errors->text());
    }

    /**
     * These data come from the LoadAdherentData fixtures file.
     *
     * @see LoadAdherentData
     */
    public function provideEmailAddress(): array
    {
        return [
            ['michelle.dufour@example.ch'],
            ['carl999@example.fr'],
        ];
    }

    public function testCreateMembershipAccountForFrenchAdherentIsSuccessful(): void
    {
        $follower = $this->getCauseFollowerRepository()->findOneBy(['emailAddress' => 'jean-paul@dupont.tld']);

        $this->assertInstanceOf(CauseFollower::class, $follower);

        $followerId = $follower->getId();

        $this->client->request(Request::METHOD_GET, '/inscription-utilisateur');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Créer mon compte')->form(), static::createFormData());

        $this->assertClientIsRedirectedTo('/presque-fini', $this->client);

        $adherent = $this->getAdherentRepository()->findOneByEmail('jean-paul@dupont.tld');
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertNull($adherent->getGender());
        $this->assertSame('Jean-Paul', $adherent->getFirstName());
        $this->assertSame('Dupont', $adherent->getLastName());
        $this->assertEmpty($adherent->getAddress());
        $this->assertEmpty($adherent->getCityName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertNull($adherent->getBirthdate());
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());
        $this->assertNull($adherent->getPosition());
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::MILITANT_ACTION_SMS));
        $this->assertFalse($adherent->hasSubscribedLocalHostEmails());

        /** @var Adherent $adherent */
        $this->assertInstanceOf(
            Adherent::class,
            $adherent = $this->client->getContainer()->get('doctrine')->getRepository(Adherent::class)->findOneByEmail('jean-paul@dupont.tld')
        );
        $this->assertSame('Jean-Paul', $adherent->getFirstName());
        $this->assertSame('Dupont', $adherent->getLastName());
        $this->assertInstanceOf(AdherentActivationToken::class, $activationToken = $this->activationTokenRepository->findAdherentMostRecentKey((string) $adherent->getUuid()));
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(AdherentAccountActivationMessage::class, 'paul@dupont.tld'));

        // Activate the user account
        $activateAccountUrl = sprintf('/inscription/finaliser/%s/%s', $adherent->getUuid(), $activationToken->getValue());
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/adhesion', $this->client);

        $this->client->followRedirect();

        // User is automatically logged-in
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // check follower
        $this->manager->clear();
        $follower = $this->getCauseFollowerRepository()->findOneBy(['emailAddress' => 'jean-paul@dupont.tld']);

        $this->assertNull($follower);

        $follower = $this->getCauseFollowerRepository()->findOneBy(['id' => $followerId]);

        $this->assertInstanceOf(CauseFollower::class, $follower);
        $this->assertSame('jean-paul@dupont.tld', $follower->getAdherent()->getEmailAddress());

        // Activate user account twice
        $this->logout($this->client);
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Votre compte est déjà actif.', $crawler->filter('.flash')->text());

        // Try to authenticate with credentials
        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'jean-paul@dupont.tld',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertClientIsRedirectedTo('/evenements', $this->client);

        $this->client->followRedirect();
    }

    public function testAdherentSubscriptionTypesArePersistedCorrectly(): void
    {
        $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Je rejoins La République En Marche')->form(),
            [
                'g-recaptcha-response' => 'fake',
                'adherent_registration' => [
                    'firstName' => 'Test',
                    'lastName' => 'Adhesion',
                    'nationality' => 'FR',
                    'emailAddress' => [
                        'first' => 'test@test.com',
                        'second' => 'test@test.com',
                    ],
                    'password' => '12345678',
                    'address' => [
                        'address' => '1 rue des alouettes',
                        'postalCode' => '94320',
                        'cityName' => 'Thiais',
                        'city' => '94320-94073',
                        'country' => 'FR',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'male',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => true,
                ],
            ]
        );

        $this->assertClientIsRedirectedTo('/inscription/centre-interets', $this->client);
        $adherent = $this->getAdherentRepository()->findOneByEmail('test@test.com');

        self::assertCount(8, $adherent->getSubscriptionTypes());
    }

    public function testAdherentSubscriptionTypesArePersistedCorrectlyWhenAdhesionFromUser(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('simple-user@example.ch');

        self::assertCount(0, $adherent->getSubscriptionTypes());

        $this->authenticateAsAdherent($this->client, 'simple-user@example.ch');
        $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Je rejoins La République En Marche')->form(),
            [
                'become_adherent' => [
                    'nationality' => 'FR',
                    'address' => [
                        'address' => '32 Zeppelinstrasse',
                        'cityName' => 'Zürich',
                        'postalCode' => '8057',
                        'country' => 'CH',
                    ],
                    'phone' => [
                        'number' => '06 12 34 56 78',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'male',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => false,
                ],
            ]
        );

        $this->assertClientIsRedirectedTo('/espace-adherent/accueil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
        self::assertSame('Votre compte adhérent est maintenant actif.', $crawler->filter('.flash__inner')->text());

        $adherent = $this->getAdherentRepository()->findOneByEmail('simple-user@example.ch');

        self::assertCount(7, $adherent->getSubscriptionTypes());
    }

    public function testBannedAdherentSubscription(): void
    {
        $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Je rejoins La République En Marche')->form(),
            [
                'g-recaptcha-response' => 'fake',
                'adherent_registration' => [
                    'firstName' => 'Test',
                    'lastName' => 'Adhesion',
                    'emailAddress' => [
                        'first' => 'damien.schmidt@example.ch',
                        'second' => 'damien.schmidt@example.ch',
                    ],
                    'password' => '12345678',
                    'address' => [
                        'address' => '1 rue des alouettes',
                        'postalCode' => '94320',
                        'cityName' => 'Thiais',
                        'city' => '94320-94073',
                        'country' => 'FR',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'male',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => true,
                ],
            ]
        );

        $this->assertStringContainsString('Oups, quelque chose s\'est mal passé', $this->client->getCrawler()->filter('#adherent_registration_emailAddress_first_errors')->text());
    }

    public function testCreateAdherentWithCustomGender(): void
    {
        $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Je rejoins La République En Marche')->form(),
            [
                'g-recaptcha-response' => 'fake',
                'adherent_registration' => [
                    'firstName' => 'Test',
                    'lastName' => 'Adhesion',
                    'emailAddress' => [
                        'first' => 'custom.gender@example.fr',
                        'second' => 'custom.gender@example.fr',
                    ],
                    'password' => '12345678',
                    'address' => [
                        'address' => '1 rue des alouettes',
                        'postalCode' => '94320',
                        'cityName' => 'Thiais',
                        'city' => '94320-94073',
                        'country' => 'FR',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'other',
                    'customGender' => 'my custom gender',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => true,
                ],
            ]
        );
    }

    private static function createFormData(): array
    {
        return [
            'g-recaptcha-response' => 'dummy',
            'user_registration' => [
                'firstName' => 'jean-pauL',
                'lastName' => 'duPont',
                'nationality' => 'FR',
                'emailAddress' => [
                    'first' => 'jean-paul@dupont.tld',
                    'second' => 'jean-paul@dupont.tld',
                ],
                'password' => LoadAdherentData::DEFAULT_PASSWORD,
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '92110',
                ],
                'allowEmailNotifications' => true,
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->activationTokenRepository = $this->getActivationTokenRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->activationTokenRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
