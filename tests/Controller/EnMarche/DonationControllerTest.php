<?php

namespace Tests\App\Controller\EnMarche;

use App\Donation\PayboxPaymentSubscription;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\Transaction;
use App\Mailer\Message\DonationThanksMessage;
use App\Repository\DonationRepository;
use App\Repository\DonatorIdentifierRepository;
use App\Repository\DonatorRepository;
use App\Repository\TransactionRepository;
use Goutte\Client as PayboxClient;
use GuzzleHttp\Client;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Payment\PayboxProvider;

/**
 * @group functional
 * @group donation
 */
class DonationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const PAYBOX_PREPROD_URL = 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';

    /* @var PayboxClient */
    private $payboxClient;

    /* @var DonationRepository */
    private $donationRepository;

    /* @var TransactionRepository */
    private $transactionRepository;

    /* @var DonatorRepository */
    private $donatorRepository;

    /* @var DonatorIdentifierRepository */
    private $donatorIdentifierRepository;

    /* @var PayboxProvider */
    private $payboxProvider;

    public function getDonationSubscriptions(): iterable
    {
        yield 'None' => [PayboxPaymentSubscription::NONE];
        yield 'Unlimited' => [PayboxPaymentSubscription::UNLIMITED];
    }

    public function getInvalidSubscriptionsUrl(): iterable
    {
        yield 'invalid subscription' => ['/don/coordonnees?montant=30&abonnement=42'];
        yield 'without amount' => ['/don/coordonnees?abonnement=-1'];
    }

    public function testPayboxPreprodIsHealthy()
    {
        $client = new Client([
            'base_uri' => self::PAYBOX_PREPROD_URL,
            'timeout' => 0,
            'allow_redirects' => false,
        ]);

        if (Response::HTTP_OK === $client->request(Request::METHOD_HEAD)->getStatusCode()) {
            $this->assertSame('healthy', 'healthy');
        } else {
            $this->markTestSkipped('Paybox preprod server is not available.');
        }
    }

    /**
     * @depends testPayboxPreprodIsHealthy
     * @dataProvider getDonationSubscriptions
     */
    public function testSuccessFulProcess(int $duration)
    {
        $appClient = $this->client;
        // There should not be any donation for the moment
        $this->assertCount(0, $this->donationRepository->findAll());

        $lastAccountId = $this->donatorIdentifierRepository->findLastIdentifier()->getIdentifier();
        $this->assertSame('000052', $lastAccountId);

        $crawler = $appClient->request(Request::METHOD_GET, sprintf('/don/coordonnees?montant=30&abonnement=%d', $duration));

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $this->client->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation' => [
                'gender' => 'male',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'emailAddress' => 'test@paybox.com',
                'address' => '9 rue du Lycée',
                'country' => 'FR',
                'nationality' => 'FR',
                'postalCode' => '06000',
                'cityName' => 'Nice',
                'isPhysicalPerson' => true,
                'hasFrenchNationality' => true,
                'personalDataCollection' => true,
            ],
        ]));

        $this->assertStatusCode(302, $this->client);
        // Donation should have been saved
        $this->assertCount(1, $donations = $this->donationRepository->findAll());
        $this->assertInstanceOf(Donation::class, $donation = $donations[0]);

        /* @var Donation $donation */
        $this->assertEquals(3000, $donation->getAmount());
        $this->assertSame('male', $donation->getDonator()->getGender());
        $this->assertSame('Doe', $donation->getDonator()->getLastName());
        $this->assertSame('John', $donation->getDonator()->getFirstName());
        $this->assertSame('test@paybox.com', $donation->getDonator()->getEmailAddress());
        $this->assertSame('FR', $donation->getCountry());
        $this->assertSame('06000', $donation->getPostalCode());
        $this->assertSame('Nice', $donation->getCityName());
        $this->assertSame('9 rue du Lycée', $donation->getAddress());
        $this->assertSame(43.69949, $donation->getLatitude());
        $this->assertSame(7.274206, $donation->getLongitude());
        $this->assertSame($duration, $donation->getDuration());

        // Email should not have been sent
        $this->assertCount(0, $this->getEmailRepository()->findMessages(DonationThanksMessage::class));

        // We should be redirected to payment
        $this->assertClientIsRedirectedTo(sprintf('/don/%s/paiement', $donation->getUuid()->toString()), $appClient);

        $crawler = $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $formNode = $crawler->filter('input[name=PBX_CMD]');

        if ($suffix = PayboxPaymentSubscription::getCommandSuffix($donation->getAmount(), $donation->getDuration())) {
            $this->assertStringContainsString($suffix, $formNode->attr('value'));
        }

        /*
         * En-Marche payment page (verification and form to Paybox)
         */
        $formNode = $crawler->filter('form[name=app_donation_payment]');

        $this->assertSame(self::PAYBOX_PREPROD_URL, $formNode->attr('action'));

        $crawler = $this->payboxClient->submit($formNode->form());

        if (Response::HTTP_OK !== $status = $this->payboxClient->getInternalResponse()->getStatus()) {
            $this->markTestSkipped(sprintf('Paybox preproduction server has responded with %d.', $status));
        }

        /*
         * Paybox redirection and payment form
         */
        $crawler = $this->payboxClient->submit($crawler->filter('form[name=PAYBOX]')->form());

        // Pay using a testing account
        $crawler = $this->payboxClient->submit($crawler->filter('form[name=form_pay]')->form([
            'NUMERO_CARTE' => '4012001037141112',
            'MOIS_VALIDITE' => '12',
            'AN_VALIDITE' => '32',
            'CVVX' => '123',
        ]));

        $content = $this->payboxClient->getInternalResponse()->getContent();

        // Check payment was successful
        $callbackUrl = $crawler->filter('a')->attr('href');
        $callbackUrlRegExp = 'http://'.$this->hosts['app'].'/don/callback/(.+)'; // token
        $callbackUrlRegExp .= '\?id=(.+)_john-doe';
        if (PayboxPaymentSubscription::NONE !== $duration) {
            $durationRegExp = $duration < 0 ? 0 : $duration - 1;
            $callbackUrlRegExp .= 'PBX_2MONT0000003000PBX_NBPAIE0'.$durationRegExp.'PBX_FREQ01PBX_QUAND00';
        }
        $callbackUrlRegExp .= '&authorization=XXXXXX&result=00000';
        $callbackUrlRegExp .= '&transaction=(\d+)&amount=3000&date=(\d+)&time=(.+)';
        $callbackUrlRegExp .= '&card_type=(CB|Visa|MasterCard)&card_end=3212&card_print=(.+)&subscription=(\d+)&Sign=(.+)';

        $this->assertRegExp('#'.$callbackUrlRegExp.'#', $content);
        $this->assertRegExp('#'.$callbackUrlRegExp.'#', $callbackUrl);

        $appClient->request(Request::METHOD_GET, $callbackUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $appClient->getResponse());

        $statusUrl = $appClient->getResponse()->headers->get('location');
        $statusUrlRegExp = '/don/(.+)'; // uuid
        $statusUrlRegExp .= '/effectue\?code=donation_paybox_success&result=00000&is_registration=0&_status_token=(.+)';

        $this->assertRegExp('#'.$statusUrlRegExp.'#', $statusUrl);

        $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        self::assertSame('OK', $this->simulateIpnCall($donation, '00000'));

        // Donation should have been completed
        $donation = $this->client->getContainer()->get(DonationRepository::class)->findAll()[0];
        $donator = $donation->getDonator();

        $this->assertFalse($donation->hasError());
        if ($donation->hasSubscription()) {
            $this->assertTrue($donation->isSubscriptionInProgress());
            $donation->nextDonationAt();
        } else {
            $this->assertTrue($donation->isFinished());
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Donation without subscription can\'t have next donation date.');
            $donation->nextDonationAt();
        }
        $transactions = $donation->getTransactions();
        $this->assertCount(1, $transactions);
        $transaction = $transactions->first();
        self::assertSame('00000', $transaction->getPayboxResultCode());
        self::assertSame('XXXXXX', $transaction->getPayboxAuthorizationCode());

        $this->assertInstanceOf(Donator::class, $donator);
        $this->assertSame($donator->getEmailAddress(), $donation->getDonator()->getEmailAddress());
        $this->assertEquals($donator->getLastDonationDate(), $donation->getCreatedAt());
        $this->assertSame('000053', $donator->getIdentifier());

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(DonationThanksMessage::class));
    }

    /**
     * @depends testPayboxPreprodIsHealthy
     * @dataProvider getDonationSubscriptions
     */
    public function testRetryProcess(int $duration)
    {
        $appClient = $this->client;
        // There should not be any donation for the moment
        $this->assertCount(0, $this->donationRepository->findAll());

        $crawler = $appClient->request(Request::METHOD_GET, sprintf('/don/coordonnees?montant=30&abonnement=%d', $duration));

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $this->client->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation' => [
                'gender' => 'male',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'emailAddress' => 'test@paybox.com',
                'address' => '9 rue du Lycée',
                'country' => 'FR',
                'nationality' => 'FR',
                'postalCode' => '06000',
                'cityName' => 'Nice',
                'isPhysicalPerson' => true,
                'hasFrenchNationality' => true,
                'personalDataCollection' => true,
            ],
        ]));

        // Donation should have been saved
        $this->assertCount(1, $donations = $this->donationRepository->findAll());
        $this->assertInstanceOf(Donation::class, $donation = $donations[0]);

        /* @var Donation $donation */
        $this->assertEquals(3000, $donation->getAmount());
        $this->assertSame('male', $donation->getDonator()->getGender());
        $this->assertSame('Doe', $donation->getDonator()->getLastName());
        $this->assertSame('John', $donation->getDonator()->getFirstName());
        $this->assertSame('test@paybox.com', $donation->getDonator()->getEmailAddress());
        $this->assertSame('FR', $donation->getCountry());
        $this->assertSame('06000', $donation->getPostalCode());
        $this->assertSame('Nice', $donation->getCityName());
        $this->assertSame('FR', $donation->getNationality());
        $this->assertSame('9 rue du Lycée', $donation->getAddress());
        $this->assertSame($duration, $donation->getDuration());

        // Email should not have been sent
        $this->assertCount(0, $this->getEmailRepository()->findMessages(DonationThanksMessage::class));

        // We should be redirected to payment
        $this->assertClientIsRedirectedTo(sprintf('/don/%s/paiement', $donation->getUuid()->toString()), $appClient);

        $crawler = $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $formNode = $crawler->filter('input[name=PBX_CMD]');

        if ($suffix = PayboxPaymentSubscription::getCommandSuffix($donation->getAmount(), $donation->getDuration())) {
            $this->assertStringContainsString($suffix, $formNode->attr('value'));
        }

        /*
         * En-Marche payment page (verification and form to Paybox)
         */
        $formNode = $crawler->filter('form[name=app_donation_payment]');

        $this->assertSame(self::PAYBOX_PREPROD_URL, $formNode->attr('action'));

        /*
         * Paybox cancellation of payment form
         */
        $crawler = $this->payboxClient->submit($formNode->form());

        if (Response::HTTP_OK !== $status = $this->payboxClient->getInternalResponse()->getStatus()) {
            $this->markTestSkipped(sprintf('Paybox preproduction server has responded with %d.', $status));
        }

        $crawler = $this->payboxClient->submit($crawler->filter('form[name=PAYBOX]')->form());
        $cancelUrl = $crawler->filter('#pbx-annuler a')->attr('href');
        $cancelUrlRegExp = 'http://'.$this->hosts['app'].'/don/callback/(.+)'; // token
        $cancelUrlRegExp .= '\?id=(.+)_john-doe';
        if (PayboxPaymentSubscription::NONE !== $duration) {
            $durationRegExp = $duration < 0 ? 0 : $duration - 1;
            $cancelUrlRegExp .= 'PBX_2MONT0000003000PBX_NBPAIE0'.$durationRegExp.'PBX_FREQ01PBX_QUAND00';
        }
        $cancelUrlRegExp .= '&result=00001'; // error code
        $cancelUrlRegExp .= '&transaction=0&amount=3000&subscription=0&Sign=(.+)';

        $this->assertRegExp('#'.$cancelUrlRegExp.'#', $cancelUrl);

        $appClient->request(Request::METHOD_GET, $cancelUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $appClient->getResponse());

        $statusUrl = $appClient->getResponse()->headers->get('location');
        $statusUrlRegExp = '/don/(.+)'; // uuid
        $statusUrlRegExp .= '/erreur\?code=paybox&result=00001&is_registration=0&_status_token=(.+)';

        $this->assertRegExp('#'.$statusUrlRegExp.'#', $statusUrl);

        $crawler = $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        self::assertSame('OK', $this->simulateIpnCall($donation, '00001'));

        // Donation should have been aborted
        $donation = $this->client->getContainer()->get(DonationRepository::class)->findAll()[0];
        $this->assertTrue($donation->hasError());
        /** @var Transaction[] $transactions */
        $transactions = $this->transactionRepository->findBy(['donation' => $donation]);
        $this->assertCount(1, $transactions);
        $transaction = $transactions[0];
        self::assertSame('00001', $transaction->getPayboxResultCode());
        self::assertSame('XXXXXX', $transaction->getPayboxAuthorizationCode());
        $this->assertNull($transaction->getPayboxTransactionId());
        // Email should not have been sent
        $this->assertCount(0, $this->getEmailRepository()->findMessages(DonationThanksMessage::class));

        $retryUrl = $crawler->selectLink('Je souhaite réessayer')->attr('href');
        $retryUrlRegExp = '/don/coordonnees\?donation_retry_payload=(.*)&montant=30';

        $this->assertRegExp('#'.$retryUrlRegExp.'#', $retryUrl);

        $crawler = $this->client->request(Request::METHOD_GET, $retryUrl);

        $this->assertStatusCode(Response::HTTP_OK, $appClient);
        $this->assertStringContainsString('Doe', $crawler->filter('input[name="app_donation[lastName]"]')->attr('value'), 'Retry should be prefilled.');
    }

    /**
     * @depends testPayboxPreprodIsHealthy
     * @dataProvider getInvalidSubscriptionsUrl
     */
    public function testInvalidSubscription(string $url)
    {
        $this->client->request(Request::METHOD_GET, $url);

        $this->assertClientIsRedirectedTo('/don', $this->client);
    }

    /**
     * @dataProvider provideForeignersLivingOutsideFranceCanNotDonate
     */
    public function testCanForeignersLivingOutsideFranceCanNotDonate(string $nationality, string $country): void
    {
        $crawler = $this->client->request('GET', '/don/coordonnees?montant=30');

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation' => [
                'gender' => 'male',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'emailAddress' => 'test@paybox.com',
                'address' => '9 rue du Lycée',
                'country' => $country,
                'nationality' => $nationality,
                'postalCode' => '06000',
                'cityName' => 'Nice',
                'isPhysicalPerson' => true,
                'hasFrenchNationality' => true,
                'personalDataCollection' => true,
            ],
        ]));

        $this->assertStatusCode(200, $this->client);
        $this->assertCount(1, $errors = $crawler->filter('.form__error'));

        $error = $errors->first()->text();
        $this->assertStringContainsString(
            'Nous sommes désolés mais votre don ne peut pas être finalisé, '
            .'nous ne pouvons accepter que les dons des personnes ayant la nationalité française '
            .'ou le foyer fiscal en France.',
            $error
        );
        $this->assertStringContainsString(
            'L’article 11-4 de la loi N° 88-227 du 11 mars 1988 '
            .'relative à la transparence financière de la vie politique énonce qu’ '
            .'« Une personne physique peut verser un don à un parti ou groupement politique '
            .'si elle est de nationalité française ou si elle réside en France ».',
            $error
        );
    }

    public function provideForeignersLivingOutsideFranceCanNotDonate(): iterable
    {
        yield ['DE', 'DE'];
        yield ['DE', 'GB'];
        yield ['GB', 'DE'];
        yield ['GB', 'GB'];
    }

    public function testCallbackWithNoId()
    {
        $this->client->request(Request::METHOD_GET, '/don/callback/token');

        $this->assertClientIsRedirectedTo('/don', $this->client);
    }

    public function testCallbackWithWrongUuid()
    {
        $this->client->request(Request::METHOD_GET, '/don/callback/token', [
            'id' => 'wrong_uuid',
        ]);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/don', $this->client);
    }

    public function testCallbackWithWrongToken()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/don/coordonnees?montant=30');

        $this->client->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation' => [
                'gender' => 'male',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'emailAddress' => 'test@paybox.com',
                'address' => '9 rue du Lycée',
                'country' => 'FR',
                'nationality' => 'FR',
                'postalCode' => '06000',
                'cityName' => 'Nice',
                'isPhysicalPerson' => true,
                'hasFrenchNationality' => true,
                'personalDataCollection' => true,
            ],
        ]));

        // Donation should have been saved
        /** @var Donation[] $donations */
        $this->assertCount(1, $donations = $this->donationRepository->findAll());
        $this->assertInstanceOf(Donation::class, $donation = $donations[0]);

        $this->client->request(Request::METHOD_GET, '/don/callback/token', [
            'id' => $donation->getUuid()->toString().'_',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        // Delete all donations for tests
        $this->getRepository(Transaction::class)->createQueryBuilder('t')->delete()->getQuery()->execute();
        $this->getDonationRepository()->createQueryBuilder('d')->delete()->getQuery()->execute();

        $this->payboxClient = new PayboxClient();
        $this->donationRepository = $this->getDonationRepository();
        $this->donatorRepository = $this->getDonatorRepository();
        $this->donatorIdentifierRepository = $this->getDonatorIdentifierRepository();
        $this->transactionRepository = $this->getTransactionRepository();
        $this->payboxProvider = $this->get(PayboxProvider::class);
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->payboxClient = null;
        $this->donationRepository = null;
        $this->donatorRepository = null;
        $this->donatorIdentifierRepository = null;
        $this->transactionRepository = null;
        $this->payboxProvider = null;

        parent::tearDown();
    }

    private function simulateIpnCall(Donation $donation, string $status): string
    {
        return $this->client
            ->request(
                'POST',
                $this->payboxProvider->getIpnUri(),
                $this->payboxProvider->prepareCallbackParameters($donation->getUuid()->toString(), $status)
            )
            ->text()
        ;
    }
}
