<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadTerritorialCouncilMembershipData;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Mailer\Message\VotingPlatformCandidacyInvitationAcceptedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationCreatedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationDeclinedMessage;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Mandrill\MailAssertTrait;
use Tests\App\Test\Helper\PHPUnitHelper;

/**
 * @group functional
 * @group designation
 */
class CandidatureControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MailAssertTrait;

    public function testICannotCandidateIfTerritorialCouncilHasNotAttachedDesignation(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request('GET', '/conseil-territorial');
        $this->assertResponseStatusCode(200, $response = $this->client->getResponse());
        $this->assertStringContainsString('Conseil territorial du département 92', $response->getContent());
    }

    public function testICannotModifyIfItIsNotCandidatePeriod(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Conseil territorial de Paris', $crawler->filter('.instance-page__infos li')->first()->text());

        /** @var Designation $designation */
        $designation = $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getCurrentDesignation();
        $designation->setCandidacyEndDate(new \DateTime('-2 hours'));
        $designation->setVoteStartDate(new \DateTime('+2 hours'));
        $designation->setVoteEndDate(new \DateTime('+4 hours'));
        $this->getEntityManager(Designation::class)->flush();

        $this->client->request('GET', '/conseil-territorial');
        $this->assertStringNotContainsString('Modifier mes informations', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/conseil-territorial/candidature');
        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);
        $this->client->followRedirect();

        $this->assertStringContainsString('Vous ne pouvez pas candidater ou modifier votre candidature pour cette désignation.', $content = $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('Retirer ma pré-candidature', $content);

        $this->client->request('GET', '/conseil-territorial/candidature/retirer');

        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);
        $this->client->followRedirect();

        $this->assertStringContainsString('Vous ne pouvez pas retirer votre candidature.', $this->client->getResponse()->getContent());
    }

    public function testICanRemoveMyCandidatureSuccessfully(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $this->client->click($crawler->selectLink('Retirer ma pré-candidature')->link());

        $crawler = $this->client->followRedirect();

        $this->assertStringContainsString('Votre candidature a bien été supprimée', $this->client->getResponse()->getContent());
        $this->assertSame('Je candidate en binôme', $crawler->filter('.instance__elections-box a.btn--pink')->text());
    }

    public function testICanUpdateMyCandidatureSuccessfully(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $this->client->click($crawler->selectLink('Modifier mes informations')->link());

        $this->assertStringEndsWith('/conseil-territorial/candidature', $crawler->getUri());

        $form = $crawler->selectButton('Enregistrer')->form();

        $values = $form->getValues();

        PHPUnitHelper::assertArraySubset([
            'territorial_council_candidacy[biography]' => 'Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.',
            'territorial_council_candidacy[faithStatement]' => 'Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.',
        ], $values);

        $this->client->submit($form, [
            'territorial_council_candidacy[biography]' => 'Bonjour, voici ma bio',
            'territorial_council_candidacy[faithStatement]' => 'ma plus belle profession de foi ...',
        ]);

        $this->client->followRedirect();

        $this->assertStringContainsString('Votre candidature a bien été enregistrée', $this->client->getResponse()->getContent());
    }

    public function testICanCreateMyCandidatureAndInviteAnotherMembership(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');

        $this->assertStringContainsString('Pierre Kiroule doit accepter votre demande pour que votre candidature soit confirmée.', trim($crawler->filter('.instance__elections-box')->text()));

        $this->assertStringContainsString('Modifier ma demande de binôme', $content = $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Modifier mes informations', $content);

        $this->client->click($crawler->selectLink('Retirer ma pré-candidature')->link());
        $crawler = $this->client->followRedirect();
        $crawler = $this->client->click($crawler->selectLink('Je candidate en binôme')->link());

        $form = $crawler->selectButton('Enregistrer et choisir mon binôme')->form();

        $crawler = $this->client->submit($form);

        self::assertCount(3, $errors = $crawler->filter('.form__error'));

        self::assertSame('Photo est obligatoire', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(2)->text());

        $this->client->submit($form, [
            'territorial_council_candidacy[image][croppedImage]' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAH0CAYAAADL1t+',
            'territorial_council_candidacy[biography]' => 'ma bio',
            'territorial_council_candidacy[faithStatement]' => 'ma profession de foi',
        ]);

        $this->assertClientIsRedirectedTo('/conseil-territorial/candidature/invitation', $this->client);

        // Choice of membership
        $crawler = $this->client->followRedirect();
        $form = $crawler->selectButton('Envoyer l\'invitation')->form();

        $crawler = $this->client->submit($form, ['candidacy_quality[quality]' => 'consular_councilor']);

        self::assertCount(1, $errors = $crawler->filter('.form__error'));
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());

        $values = $form->getPhpValues();
        $values['candidacy_quality']['quality'] = 'department_councilor';
        $values['candidacy_quality']['invitations'] = [[
            'membership' => LoadTerritorialCouncilMembershipData::MEMBERSHIP_UUID1,
        ]];

        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $values);

        self::assertCount(1, $errors = $crawler->filter('.form__error'));
        self::assertSame('La qualité choisie n\'est pas compatible avec votre choix.', $errors->eq(0)->text());

        $values['candidacy_quality']['quality'] = 'city_councilor';
        $values['candidacy_quality']['invitations'] = [[
            'membership' => LoadTerritorialCouncilMembershipData::MEMBERSHIP_UUID1,
        ]];

        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertClientIsRedirectedTo('/conseil-territorial/candidature/fini', $this->client);
        $this->assertCountMails(1, VotingPlatformCandidacyInvitationCreatedMessage::class, 'kiroule.p@blabla.tld');
    }

    public function testICanDeclineAnInvitation(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_12_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');

        self::assertCount(1, $crawler = $crawler->filter('.candidacy-invitation'));
        self::assertSame('Gisele Berthoux', trim($crawler->filter('.l__row .l__row')->text()));

        $this->client->click($crawler->selectLink('Décliner')->link());
        $this->client->followRedirect();

        self::assertStringContainsString('Invitation a bien été déclinée', $this->client->getResponse()->getContent());

        $this->assertCountMails(1, VotingPlatformCandidacyInvitationDeclinedMessage::class, 'gisele-berthoux@caramail.com');
    }

    public function testICanAcceptAnInvitation(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_12_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $crawler->filter('.candidacy-invitation');

        $crawler = $this->client->click($crawler->selectLink('Accepter')->link());

        $form = $crawler->selectButton('Accepter et enregistrer')->form();

        $values = $form->getValues();

        self::assertSame('Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.', $values['territorial_council_candidacy[faithStatement]']);
        self::assertEquals(1, $values['territorial_council_candidacy[isPublicFaithStatement]']);

        $this->client->submit($form, [
            'territorial_council_candidacy[image][croppedImage]' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAH0CAYAAADL1t+',
            'territorial_council_candidacy[biography]' => 'ma bio',
            'territorial_council_candidacy[faithStatement]' => 'ma profession de foi',
            'territorial_council_candidacy[isPublicFaithStatement]' => false,
        ]);

        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);

        $this->assertCountMails(1, VotingPlatformCandidacyInvitationAcceptedMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(1, VotingPlatformCandidacyInvitationAcceptedMessage::class, 'kiroule.p@blabla.tld');

        $crawler = $this->client->followRedirect();

        self::assertStringContainsString('Votre candidature a bien été enregistrée', $this->client->getResponse()->getContent());

        $crawler = $this->client->click($crawler->selectLink('Modifier mes informations')->link());
        $form = $crawler->selectButton('Enregistrer')->form();
        $values = $form->getValues();

        self::assertSame('ma profession de foi', $values['territorial_council_candidacy[faithStatement]']);
        self::assertArrayNotHasKey('territorial_council_candidacy[isPublicFaithStatement]', $values);

        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $this->client->click($crawler->selectLink('Modifier mes informations')->link());
        $form = $crawler->selectButton('Enregistrer')->form();
        $values = $form->getValues();

        self::assertSame('ma profession de foi', $values['territorial_council_candidacy[faithStatement]']);
        self::assertArrayNotHasKey('territorial_council_candidacy[isPublicFaithStatement]', $values);
    }
}
