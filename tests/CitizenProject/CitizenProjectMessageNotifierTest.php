<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectCommentEvent;
use AppBundle\CitizenProject\CitizenProjectFollowerAddedEvent;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\CitizenProject\CitizenProjectWasApprovedEvent;
use AppBundle\CitizenProject\CitizenProjectWasCreatedEvent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Committee\CommitteeManager;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;

/**
 * @group citizenProject
 */
class CitizenProjectMessageNotifierTest extends TestCase
{
    private $adherentRepository;

    public function testOnCitizenProjectApprove()
    {
        $mailPost = $this->createMock(MailPostInterface::class);
        $citizenProjectWasApprovedEvent = $this->createMock(CitizenProjectWasApprovedEvent::class);
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $citizenProject->expects($this->any())->method('getCreator')->willReturn($this->createMock(Adherent::class));
        $citizenProject->expects($this->once())->method('getPendingCommitteeSupports')->willReturn(new ArrayCollection());

        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);
        $citizenProjectWasApprovedEvent->expects($this->any())->method('getCitizenProject')->willReturn($citizenProject);
        $mailPost->expects($this->once())->method('address');
        $manager = $this->createManager($administrator);

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailPost, $committeeManager, $router);
        $citizenProjectMessageNotifier->onCitizenProjectApprove($citizenProjectWasApprovedEvent);
    }

    public function testOnCitizenProjectCreation()
    {
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);
        $manager = $this->createManager($administrator);

        $mailPost = $this->createMock(MailPostInterface::class);
        $citizenProjectWasCreatedEvent = $this->createMock(CitizenProjectWasCreatedEvent::class);
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);
        $coordinator = $this->createMock(Adherent::class);

        $citizenProjectWasCreatedEvent->expects($this->once())
            ->method('getCitizenProject')
            ->willReturn($citizenProject)
        ;

        $citizenProjectWasCreatedEvent->expects($this->once())
            ->method('getCreator')
            ->willReturn($administrator)
        ;

        $router->expects($this->exactly(2))->method('generate')->willReturn('http://foobar.io');
        $mailPost->expects($this->exactly(2))->method('address');

        $this->adherentRepository->expects($this->once())
            ->method('findCoordinatorsByCitizenProject')
            ->willReturn(new AdherentCollection([$coordinator]))
        ;

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier(
            $this->adherentRepository,
            $manager,
            $mailPost,
            $committeeManager,
            $router
        );

        $citizenProjectMessageNotifier->onCitizenProjectCreation($citizenProjectWasCreatedEvent);
    }

    public function testSendAdherentNotificationCreation()
    {
        $mailPost = $this->createMock(MailPostInterface::class);
        $manager = $this->createManager();
        $adherent = $this->createMock(Adherent::class);
        $creator = $this->createMock(Adherent::class);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $mailPost->expects($this->once())->method('address');

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailPost, $committeeManager, $router);
        $citizenProjectMessageNotifier->sendAdherentNotificationCreation([$adherent], $citizenProject, $creator);
    }

    public function testSendAdminitratorNotificationWhenFollowerAdded()
    {
        $mailPost = $this->createMock(MailPostInterface::class);
        $manager = $this->createManager();
        $adherent = $this->createMock(Adherent::class);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);
        $administrator = $this->createAdministrator(LoadAdherentData::COMMITTEE_1_UUID);

        $manager->expects($this->once())->method('getCitizenProjectAdministrators')->willReturn(new AdherentCollection([$administrator]));
        $mailPost->expects($this->once())->method('address');

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailPost, $committeeManager, $router);
        $followerAddedEvent = new CitizenProjectFollowerAddedEvent($citizenProject, $adherent);
        $citizenProjectMessageNotifier->onCitizenProjectFollowerAdded($followerAddedEvent);
    }

    public function testSendAdminitratorNotificationWhenFollowerAddedWithAdministratorsInCitizenProject()
    {
        $mailPost = $this->createMock(MailPostInterface::class);
        $manager = $this->createManager();
        $adherent = $this->createMock(Adherent::class);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $manager->expects($this->once())->method('getCitizenProjectAdministrators')->willReturn(new AdherentCollection());
        $mailPost->expects($this->never())->method('address');

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailPost, $committeeManager, $router);
        $followerAddedEvent = new CitizenProjectFollowerAddedEvent($citizenProject, $adherent);
        $citizenProjectMessageNotifier->onCitizenProjectFollowerAdded($followerAddedEvent);
    }

    public function testSendFollowerNotificationWhenAdministratorAddCommentToCitizenProject()
    {
        $mailPost = $this->createMock(MailPostInterface::class);
        $member = $this->createAdministrator(LoadAdherentData::ADHERENT_2_UUID);
        $comment = $this->createComment();
        $manager = $this->createManager(null, $member);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $manager
            ->expects($this->once())
            ->method('getCitizenProjectMembers')
            ->willReturn(new AdherentCollection())
        ;
        $mailPost
            ->expects($this->once())
            ->method('address')
        ;

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailPost, $committeeManager, $router);
        $commentCreatedEvent = new CitizenProjectCommentEvent($citizenProject, $comment, true);
        $citizenProjectMessageNotifier->sendCommentCreatedEmail($commentCreatedEvent);
    }

    private function createCitizenProject(string $uuid, string $cityName): CitizenProject
    {
        $citizenProjectUuid = Uuid::fromString($uuid);

        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject
            ->expects($this->any())
            ->method('getUuid')
            ->willReturn($citizenProjectUuid)
        ;
        $citizenProject
            ->expects($this->any())
            ->method('getCityName')
            ->willReturn($cityName)
        ;

        return $citizenProject;
    }

    private function createAdministrator(string $uuid): Adherent
    {
        $administratorUuid = Uuid::fromString($uuid);

        $administrator = $this->createMock(Adherent::class);
        $administrator->expects($this->any())->method('getUuid')->willReturn($administratorUuid);

        return $administrator;
    }

    private function createAuthor(): Adherent
    {
        $administrator = $this->createMock(Adherent::class);
        $administrator
            ->expects($this->any())
            ->method('getFirstName')
            ->willReturn('Pierre')
        ;

        return $administrator;
    }

    private function createComment(): CitizenProjectComment
    {
        $administrator = $this->createMock(CitizenProjectComment::class);
        $administrator
            ->expects($this->any())
            ->method('getContent')
            ->willReturn('Mon message')
        ;
        $administrator
            ->expects($this->any())
            ->method('getAuthor')
            ->willReturn($this->createAuthor())
        ;

        return $administrator;
    }

    private function createManager(?Adherent $administrator = null, ?Adherent $member = null): CitizenProjectManager
    {
        $manager = $this->createMock(CitizenProjectManager::class);

        if ($administrator) {
            $manager->expects($this->any())->method('getCitizenProjectCreator')->willReturn($administrator);
        }
        if ($member) {
            $membres = new AdherentCollection();
            $membres->add($member);
            $manager
                ->expects($this->any())
                ->method('getCitizenProjectMembers')
                ->willReturn($membres)
            ;
        }

        return $manager;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->adherentRepository = $this->createMock(AdherentRepository::class);
    }

    protected function tearDown()
    {
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
