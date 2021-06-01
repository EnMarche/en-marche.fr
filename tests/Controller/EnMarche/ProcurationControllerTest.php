<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\ElectionRound;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Procuration\ElectionContext;
use App\Procuration\ProcurationSession;
use App\Repository\ProcurationProxyRepository;
use App\Repository\ProcurationRequestRepository;
use libphonenumber\PhoneNumber;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group procuration
 */
class ProcurationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /** @var ProcurationRequestRepository */
    private $procurationRequestRepostitory;

    /** @var ProcurationProxyRepository */
    private $procurationProxyRepostitory;

    public function testLandingWithoutComingElection()
    {
        array_map(function (ElectionRound $round) {
            $round->setDate(new \DateTime('-5 days'));
        }, $this->getRepository(ElectionRound::class)->findAll());
        $this->getEntityManager(ElectionRound::class)->flush();

        $crawler = $this->client->request(Request::METHOD_GET, '/procuration');

        $this->isSuccessful($this->client->getResponse());
        $this->assertSame(
            'Pas d\'élection en approche.',
            trim($crawler->filter('.procuration__header--inner')->text())
        );
        $this->assertSame(
            'Pas d\'élection en approche.',
            trim($crawler->filter('.procuration__content')->text())
        );
    }

    public function testLanding()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(
            'Chaque vote compte.',
            trim($crawler->filter('.procuration__header--inner > h1')->text())
        );
        $this->assertSame(
            'L\'élection législative partielle pour la 1ère circonscription du Val-d\'Oise aura lieu les 28 janvier et 4 février 2018.',
            trim($crawler->filter('.procuration__header--inner > h2')->text())
        );
        $this->assertSame(
            'Si vous ne votez pas en France métropolitaine, renseignez-vous sur les dates.',
            trim($crawler->filter('.procuration__header--inner > div.text--body')->text())
        );
        $this->assertStringStartsWith(
            'Vous avez des questions concernant les modalités du vote par procuration ? Cliquez ici !',
            trim($crawler->filter('.procuration .l__wrapper p#procuration_faq')->text())
        );

        $this->assertCount(1, $crawler->filter('.procuration__content a:contains("Je me porte mandataire")'));
        $this->assertCount(1, $crawler->filter('.procuration__content div:last-child a:contains("Je donne procuration")'));
    }

    public function testChooseElectionOnRequest()
    {
        $this->assertFalse(
            $this->get(ProcurationSession::class)->hasElectionContext(),
            'The session should not have an election context yet.'
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/choisir/'.ElectionContext::ACTION_REQUEST);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(
            'La procuration avecLa République En Marche !',
            trim($crawler->filter('.procuration__header--outer h2')->text())
        );
        $this->assertStringStartsWith(
            'Vous avez des questions concernant les modalités du vote par procuration ? Cliquez ici !',
            trim($crawler->filter('.procuration__upper > section p#procuration_faq')->text())
        );
        $this->assertSame(
            'Un de nos volontaires peut porter votre voix',
            trim($crawler->filter('.procuration__content h2')->text())
        );

        $this->assertCount(2, $crawler->filter('#election_context_election > div.form__radio > input[type="radio"]'));
        $this->assertSame(
            'Élection législative partielle pour la 1ère circonscription du Val-d\'Oise',
            $crawler->filter('#election_context_election label')->text()
        );

        $crawler = $this->client->submit($crawler->selectButton('Continuer')->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('.form__error'));
        $this->assertSame('Vous devez choisir au moins une élection.', $error->text());

        $this->client->submit($crawler->selectButton('Continuer')->form(['election_context[election]' => 5]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/'.ProcurationRequest::STEP_URI_VOTE, $this->client);
        $this->assertTrue(
            static::$container->get(ProcurationSession::class)->hasElectionContext(),
            'The session should have saved an election context.'
        );
    }

    public function testChooseElectionOnProposal()
    {
        $this->assertFalse(
            $this->get(ProcurationSession::class)->hasElectionContext(),
            'The session should not have an election context yet.'
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/choisir/'.ElectionContext::ACTION_PROPOSAL);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(
            'La procuration avecLa République En Marche !',
            trim($crawler->filter('.procuration__header--outer h2')->text())
        );
        $this->assertStringStartsWith(
            'Vous avez des questions concernant les modalités du vote par procuration ? Cliquez ici !',
            trim($crawler->filter('.procuration__upper > section p#procuration_faq')->text())
        );
        $this->assertSame(
            'Portez la voix d\'un citoyen de votre ville',
            trim($crawler->filter('.procuration__content h2')->text())
        );
        $this->assertCount(2, $crawler->filter('#election_context_election input[type="radio"]'));
        $this->assertSame(
            'Élection législative partielle pour la 1ère circonscription du Val-d\'Oise',
            $crawler->filter('#election_context_election label')->text()
        );

        $crawler = $this->client->submit($crawler->selectButton('Continuer')->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('.form__error'));
        $this->assertSame('Vous devez choisir au moins une élection.', $error->text());

        $this->client->submit($crawler->selectButton('Continuer')->form(['election_context[election]' => 5]));

        $this->assertClientIsRedirectedTo('/procuration/je-propose', $this->client);
        $this->assertTrue(
            static::$container->get(ProcurationSession::class)->hasElectionContext(),
            'The session should have saved an election context.'
        );
    }

    public function testProcurationRequestLegacyIndex()
    {
        $this->client->request(Request::METHOD_GET, '/procuration/je-demande');

        $this->assertClientIsRedirectedTo('/procuration/je-demande/mon-lieu-de-vote', $this->client, false, true);
    }

    /**
     * @dataProvider provideStepsRequiringElectionContext
     */
    public function testProcurationRequestNeedsElectionContext(string $step)
    {
        $this->client->request(Request::METHOD_GET, "/procuration/je-demande/$step");

        $this->assertClientIsRedirectedTo('/procuration/choisir/'.ElectionContext::ACTION_REQUEST, $this->client);
    }

    public function provideStepsRequiringElectionContext(): iterable
    {
        yield [ProcurationRequest::STEP_URI_VOTE];
        yield [ProcurationRequest::STEP_URI_PROFILE];
        yield [ProcurationRequest::STEP_URI_ELECTION_ROUNDS];
    }

    public function testProcurationRequest()
    {
        $this->setElectionContext();

        $initialProcurationRequestCount = 13;
        $procurationRequest = new ProcurationRequest();

        $this->assertCurrentProcurationRequestSameAs($procurationRequest);
        $this->assertCount($initialProcurationRequestCount, $this->procurationRequestRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-demande/'.ProcurationRequest::STEP_URI_VOTE);

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_request' => [
                'voteCountry' => 'FR',
                'votePostalCode' => '92110',
                'voteCity' => '92110-92024',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/'.ProcurationRequest::STEP_URI_PROFILE, $this->client);

        $procurationRequest->setVoteCountry('FR');
        $procurationRequest->setVotePostalCode('92110');
        $procurationRequest->setVoteCity('92110-92024');
        $procurationRequest->setVoteCityName('');
        $procurationRequest->setVoteOffice('TestOfficeName');

        $this->assertCurrentProcurationRequestSameAs($procurationRequest);

        // Profile
        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_request' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'timothe.baume@example.gb',
                'address' => '6 rue Neyret',
                'country' => 'ES',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'birthdate' => '1950-01-20',
            ],
        ]));

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(0, $crawler->filter('.form--warning'));
        $this->assertCount(2, $errors = $crawler->filter('.form__error'));
        $this->assertSame('Pour la France, la saisie du code postal est obligatoire.', $errors->eq(0)->text());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $errors->eq(1)->text());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_request' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'timothe.baume@example.gb',
                'address' => '6 rue Neyret',
                'country' => 'FR',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0140998080',
                ],
                'birthdate' => '1950-01-20',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/'.ProcurationRequest::STEP_URI_ELECTION_ROUNDS, $this->client);

        $procurationRequest->setGender('male');
        $procurationRequest->setFirstNames('Paul, Jean, Martin');
        $procurationRequest->setLastName('Dupont');
        $procurationRequest->setEmailAddress('timothe.baume@example.gb');
        $procurationRequest->setAddress('6 rue Neyret');
        $procurationRequest->setCountry('FR');
        $procurationRequest->setPostalCode('69001');
        $procurationRequest->setCity('69001-69381');
        $procurationRequest->setCityName('');
        $procurationRequest->setPhone($this->createPhoneNumber('33', '140998080'));
        $procurationRequest->setBirthdate(date_create_from_format('Y m d His', '1950 1 20 000000'));

        $this->assertCurrentProcurationRequestSameAs($procurationRequest);

        // Elections
        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame("En cochant cette case, j'accepte d'être recontacté par LaREM pour la prochaine échéance électorale.", $crawler->filter('#procuration_reachable > label')->text());
        $this->assertSame("En cochant cette case, j'accepte que LaREM traite mes données dans le cadre de la plateforme de procuration en ligne. *", $crawler->filter('#procuration_authorization > label')->text());
        $this->assertStringContainsString("Les informations marquées d'un astérisque sont obligatoires", $crawler->filter('#procuration_legal_notices')->text());

        $crawler = $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_request' => [
                'electionRounds' => [],
                'reason' => ProcurationRequest::REASON_HEALTH,
                'authorization' => true,
            ],
        ]));

        $this->isSuccessful($this->client->getResponse());
        $this->assertSame('Vous devez choisir au moins un tour d\'élection.', $crawler->filter('.form__error')->text());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_request' => [
                'electionRounds' => ['10'],
                'reason' => ProcurationRequest::REASON_HEALTH,
                'authorization' => true,
                'reachable' => true,
            ],
        ]));

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-demande/'.ProcurationRequest::STEP_URI_THANKS, $this->client);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        // Procuration request should have been saved
        /* @var ProcurationRequest $request */
        $this->assertCount($initialProcurationRequestCount + 1, $requests = $this->procurationRequestRepostitory->findAll());
        $this->assertInstanceOf(ProcurationRequest::class, $request = end($requests));

        $this->assertSame('FR', $request->getVoteCountry());
        $this->assertSame('92110', $request->getVotePostalCode());
        $this->assertSame('Clichy', $request->getVoteCityName());
        $this->assertSame('TestOfficeName', $request->getVoteOffice());
        $this->assertSame('male', $request->getGender());
        $this->assertSame('Paul, Jean, Martin', $request->getFirstNames());
        $this->assertSame('Dupont', $request->getLastName());
        $this->assertSame('timothe.baume@example.gb', $request->getEmailAddress());
        $this->assertSame('FR', $request->getCountry());
        $this->assertSame('69001', $request->getPostalCode());
        $this->assertSame('Lyon 1er', $request->getCityName());
        $this->assertSame('6 rue Neyret', $request->getAddress());
        $this->assertSame(true, $request->isReachable());
        $this->assertEquals([$this->getRepository(ElectionRound::class)->find(10)], $request->getElectionRounds()->toArray());
        $this->assertSame(ProcurationRequest::REASON_HEALTH, $request->getReason());
    }

    public function testProcurationRequestAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $this->setElectionContext();

        $procurationRequest = new ProcurationRequest();

        $this->assertCurrentProcurationRequestSameAs($procurationRequest);

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-demande/'.ProcurationRequest::STEP_URI_VOTE);

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_request' => [
                'voteCountry' => 'FR',
                'votePostalCode' => '92110',
                'voteCity' => '92110-92024',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/'.ProcurationRequest::STEP_URI_PROFILE, $this->client);

        $procurationRequest->setVoteCountry('FR');
        $procurationRequest->setVotePostalCode('92110');
        $procurationRequest->setVoteCity('92110-92024');
        $procurationRequest->setVoteCityName('');
        $procurationRequest->setVoteOffice('TestOfficeName');

        $this->assertCurrentProcurationRequestSameAs($procurationRequest);

        $this->client->followRedirect();

        // Request should have been hydrated by user data
        $procurationRequest->setGender('female');
        $procurationRequest->setFirstNames('Lucie');
        $procurationRequest->setLastName('Olivera');
        $procurationRequest->setEmailAddress('luciole1989@spambox.fr');
        $procurationRequest->setAddress('13 boulevard des Italiens');
        $procurationRequest->setCountry('FR');
        $procurationRequest->setPostalCode('75009');
        $procurationRequest->setCity('75009-75109');
        $procurationRequest->setCityName('');
        $procurationRequest->setPhone($this->createPhoneNumber('33', '727363643'));
        $procurationRequest->setBirthdate(date_create_from_format('Y m d His', '1989 9 17 000000'));

        $this->assertCurrentProcurationRequestSameAs($procurationRequest);
    }

    public function testProcurationProposalNeedsElectionContext()
    {
        $this->client->request(Request::METHOD_GET, '/procuration/je-propose');

        $this->assertClientIsRedirectedTo('/procuration/choisir/'.ElectionContext::ACTION_PROPOSAL, $this->client);
    }

    public function testProcurationProposal()
    {
        $this->setElectionContext(ElectionContext::ACTION_PROPOSAL);

        $initialProcurationProxyCount = 8;

        $this->assertCount($initialProcurationProxyCount, $this->procurationProxyRepostitory->findAll(), 'There should not be any proposal at the moment');

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-propose');

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame("En cochant cette case, j'accepte d'être recontacté par LaREM pour la prochaine échéance électorale.", $crawler->filter('#procuration_reachable > label')->text());
        $this->assertSame("En cochant cette case, je m'engage à voter selon les vœux du mandant. *", $crawler->filter('#procuration_conditions > label')->text());
        $this->assertSame("En cochant cette case, j'accepte que LaREM traite mes données dans le cadre de la plateforme de procuration en ligne. *", $crawler->filter('#procuration_authorization > label')->text());
        $this->assertStringContainsString("Les informations marquées d'un astérisque sont obligatoires", $crawler->filter('#procuration_legal_notices')->text());

        $crawler = $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_proposal' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'maxime.michaux@example.fr',
                'address' => '6 rue Neyret',
                'country' => 'ES',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'birthdate' => '1950-01-20',
                'voteCountry' => 'FR',
                'votePostalCode' => '',
                'voteCity' => '92110-92024',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
                'electionRounds' => [],
                'conditions' => true,
                'authorization' => true,
                'proxiesCount' => 2,
            ],
        ]));

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(0, $crawler->filter('.form--warning'));
        $this->assertCount(3, $errors = $crawler->filter('.form__error'));
        $this->assertSame('Pour la France, la saisie du code postal est obligatoire.', $errors->eq(0)->text());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $errors->eq(1)->text());
        $this->assertSame('Vous devez choisir au moins un tour d\'élection.', $errors->eq(2)->text());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_proposal' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'maxime.michaux@example.fr',
                'address' => '6 rue Neyret',
                'country' => 'FR',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0140998080',
                ],
                'birthdate' => '1950-01-20',
                'voteCountry' => 'FR',
                'votePostalCode' => '92110',
                'voteCity' => '92110-92024',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
                'electionRounds' => ['10'],
                'conditions' => true,
                'authorization' => true,
                'proxiesCount' => 2,
                'reachable' => true,
            ],
        ]));

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-propose/merci', $this->client);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        /* @var ProcurationProxy $proposal */
        $this->assertCount($initialProcurationProxyCount + 1, $proposals = $this->procurationProxyRepostitory->findAll(), 'Procuration request should have been saved');
        $this->assertInstanceOf(ProcurationProxy::class, $proposal = end($proposals));
        $this->assertSame('FR', $proposal->getVoteCountry());
        $this->assertSame('92110', $proposal->getVotePostalCode());
        $this->assertSame('Clichy', $proposal->getVoteCityName());
        $this->assertSame('TestOfficeName', $proposal->getVoteOffice());
        $this->assertSame('male', $proposal->getGender());
        $this->assertSame('Paul, Jean, Martin', $proposal->getFirstNames());
        $this->assertSame('Dupont', $proposal->getLastName());
        $this->assertSame('maxime.michaux@example.fr', $proposal->getEmailAddress());
        $this->assertSame('FR', $proposal->getCountry());
        $this->assertSame('69001', $proposal->getPostalCode());
        $this->assertSame('Lyon 1er', $proposal->getCityName());
        $this->assertSame('6 rue Neyret', $proposal->getAddress());
        $this->assertSame(true, $proposal->isReachable());
        $this->assertSame(1, $proposal->getReliability());
        $this->assertEquals([$this->getRepository(ElectionRound::class)->find(10)], $proposal->getElectionRounds()->toArray());
    }

    public function testProcurationRequestNotUniqueEmailBirthDate()
    {
        $initialProcurationRequestCount = 13;

        $this->assertCount($initialProcurationRequestCount, $this->procurationRequestRepostitory->findAll());

        $this->setElectionContext();

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-demande/'.ProcurationRequest::STEP_URI_VOTE);

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_request' => [
                'voteCountry' => 'FR',
                'votePostalCode' => '75018',
                'voteCity' => '75018-75118',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/'.ProcurationRequest::STEP_URI_PROFILE, $this->client);

        // Profile
        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_request' => [
                'gender' => 'female',
                'firstNames' => 'Carine, Margaux',
                'lastName' => 'Édouard',
                'emailAddress' => 'caroline.edouard@example.fr',
                'address' => '165 rue Marcadet',
                'country' => 'FR',
                'postalCode' => '75018',
                'city' => '75018-75118',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0600010203',
                ],
                'birthdate' => '1968-10-09',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/'.ProcurationRequest::STEP_URI_ELECTION_ROUNDS, $this->client);

        // Profile
        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_request' => [
                'electionRounds' => ['10'],
                'reason' => ProcurationRequest::REASON_HEALTH,
                'authorization' => true,
            ],
        ]));

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-demande/merci', $this->client);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount($initialProcurationRequestCount + 1, $this->procurationRequestRepostitory->findAll(), 'Procuration request should have been saved');
    }

    public function testProcurationProposalNotUniqueEmailBirthdate()
    {
        $initialProcurationProxyCount = 8;

        $this->assertCount($initialProcurationProxyCount, $this->procurationProxyRepostitory->findAll(), 'There should not be any proposal at the moment');

        $this->setElectionContext();

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-propose');

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_proposal' => [
                'gender' => 'male',
                'firstNames' => 'Maxime',
                'lastName' => 'Michaux',
                'emailAddress' => 'maxime.michaux@example.fr',
                'address' => '14 rue Jules Ferry',
                'country' => 'FR',
                'postalCode' => '75018',
                'city' => '75018-75120',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0140998080',
                ],
                'birthdate' => '1989-02-17',
                'voteCountry' => 'FR',
                'votePostalCode' => '75018',
                'voteCity' => '75018-75120',
                'voteCityName' => '',
                'voteOffice' => 'Mairie',
                'electionRounds' => ['10'],
                'conditions' => true,
                'authorization' => true,
                'proxiesCount' => 2,
            ],
        ]));

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-propose/merci', $this->client);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount($initialProcurationProxyCount + 1, $this->procurationProxyRepostitory->findAll(), 'Procuration request should have been saved');
    }

    public function testMyRequestRequiresProcessedRequest()
    {
        $this->client->request(Request::METHOD_GET, '/procuration/ma-demande/4/'.(Uuid::uuid4()->toString()));

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testMyRequestRequiresValidToken()
    {
        $this->client->request(Request::METHOD_GET, '/procuration/ma-demande/5/'.(Uuid::uuid4()->toString()));

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testMyRequest()
    {
        /** @var ProcurationRequest $procurationRequest */
        $procurationRequest = $this->procurationRequestRepostitory->find(7);

        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/ma-demande/7/'.$procurationRequest->generatePrivateToken());

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(4, $rounds = $crawler->filter('.concerned-election_rounds li'));
        $this->assertSame('1er tour des éléctions présidentielles 2017', $rounds->eq(0)->text());
        $this->assertSame('2e tour des éléctions présidentielles 2017', $rounds->eq(1)->text());
        $this->assertSame('1er tour des éléctions législatives 2017', $rounds->eq(2)->text());
        $this->assertSame('2e tour des éléctions législatives 2017', $rounds->eq(3)->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->procurationRequestRepostitory = $this->getProcurationRequestRepository();
        $this->procurationProxyRepostitory = $this->getProcurationProxyRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->procurationRequestRepostitory = null;
        $this->procurationProxyRepostitory = null;

        parent::tearDown();
    }

    private function setElectionContext(string $action = ElectionContext::ACTION_REQUEST): void
    {
        if (!\in_array($action, [ElectionContext::ACTION_REQUEST, ElectionContext::ACTION_PROPOSAL])) {
            throw new \InvalidArgumentException(sprintf('$action must be "%s" or "%s"', ElectionContext::ACTION_REQUEST, ElectionContext::ACTION_PROPOSAL));
        }

        $crawler = $this->client->request(Request::METHOD_GET, "/procuration/choisir/$action");

        $this->client->submit($crawler->selectButton('Continuer')->form(['election_context[election]' => 5]));

        $path = ElectionContext::ACTION_REQUEST === $action ? 'je-demande/'.ProcurationRequest::STEP_URI_VOTE : 'je-propose';

        $this->assertClientIsRedirectedTo("/procuration/$path", $this->client);

        $this->client->followRedirect();
    }

    private function assertCurrentProcurationRequestSameAs(ProcurationRequest $expectedRequest): void
    {
        $this->assertEquals($expectedRequest, static::$container->get(ProcurationSession::class)->getCurrentRequest());
    }

    private function createPhoneNumber(string $country, string $number): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($country);
        $phone->setNationalNumber($number);

        return $phone;
    }
}
