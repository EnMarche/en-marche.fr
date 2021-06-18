<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\AdherentActivationToken;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait RegistrationTrait
{
    /**
     * Registers Paul Dupont (paul@dupont.tld) with "#example!12345#" as pass.
     *
     * the given crawler must contain the registration form.
     */
    private function register(
        KernelBrowser $client,
        Crawler $crawler,
        string $redirectUrl = '/espace-adherent/accueil'
    ): Crawler {
        $registrationButton = $crawler->selectButton('adherent_registration_submit');

        $this->assertCount(1, $registrationButton, 'The registration form wan not found.');

        $client->submit($registrationButton->form([
            'g-recaptcha-response' => 'dummy',
            'adherent_registration' => [
                'firstName' => 'Paul',
                'lastName' => 'Dupont',
                'nationality' => 'FR',
                'emailAddress' => [
                    'first' => 'paul@dupont.tld',
                    'second' => 'paul@dupont.tld',
                ],
                'gender' => 'male',
                'birthdate' => [
                    'year' => '1985',
                    'month' => '10',
                    'day' => '27',
                ],
                'address' => [
                    'address' => '45 rue Nationale',
                    'city' => '75008-75108',
                    'postalCode' => '75013',
                    'country' => 'FR',
                ],
                'phone' => [
                    'country' => '',
                    'number' => '',
                ],
                'password' => '#example!12345#',
                'conditions' => true,
                'allowEmailNotifications' => true,
                'allowMobileNotifications' => true,
            ],
        ]));

        $registrationSteps = [
            '/inscription/centre-interets',
            '/inscription/choisir-des-comites',
            '/inscription/don',
        ];

        foreach ($registrationSteps as $step) {
            $this->assertClientIsRedirectedTo($step, $client);

            $crawler = $client->followRedirect();
            $skip = $crawler->selectButton('Passer cette étape');

            if ($skip->count()) {
                $client->submit($skip->form());
            } else {
                $client->click($crawler->selectLink('Passer cette étape')->link());
            }
        }

        $this->assertSame('/presque-fini', $client->getRequest()->getPathInfo());
        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $tokens = $this->getRepository(AdherentActivationToken::class)->findAll();

        /** @var AdherentActivationToken $lastActivationToken */
        $this->assertInstanceOf(AdherentActivationToken::class, $lastActivationToken = end($tokens));

        $client->request(Request::METHOD_GET, sprintf(
            '/inscription/finaliser/%s/%s',
            $lastActivationToken->getAdherentUuid(),
            $lastActivationToken->getValue()
        ));

        $this->assertClientIsRedirectedTo($redirectUrl, $client);

        return $client->followRedirect();
    }
}
