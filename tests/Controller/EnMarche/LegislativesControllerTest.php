<?php

namespace Tests\App\Controller\EnMarche;

use App\Mailer\Message\LegislativeCampaignContactMessage;
use App\Repository\EmailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Mandrill\MailAssertTrait;

/**
 * @group functional
 * @group legislatives
 */
class LegislativesControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MailAssertTrait;

    /**
     * @var EmailRepository
     */
    private $emailRepository;

    public function testAnonymousUserIsForbiddenToSendContactMessage()
    {
        $this->client->request(Request::METHOD_GET, '/espace-candidat-legislatives/contact');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $crawler = $this->client->followRedirect();

        $this->assertSame(0, $crawler->filter('#legislatives_campaign_contact_form')->count());
        $this->assertSame(1, $crawler->filter('#_login_email')->count());
        $this->assertSame(1, $crawler->filter('#_login_password')->count());
    }

    public function testRegularAdherentIsForbiddenToSendContactMessage()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat-legislatives/contact');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
        $this->assertSame(0, $crawler->filter('#legislatives_campaign_contact_form')->count());
    }

    /**
     * @dataProvider provideLegislativeCandidatesContacts
     */
    public function testLegislativeCandidateIsAllowedToSendContactMessage(
        string $expectedRecipient,
        string $selectedRecipient
    ) {
        $this->authenticateAsAdherent($this->client, 'senatorial-candidate@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat-legislatives/contact');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('#legislatives_campaign_contact_form')->count());

        $crawler = $this->client->click($crawler->selectButton("J'envoie ma demande")->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('#legislatives_campaign_contact_form')->count());
        $this->assertSame(0, $crawler->filter('.flash--info')->count());
        $this->assertSame(7, $crawler->filter('#legislatives_campaign_contact_form .form__error')->count());
        $this->assertCount(0, $this->emailRepository->findMessages(LegislativeCampaignContactMessage::class));

        $this->client->click($crawler->selectButton("J'envoie ma demande")->form([
            'legislative_campaign_contact_message' => [
                'emailAddress' => 'marc1337@gmail.tld',
                'firstName' => 'Marc 🍇',
                'lastName' => '🍎 Dupont',
                'departmentNumber' => '92',
                'electoralDistrictNumber' => '3',
                'role' => 'Responsable communication',
                'recipient' => $selectedRecipient,
                'subject' => '🍔 Avez-vous pensez aux réseaux sociaux ? 🍔',
                'message' => 'Puis-je avoir accès aux comptes Twitter 🐦 et Facebook 📆 svp ?',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $emails = $this->emailRepository->findRecipientMessages(LegislativeCampaignContactMessage::class, $expectedRecipient);
        $this->assertCount(1, $emails);

        $email = $emails[0];

        self::assertMailSubject('Élections Législatives - Nouvelle demande de contact', $email);
        self::assertMailFromName('Marc Dupont', $email);
        self::assertMailTemplateName('legislative-campaign-contact', $email);

        self::assertMailVars([
            'email' => 'marc1337@gmail.tld',
            'first_name' => 'Marc',
            'last_name' => 'Dupont',
            'department_number' => '92',
            'electoral_district_number' => '3',
            'role' => 'Responsable communication',
            'subject' => 'Avez-vous pensez aux réseaux sociaux ?',
            'message' => 'Puis-je avoir accès aux comptes Twitter  et Facebook  svp ?',
        ], $email);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('#legislatives_campaign_contact_form')->count());
        $this->seeFlashMessage($crawler, "Votre demande d'information a été envoyée avec succès. Elle sera traitée dans les plus brefs délais par le service concerné.");
        $this->assertSame(0, $crawler->filter('#legislatives_campaign_contact_form .form__error')->count());
        $this->assertCount(1, $this->emailRepository->findMessages(LegislativeCampaignContactMessage::class));
    }

    public static function provideLegislativeCandidatesContacts(): array
    {
        return [
            ['comptes.legislatives@en-marche.fr', 'financial'],
            ['campagne.legislatives@en-marche.fr', 'standard'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;

        parent::tearDown();
    }
}
