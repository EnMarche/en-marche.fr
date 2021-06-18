<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\AssessorOfficeEnum;
use App\Mailer\Message\AssessorRequestConfirmationMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class AssessorControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const ASSESSOR_REQUEST_PATH = '/assesseur/demande';

    public function testAssessorRequest()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_REQUEST_PATH);

        $this->assertCount(0, $this->getEmailRepository()->findMessages(AssessorRequestConfirmationMessage::class));

        // Step 1
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Informations personnelles', $crawler->filter('.title h1')->text());
        $this->assertStringContainsString('1/3', $crawler->filter('span.step')->text());

        $this->client->submit($crawler->filter('form[name="assessor_request"]')->form([
            'assessor_request' => [
                'gender' => 'male',
                'firstName' => 'Ernestino',
                'lastName' => 'Bonsoirini',
                'birthName' => 'Boujourini',
                'address' => '39 rue du Welsh',
                'postalCode' => '59290',
                'city' => 'Wasquehal',
                'voteCity' => 'Lille',
                'officeNumber' => '001',
                'birthCity' => 'Lille',
                'emailAddress' => 'ernestino@bonsoirini.fr',
                'birthdate' => '1985-10-27',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0620202020',
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Step 2
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Informations assesseur(e)', $crawler->filter('.title h1')->text());
        $this->assertStringContainsString('2/3', $crawler->filter('span.step')->text());

        $form = $crawler->filter('form[name="assessor_request"]')->form();
        $form->getUri();

        $this->client->request('POST', $form->getUri(), [
            'assessor_request' => [
                'assessorCountry' => 'FR',
                'assessorPostalCode' => '59000',
                'assessorCity' => 'Lille',
                'votePlaceWishes' => [0 => 1],
                'office' => AssessorOfficeEnum::HOLDER,
                'reachable' => 1,
                'acceptDataTreatment' => 1,
                'acceptValuesCharter' => 1,
                '_token' => $form['assessor_request[_token]']->getValue(),
            ],
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Step 3
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Récapitulatif', $crawler->filter('.title h1')->text());
        $this->assertStringContainsString('3/3', $crawler->filter('span.step')->text());

        $this->assertStringContainsString('Homme', $crawler->filter('.summary-bloc tr.gender td:last-child')->text());
        $this->assertStringContainsString('Ernestino', $crawler->filter('.summary-bloc tr.firstname td:last-child')->text());
        $this->assertStringContainsString('Bonsoirini', $crawler->filter('.summary-bloc tr.lastname td:last-child')->text());
        $this->assertStringContainsString('39 rue du Welsh', $crawler->filter('.summary-bloc tr.address td:last-child')->text());
        $this->assertStringContainsString('59290', $crawler->filter('.summary-bloc tr.postalcode td:last-child')->text());
        $this->assertStringContainsString('Wasquehal', $crawler->filter('.summary-bloc tr.city td:last-child')->text());
        $this->assertStringContainsString('Wasquehal', $crawler->filter('.summary-bloc tr.city td:last-child')->text());
        $this->assertStringContainsString('001', $crawler->filter('.summary-bloc tr.office-number td:last-child')->text());
        $this->assertStringContainsString('Lille', $crawler->filter('.summary-bloc tr.birthcity td:last-child')->text());
        $this->assertStringContainsString('ernestino@bonsoirini.fr', $crawler->filter(
            '.summary-bloc tr.email td:last-child')->text()
        );
        $this->assertStringContainsString('Oui', $crawler->filter('.summary-bloc tr.reachable td:last-child')->text());
        $this->assertStringContainsString('+33 6 20 20 20 20', $crawler->filter('.summary-bloc tr.phone td:last-child')->text());
        $this->assertStringContainsString('France', $crawler->filter('.summary-bloc tr.assessor-country td:last-child')->text());
        $this->assertStringContainsString('Lille', $crawler->filter('.summary-bloc tr.assessor-city td:last-child')->text());
        $this->assertStringContainsString('59000', $crawler->filter('.summary-bloc tr.assessor-postalcode td:last-child')->text());
        $this->assertStringContainsString('Titulaire', $crawler->filter('.summary-bloc tr.assessor-office td:last-child')->text());
        $this->assertStringContainsString('Salle Polyvalente De Wazemmes', $crawler->filter(
            '.summary-bloc tr.assessor-vote-place-wishes td:last-child')->text()
        );

        $this->client->submit($crawler->selectButton('Valider')->form([
                'g-recaptcha-response' => 'dummy',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Confirmation
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Votre demande a bien été prise en compte.');
        $this->assertCount(1, $this->getEmailRepository()->findMessages(AssessorRequestConfirmationMessage::class));
    }

    /**
     * @dataProvider provideFormError
     */
    public function testAssessorRequestFormErrors(array $testedValue)
    {
        $this->expectException(\InvalidArgumentException::class);
        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_REQUEST_PATH);

        $this->client->submit($crawler->filter('form[name="assessor_request"]')->form([
            'assessor_request' => [
                $testedValue,
            ],
        ]));
    }

    public function provideFormError(): \Generator
    {
        yield 'Test wrong gender' => [['gender' => 'orc']];
        yield 'Test wrong phone number' => ['phone' => ['country' => 'TO']];
        yield 'Test wrong birthdate' => ['birthdate' => [
            'year' => (new \DateTime())->format('Y'),
            'month' => '01',
            'day' => '01',
        ]];
    }
}
