<?php

namespace Tests\App\Controller\EnMarche\CommitteeDesignation;

use App\DataFixtures\ORM\LoadCommitteeData;
use App\Mailer\Message\VotingPlatformCandidacyInvitationCreatedMessage;
use Doctrine\ORM\EntityManager;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Mandrill\MailAssertTrait;

/**
 * @group functional
 * @group designation
 */
class CandidatureControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MailAssertTrait;

    public function testICanCreateMyCandidatureAndInviteAnotherMembership(): void
    {
        $this->authenticateAsAdherent($this->client, 'responsable-communal@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/comites/en-marche-allemagne');

        $this->assertStringContainsString('Élection du binôme paritaire d’Animateurs locaux', trim($crawler->filter('.instance__elections-box')->text()));

        $crawler = $this->client->click($crawler->selectLink('Je candidate en binôme')->link());

        $form = $crawler->selectButton('Enregistrer et choisir mon binôme')->form();

        $crawler = $this->client->submit($form);

        self::assertCount(3, $errors = $crawler->filter('.form__error'));

        self::assertSame('Photo est obligatoire', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(2)->text());

        $this->client->submit($form, [
            'committee_supervisor_candidacy[image][croppedImage]' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAH0CAYAAADL1t+',
            'committee_supervisor_candidacy[biography]' => 'ma bio',
            'committee_supervisor_candidacy[faithStatement]' => 'ma profession de foi',
        ]);

        $this->assertClientIsRedirectedTo('/comites/en-marche-allemagne/candidature/choix-de-binome', $this->client);

        // Choice of membership
        $crawler = $this->client->followRedirect();
        $form = $crawler->filter('form[name="candidacy_binome"]')->form();

        $crawler = $this->client->submit($form);

        self::assertCount(1, $errors = $crawler->filter('.form__error'));
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());

        /** @var EntityManager $entityManager */
        $adherentToInvite = $this->getAdherentRepository()->findOneByEmail('adherent-male-49@en-marche-dev.fr');

        $values = $form->getPhpValues();
        $values['candidacy_binome']['invitations'] = [[
            'membership' => $this->getCommitteeMembershipRepository()->findMembership(
                $adherentToInvite,
                $this->getCommittee(LoadCommitteeData::COMMITTEE_12_UUID)
            )->getUuid()->toString(),
        ]];

        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertClientIsRedirectedTo('/comites/en-marche-allemagne/candidature/choix-de-binome/fini', $this->client);
        $this->assertCountMails(1, VotingPlatformCandidacyInvitationCreatedMessage::class, 'adherent-male-49@en-marche-dev.fr');
    }
}
