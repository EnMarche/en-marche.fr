<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadCommitteeData;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use App\Mailchimp\Synchronisation\Command\RemoveAdherentFromStaticSegmentCommand;
use App\Mailer\Message\CommitteeNewFollowerMessage;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group committee
 */
class CommitteeControllerTest extends AbstractGroupControllerTest
{
    use MessengerTestTrait;

    private $committeeRepository;

    public function testBackButtonNotPresentWhenCommitteeIsPending(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');
        $this->client->request(Request::METHOD_GET, '/comites/en-marche-marseille-3/editer');

        $this->assertStringNotContainsString('Tous mes comités', $this->client->getResponse()->getContent());
    }

    public function testBackButtonPresentWhenCommitteeIsAccepted(): void
    {
        $this->authenticateAsAdherent($this->client, 'kiroule.p@blabla.tld');
        $this->client->request(Request::METHOD_GET, '/comites/en-marche-comite-de-new-york-city/editer');

        $this->assertStringContainsString('Tous mes comités', $this->client->getResponse()->getContent());
    }

    public function testRedirectionFromCommitteeShowToCommitteeEditWhenCommitteeIsPending(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');
        $this->client->request(Request::METHOD_GET, '/comites/en-marche-marseille-3');

        $this->client->followRedirect();

        $this->assertStringContainsString('/comites/en-marche-marseille-3/editer', $this->client->getRequest()->getUri());
    }

    public function testRedirectionComiteFromOldUrl()
    {
        $this->client->request(Request::METHOD_GET, '/comites/'.LoadCommitteeData::COMMITTEE_3_UUID.'/en-marche-dammarie-les-lys');

        $this->assertClientIsRedirectedTo('/comites/en-marche-dammarie-les-lys', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function testAnonymousUserIsNotAllowedToFollowCommittee()
    {
        $committeeUrl = '/comites/en-marche-dammarie-les-lys';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertTrue($this->seeRegisterLink($crawler));
    }

    public function testAuthenticatedCommitteeSupervisorCannotUnfollowCommittee()
    {
        // Login as supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');

        $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());

        self::assertStringNotContainsString('Quitter ce comité', $response->getContent());
    }

    public function testAuthenticatedCommitteeHostCanUnfollowCommittee()
    {
        // Login as host
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('Mes comités')->link());
        $this->assertEquals('http://test.enmarche.code/parametres/mes-activites#committees', $crawler->getUri());

        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $unfollowButton = $crawler->filter('.committee-unfollow');

        // Button should be enabled for there is a supervisor
        $this->assertNull($unfollowButton->attr('disabled'));

        // Unfollowing
        $committeeUrl = $this->client->getRequest()->getRequestUri();
        $this->client->request(Request::METHOD_POST, $committeeUrl.'/quitter', [
            'token' => $unfollowButton->attr('data-csrf-token'),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertMessageIsDispatched(RemoveAdherentFromStaticSegmentCommand::class);

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        // Ex-host should be allow to follow again
        $this->assertTrue($this->seeFollowLink($crawler));

        $this->logout($this->client);
        $this->getEntityManager(Adherent::class)->clear();

        // Login again as supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        // Unfollow link must not exist because there is no other host
        $this->assertSame(0, $crawler->filter('.committee-unfollow')->count());
        // Other follower/register links must not exist
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
    }

    public function testAuthenticatedAdherentCanFollowCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        // Browse to the committee details page
        $committeeUrl = '/comites/en-marche-dammarie-les-lys';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('3 adhérents', $crawler->filter('.committee__infos')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));

        // Emulate POST request to follow the committee.
        $token = $crawler->selectButton('Suivre ce comité')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $committeeUrl.'/rejoindre', ['token' => $token]);

        // Email sent to the host
        $this->assertCountMails(1, CommitteeNewFollowerMessage::class, 'francis.brioul@yahoo.com');
        $this->assertMessageIsDispatched(AddAdherentToStaticSegmentCommand::class);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('4 adhérents', $crawler->filter('.committee__infos')->text());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertTrue($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));

        // Emulate POST request to unfollow the committee.
        $token = $crawler->selectButton('Quitter ce comité')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $committeeUrl.'/quitter', ['token' => $token]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('3 adhérents', $crawler->filter('.committee__infos')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
    }

    public function testApprovedCommitteePageIsViewableByAnyone()
    {
        $committeeUrl = '/comites/en-marche-dammarie-les-lys';

        // Anonymous
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->seeMessageForContactHosts($crawler);
        $this->assertSeeHosts($crawler, [
            ['FB', 'Francis B.', 'Animateur'],
            ['GB', 'Gisele B.', 'Animatrice provisoire'],
            ['JF', 'Jean-Baptiste F.', 'Animateur provisoire'],
            ['JP', 'Jacques P.', 'Co-animateur'],
        ], false);
        $this->assertSeeDesignedAdherents($crawler, [
            ['LO', 'Lucie O.', 'Adhérente désignée'],
            ['JP', 'Jacques P.', 'Adhérent désigné'],
        ]);
        $this->assertCountTimelineMessages($crawler, 2);
        $this->assertSeeTimelineMessages($crawler, [
            ['Jacques P.', 'co-animateur', 'Connectez-vous'],
            ['Jacques P.', 'co-animateur', 'Connectez-vous'],
        ]);

        // Adherent
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCountTimelineMessages($crawler, 2);
        $this->assertSeeTimelineMessages($crawler, [
            ['Jacques Picard', 'co-animateur', 'À la recherche de volontaires !'],
            ['Jacques Picard', 'co-animateur', 'Lancement du comité !'],
        ]);

        $this->logout($this->client);

        // Member
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCountTimelineMessages($crawler, 2);
        $this->assertSeeTimelineMessages($crawler, [
            ['Jacques Picard', 'co-animateur', 'À la recherche de volontaires !'],
            ['Jacques Picard', 'co-animateur', 'Lancement du comité !'],
        ]);
    }

    public function testAnyoneCanSeeSupervisorProvisionalOnCommitteePage()
    {
        $committeeUrl = '/comites/en-marche-comite-de-evry';

        // Anonymous
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->seeMessageForContactHosts($crawler);
        $this->assertSeeHosts($crawler, [
            ['FB', 'Francis B.', 'Animateur'],
            ['GB', 'Gisele B.', 'Animatrice provisoire'],
        ], false);
    }

    public function testUnapprovedCommitteeIsViewableByItsCreator()
    {
        $committeeUrl = '/comites/en-marche-marseille-3';

        // Adherent
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->logout($this->client);
    }

    public function testAnonymousGuestCanShowCommitteePage()
    {
        $committeeUrl = '/comites/en-marche-paris-8';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeRegisterLink($crawler), 'The guest should not see the "register link"');
        $this->assertTrue($this->seeLoginLink($crawler), 'The guest should see the "login link"');
        $this->assertFalse($this->seeFollowLink($crawler), 'The guest should not see the "follow link"');
        $this->assertFalse($this->seeUnfollowLink($crawler), 'The guest should not see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The guest should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The guest should see the hosts');
        $this->assertFalse($this->seeHostNav($crawler), 'The guest should not see the host navigation');
        $this->assertSeeSocialLinks($crawler, $this->committeeRepository->findOneByUuid(LoadCommitteeData::COMMITTEE_1_UUID));
        $this->assertFalse($this->seeMessageForm($crawler));
        $this->seeMessageForContactHosts($crawler);
    }

    public function testAuthenticatedAdherentCanShowCommitteePage()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $committeeUrl = '/comites/en-marche-paris-8';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSeeHosts($crawler, [
            ['JP', 'Jacques Picard', 'Animateur'],
            ['GB', 'Gisele Berthoux', 'Co-animatrice'],
        ]);
        $this->assertSeeDesignedAdherents($crawler, [
            ['GB', 'Gisele Berthoux', 'Adhérente désignée'],
            ['CM', 'Carl Mirabeau', 'Adhérent désigné'],
        ]);

        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The adherent should not see the "register link"');
        $this->assertFalse($this->seeLoginLink($crawler), 'The adherent should not see the "login link"');
        $this->assertTrue($this->seeFollowLink($crawler), 'The adherent should see the "follow link"');
        $this->assertFalse($this->seeUnfollowLink($crawler), 'The adherent should not see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The adherent should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The adherent should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 2), 'The adherent should see the hosts contact link');
        $this->assertFalse($this->seeHostNav($crawler), 'The adherent should not see the host navigation');
        $this->assertFalse($this->seeMessageForm($crawler));
    }

    public function testAuthenticatedCommitteeFollowerCanShowCommitteePage()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The follower should not see the "register link"');
        $this->assertFalse($this->seeLoginLink($crawler), 'The adherent should not see the "login link"');
        $this->assertFalse($this->seeFollowLink($crawler), 'The follower should not see the "follow link"');
        $this->assertTrue($this->seeUnfollowLink($crawler), 'The follower should see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The follower should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The follower should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 2), 'The follower should see the hosts contact link');
        $this->assertFalse($this->seeHostNav($crawler), 'The follower should not see the host navigation');
        $this->assertFalse($this->seeMessageForm($crawler));
    }

    public function testAuthenticatedCommitteeHostCanShowCommitteePage()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The host should not see the "register link"');
        $this->assertFalse($this->seeLoginLink($crawler), 'The adherent should not see the "login link"');
        $this->assertFalse($this->seeFollowLink($crawler), 'The host should not see the "follow link"');
        $this->assertTrue($this->seeUnfollowLink($crawler), 'The host should see the "unfollow link" because there is another host');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The host should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The host should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 1), 'The host should see the other contact links');
        $this->assertTrue($this->seeSelfHostContactLink($crawler, 'Gisele Berthoux', 'Co-animatrice'), 'The host should see his own contact link');
        $this->assertTrue($this->seeHostNav($crawler), 'The host should see the host navigation');
    }

    public function testNoEditLinkWithAnonymousUser()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertEditDeleteButton($crawler, 0);
    }

    public function testDisplayEditLinkWithAnimateurUser()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertEditDeleteButton($crawler, 10);
    }

    public function testDisplayEditLinkWithNormaleUser()
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertEditDeleteButton($crawler, 5);
    }

    public function testEditMessage()
    {
        $committee = $this->manager->getRepository(Committee::class)->findOneBy(['slug' => 'en-marche-paris-8']);
        $messages = $this->manager->getRepository(CommitteeFeedItem::class)->findMostRecentFeedEvent($committee->getUuid());
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8/timeline/'.$messages->getId().'/modifier');
        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('committee_feed_item_message_send')->form();
        $this->assertSame($messages->getContent(), $form->get('committee_feed_item_message[content]')->getValue());

        $form->setValues(['committee_feed_item_message[content]' => $messages->getContent().' test']);
        $this->client->submit($form);
        $this->assertClientIsRedirectedTo('/comites/en-marche-paris-8', $this->client);

        $this->client->followRedirect();
        self::assertStringContainsString($messages->getContent().' test', $this->client->getResponse()->getContent());
    }

    public function testDeleteMessage()
    {
        $committee = $this->manager->getRepository(Committee::class)->findOneBy(['slug' => 'en-marche-paris-8']);
        $messages = $this->manager->getRepository(CommitteeFeedItem::class)->findMostRecentFeedEvent($committee->getUuid());
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8');
        $form = $crawler->selectButton('delete_entity_delete')->form();
        $this->client->submit($form);
        $this->assertClientIsRedirectedTo('/comites/en-marche-paris-8', $this->client);

        $this->client->followRedirect();
        self::assertStringNotContainsString($messages->getContent(), $this->client->getResponse()->getContent());
    }

    public function testDeleteEditDenied()
    {
        $committee = $this->manager->getRepository(Committee::class)->findOneBy(['slug' => 'en-marche-paris-8']);
        $messages = $this->manager->getRepository(CommitteeFeedItem::class)->findMostRecentFeedEvent($committee->getUuid());

        $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8/timeline/'.$messages->getId().'/modifier');
        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8/timeline/'.$messages->getId().'/supprimer');
        $this->assertClientIsRedirectedTo('/comites', $this->client, false, true);

        $this->client->request(Request::METHOD_DELETE, '/comites/en-marche-paris-8/timeline/'.$messages->getId().'/supprimer');
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testGetTimeLineConnected()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request('GET', '/comites/en-marche-paris-8/timeline?offset=10');

        $this->assertEditDeleteButton($crawler, 10);
    }

    public function testGetTimeLineNotConnected()
    {
        $crawler = $this->client->request('GET', '/comites/en-marche-paris-8/timeline?offset=10');

        $this->assertEditDeleteButton($crawler, 0);
    }

    private function seeLoginLink(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('#committee-login-link'));
    }

    private function seeRegisterLink(Crawler $crawler, $nb = 1): bool
    {
        $this->assertCount($nb, $crawler->filter('.committee-follow--disabled'));

        return 1 === \count($crawler->filter('#committee-register-link'));
    }

    private function seeFollowLink(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('.committee-follow'));
    }

    private function seeUnfollowLink(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('.committee-unfollow'));
    }

    private function seeMembersCount(Crawler $crawler, string $membersCount): bool
    {
        return false !== strpos(
            $crawler->filter('.committee__infos')->text(),
            $membersCount.' adhérent'.($membersCount > 1 ? 's' : '')
        );
    }

    private function seeHosts(Crawler $crawler, int $hostsCount): bool
    {
        return $hostsCount === \count($crawler->filter('.committee__card .committee-host'));
    }

    private function assertSeeHosts(Crawler $crawler, array $hosts, bool $isConnected = true): void
    {
        $this->assertCount(\count($hosts), $nodes = $crawler->filter('.committee-host'));
        $contact = $isConnected ? '\s+(Contacter)' : '';

        foreach ($hosts as $position => $host) {
            [$initials, $name, $role] = $host;
            $this->assertMatchesRegularExpression(
                '/^'.preg_quote($initials).'\s+'.preg_quote($name).'\s+'.$role.$contact.'?$/',
                trim($nodes->eq($position)->text())
            );
        }
    }

    private function assertSeeDesignedAdherents(Crawler $crawler, array $adherents): void
    {
        $this->assertCount(\count($adherents), $nodes = $crawler->filter('.committee-designed-adherents'));

        foreach ($adherents as $position => $adherent) {
            [$initials, $name, $role] = $adherent;
            $this->assertMatchesRegularExpression(
                '/^'.preg_quote($initials).'\s+'.preg_quote($name).'\s+'.$role.'?$/',
                trim($nodes->eq($position)->text())
            );
        }
    }

    private function seeHostsContactLink(Crawler $crawler, int $hostsCount): bool
    {
        return $hostsCount === \count($crawler->filter('.committee__card .committee-host a'));
    }

    private function seeMessageForContactHosts(Crawler $crawler): void
    {
        $this->assertStringContainsString(
            'Connectez-vous pour pouvoir contacter les responsables de comité.',
            $crawler->filter('.committee__card > .text--summary')->text()
        );
    }

    private function seeSelfHostContactLink(Crawler $crawler, string $name, string $role): bool
    {
        /** @var \DOMElement $host */
        foreach ($crawler->filter('.committee__card .committee-host') as $host) {
            if (false !== strpos($host->textContent, 'Contacter')) {
                continue;
            }

            return preg_match('/'.preg_quote($name).'\s+'.$role.'\s+\(vous\)/', $host->textContent);
        }

        return false;
    }

    private function seeHostNav(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('#committee-host-nav'));
    }

    private function seeMessageForm(Crawler $crawler, array $errorMessages = []): bool
    {
        if ($errorMessages) {
            $errors = $crawler->filter('form[name="committee_feed_message"] .form__error');

            $this->assertCount(\count($errorMessages), $errors);

            foreach ($errorMessages as $i => $errorMessage) {
                $this->assertSame($errorMessage, trim($errors->eq($i)->text()));
            }
        } else {
            $this->assertCount(0, $crawler->filter('form[name="committee_feed_message"] .form__errors'));
        }

        return 1 === \count($crawler->filter('form[name="committee_feed_message"]'));
    }

    private function assertCountTimelineMessages(Crawler $crawler, int $nb, string $message = '')
    {
        self::assertCount($nb, $crawler->filter('.committee__timeline__message'), $message);
    }

    private function assertSeeTimelineMessages(Crawler $crawler, array $messages)
    {
        foreach ($messages as $position => $message) {
            [$author, $role, $text] = $message;
            $this->assertSeeCommitteeTimelineMessage($crawler, $position, $author, $role, $text);
        }
    }

    private function assertSeeSocialLinks(Crawler $crawler, Committee $committee)
    {
        $facebookLinkPattern = 'a.committee__social--facebook';
        $twitterLinkPattern = 'a.committee__social--twitter';

        if ($facebookUrl = $committee->getFacebookPageUrl()) {
            $this->assertCount(1, $facebookLink = $crawler->filter($facebookLinkPattern));
            $this->assertSame($facebookUrl, $facebookLink->attr('href'));
        } else {
            $this->assertCount(0, $crawler->filter($facebookLinkPattern));
        }

        if ($twitterNickname = $committee->getTwitterNickname()) {
            $this->assertCount(1, $twitterLink = $crawler->filter($twitterLinkPattern));
            $this->assertSame(sprintf('https://twitter.com/%s', $twitterNickname), $twitterLink->attr('href'));
        } else {
            $this->assertCount(0, $crawler->filter($twitterLinkPattern));
        }
    }

    public function assertRedictIfCommitteeNotExist()
    {
        $this->client->request(Request::METHOD_GET, '/comites/ariege-leze');

        $this->assertStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client);

        $this->assertClientIsRedirectedTo('/comites', $this->client);
        $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->committeeRepository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->committeeRepository = null;

        parent::tearDown();
    }

    protected function getGroupUrl(): string
    {
        return '/comites/en-marche-dammarie-les-lys';
    }

    private function assertEditDeleteButton(Crawler $crawler, int $nbExpected)
    {
        $result = $crawler->selectLink('Modifier le message');
        $this->assertSame($nbExpected, $result->count());

        $result = $crawler->selectButton('delete_entity[delete]');
        $this->assertSame($nbExpected, $result->count());
    }
}
