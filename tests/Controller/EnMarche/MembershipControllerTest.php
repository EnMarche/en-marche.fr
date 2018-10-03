<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mail\Transactional\AdherentAccountActivationMail;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\EmailRepository;
use AppBundle\Subscription\SubscriptionTypeEnum;
use EnMarche\MailerBundle\Test\MailTestCaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group membership
 */
class MembershipControllerTest extends WebTestCase
{
    use ControllerTestTrait,
        MailTestCaseTrait;

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
        $crawler = $this->client->submit($crawler->selectButton('Créer mon compte')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertValidationErrors(['data.emailAddress'], $this->client->getContainer());
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
        $this->assertFalse($adherent->hasCitizenProjectCreationEmailSubscription());

        /** @var Adherent $adherent */
        $this->assertInstanceOf(
            Adherent::class,
            $adherent = $this->client->getContainer()->get('doctrine')->getRepository(Adherent::class)->findOneByEmail('jean-paul@dupont.tld')
        );
        $this->assertSame('Jean-Paul', $adherent->getFirstName());
        $this->assertSame('Dupont', $adherent->getLastName());
        $this->assertInstanceOf(AdherentActivationToken::class, $activationToken = $this->activationTokenRepository->findAdherentMostRecentKey((string) $adherent->getUuid()));
        $this->assertMailCountForClass(1, AdherentAccountActivationMail::class);
        $this->assertMailSentForRecipient('jean-paul@dupont.tld', AdherentAccountActivationMail::class);

        // Activate the user account
        $activateAccountUrl = sprintf('/inscription/finaliser/%s/%s', $adherent->getUuid(), $activationToken->getValue());
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/adhesion', $this->client);

        $this->client->followRedirect();

        // User is automatically logged-in
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Activate user account twice
        $this->logout($this->client);
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Votre compte est déjà actif.', $crawler->filter('.flash')->text());

        // Try to authenticate with credentials
        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'jean-paul@dupont.tld',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertClientIsRedirectedTo('http://'.$this->hosts['app'].'/evenements', $this->client);

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
                    'allowNotifications' => true,
                ],
            ]
        );

        $this->assertClientIsRedirectedTo('/inscription/centre-interets', $this->client);
        $adherent = $this->getAdherentRepository()->findOneByEmail('test@test.com');

        self::assertCount(8, $adherent->getSubscriptionTypes());
    }

    private static function createFormData(): array
    {
        return [
            'g-recaptcha-response' => 'dummy',
            'user_registration' => [
                'firstName' => 'jean-pauL',
                'lastName' => 'duPont',
                'emailAddress' => [
                    'first' => 'jean-paul@dupont.tld',
                    'second' => 'jean-paul@dupont.tld',
                ],
                'password' => LoadAdherentData::DEFAULT_PASSWORD,
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '92110',
                ],
                'allowNotifications' => true,
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
        ]);

        $this->adherentRepository = $this->getAdherentRepository();
        $this->activationTokenRepository = $this->getActivationTokenRepository();
        $this->emailRepository = $this->getEmailRepository();
        $this->clearMails();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;
        $this->activationTokenRepository = null;
        $this->adherentRepository = null;
        $this->clearMails();

        parent::tearDown();
    }
}
