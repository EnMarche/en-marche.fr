<?php

namespace Tests\App\Committee\Feed;

use App\Committee\Feed\CommitteeFeedManager;
use App\Committee\Feed\CommitteeMessage;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Entity\CommitteeFeedItem;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group committee
 */
class CommitteeFeedManagerTest extends AbstractKernelTestCase
{
    /* @var CommitteeFeedManager */
    private $committeeFeedManager;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var AdherentRepository */
    private $adherentRepository;

    public function testCreateMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadCommitteeData::COMMITTEE_1_UUID);
        $author = $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID))->first();

        $messageContent = 'Bienvenue !';
        $message = $this->committeeFeedManager->createMessage(new CommitteeMessage($author, $committee, 'Foo subject', $messageContent));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());
    }

    public function testCreateNoNotificationMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadCommitteeData::COMMITTEE_1_UUID);
        $author = $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID))->first();

        $messageContent = 'Bienvenue !';
        $message = $this->committeeFeedManager->createMessage(new CommitteeMessage($author, $committee, 'Foo subject', $messageContent, true, 'now', false));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeFeedManager = $this->get(CommitteeFeedManager::class);
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown(): void
    {
        $this->committeeFeedManager = null;
        $this->committeeRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
