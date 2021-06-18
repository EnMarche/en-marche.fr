<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\Email;
use App\Entity\TonMacronChoice;
use App\Entity\TonMacronFriendInvitation;
use App\Repository\EmailRepository;
use App\Repository\TonMacronChoiceRepository;
use App\Repository\TonMacronFriendInvitationRepository;
use App\TonMacron\InvitationProcessor;
use App\TonMacron\InvitationProcessorHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class TonMacronControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public const INVITATION_PATH = '/pourquoivoterenmarche';
    public const INVITATION_RESTART_PATH = '/pourquoivoterenmarche/recommencer';
    public const INVITATION_SENT_PATH = '/pourquoivoterenmarche/%s/merci';

    /* @var TonMacronChoiceRepository */
    private $tonMacronChoiceRepository;

    /* @var TonMacronFriendInvitationRepository */
    private $tonMacronInvitationRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testInviteAction()
    {
        $this->assertCount(0, $this->emailRepository->findAll());

        $invitation = new InvitationProcessor();

        $crawler = $this->client->request(Request::METHOD_GET, self::INVITATION_PATH);

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_info]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendFirstName]' => $invitation->friendFirstName = 'Béatrice',
            'ton_macron_invitation[friendAge]' => '32',
            'ton_macron_invitation[friendGender]' => $invitation->friendGender = 'female',
            'ton_macron_invitation[friendPosition]' => 4,
        ]));

        $invitation->friendAge = 32;
        $invitation->friendPosition = $this->getChoice(4);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_FRIEND_PROJECT;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_project]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendProject]' => '19',
        ]));

        $invitation->friendProject = $this->getChoice(19);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_FRIEND_INTERESTS;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('ton_macron_invitation[fill_interests]')->form([
            'ton_macron_invitation[friendInterests]' => [
                0 => 29,
                18 => 47,
            ],
        ]));

        $invitation->friendInterests = $this->getChoices([29, 47]);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_SELF_REASONS;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_reasons]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[selfReasons]' => [1 => '62', 2 => '63'],
        ]));

        $invitation->selfReasons = $this->getChoices([62, 63], true);
        $invitation->marking = InvitationProcessor::STATE_SUMMARY;

        $currentInvitation = $this->getCurrentInvitation();

        $this->assertEquals($invitation->selfReasons, $currentInvitation->selfReasons);
        $this->assertNotEquals($invitation->messageContent, $currentInvitation->messageContent);
        foreach ($invitation->getArguments() as $choice) {
            $this->assertStringContainsString($choice, $currentInvitation->messageContent);
        }
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[send]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[messageSubject]' => $currentInvitation->messageSubject = 'Toujours envie de voter blanc ?',
            'ton_macron_invitation[selfFirstName]' => $currentInvitation->selfFirstName = 'Marie',
            'ton_macron_invitation[selfLastName]' => $currentInvitation->selfLastName = 'Dupont',
            'ton_macron_invitation[selfEmail]' => $currentInvitation->selfEmail = 'marie.dupont@example.org',
            'ton_macron_invitation[friendEmail]' => $currentInvitation->friendEmail = 'beatrice@example.org',
        ]));

        $this->assertNull($this->client->getRequest()->getSession()->get(InvitationProcessorHandler::SESSION_KEY));
        $this->assertCount(1, $mails = $this->emailRepository->findAll());

        /** @var Email $mail */
        $mail = $mails[0];

        $this->assertSame('TonMacronFriendMessage', $mail->getMessageClass());
        $this->assertStringContainsString('beatrice@example.org', $mail->getRecipientsAsString());
        $this->assertCount(1, $invitations = $this->tonMacronInvitationRepository->findAll());

        /** @var TonMacronFriendInvitation $invitationLog */
        $invitationLog = $invitations[0];

        $this->assertSame($currentInvitation->friendFirstName, $invitationLog->getFriendFirstName());
        $this->assertSame($currentInvitation->friendAge, $invitationLog->getFriendAge());
        $this->assertSame($currentInvitation->friendGender, $invitationLog->getFriendGender());
        $this->assertSame($currentInvitation->friendPosition->getContentKey(), $invitationLog->getFriendPosition());
        $this->assertSame($currentInvitation->friendEmail, $invitationLog->getFriendEmailAddress());
        $this->assertSame($currentInvitation->selfFirstName, $invitationLog->getAuthorFirstName());
        $this->assertSame($currentInvitation->selfLastName, $invitationLog->getAuthorLastName());
        $this->assertSame($currentInvitation->selfEmail, $invitationLog->getAuthorEmailAddress());
        $this->assertSame($currentInvitation->messageSubject, $invitationLog->getMailSubject());
        $this->assertSame(trim($currentInvitation->messageContent), $invitationLog->getMailBody());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(sprintf(self::INVITATION_SENT_PATH, $invitationLog->getUuid()->toString()), $this->client);
    }

    public function testRestartAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::INVITATION_PATH);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendFirstName]' => 'Béatrice',
            'ton_macron_invitation[friendAge]' => '32',
            'ton_macron_invitation[friendGender]' => 'female',
            'ton_macron_invitation[friendPosition]' => '4',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $this->client->followRedirect();
        $this->client->request(Request::METHOD_GET, self::INVITATION_RESTART_PATH);

        $this->assertNull($this->client->getRequest()->getSession()->get(InvitationProcessorHandler::SESSION_KEY));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->tonMacronChoiceRepository = $this->getTonMacronChoiceRepository();
        $this->tonMacronInvitationRepository = $this->getTonMacronInvitationRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->tonMacronInvitationRepository = null;
        $this->tonMacronChoiceRepository = null;

        parent::tearDown();
    }

    private function getTonMacronInvitationHandler(): InvitationProcessorHandler
    {
        return $this->get(InvitationProcessorHandler::class);
    }

    private function getCurrentInvitation(): InvitationProcessor
    {
        return $this->getTonMacronInvitationHandler()->start($this->client->getRequest()->getSession());
    }

    private function getChoice(int $id): ?TonMacronChoice
    {
        return $this->tonMacronChoiceRepository->find($id);
    }

    /**
     * @param int[] $ids
     *
     * @return TonMacronChoice[]|ArrayCollection|array
     */
    private function getChoices(array $ids, bool $asCollection = false): iterable
    {
        $choices = $this->tonMacronChoiceRepository->findBy(['id' => $ids]);

        return $asCollection ? new ArrayCollection($choices) : $choices;
    }
}
