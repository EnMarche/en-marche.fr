<?php

namespace Tests\App\UserListDefinition;

use App\ElectedRepresentative\UserListDefinitionHistoryManager;
use App\Entity\Committee;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use App\Exception\UserListDefinitionException;
use App\Exception\UserListDefinitionMemberException;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Repository\UserListDefinitionRepository;
use App\UserListDefinition\UserListDefinitionManager;
use App\UserListDefinition\UserListDefinitionPermissions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Tests\App\AbstractKernelTestCase;

class UserListDefinitionManagerTest extends AbstractKernelTestCase
{
    public const TYPES = [
        [
            'id' => 2,
            'type' => 'elected_representative',
            'code' => 'instances_member',
            'label' => 'Participe aux instances',
        ],
        [
            'id' => 1,
            'type' => 'elected_representative',
            'code' => 'supporting_la_rem',
            'label' => 'Sympathisant(e) LaREM',
        ],
    ];

    /* @var UserListDefinitionManager */
    private $userListDefinitionManager;

    public function testThrowExceptionWhenGettingUserListDefinitionMembersForUnknownType()
    {
        $this->expectException(UserListDefinitionException::class);
        $this->expectExceptionMessage("Type 'unknown_type' is not supported.");

        $this->userListDefinitionManager->getUserListDefinitionMembers('unknown_type', [1, 2], ElectedRepresentative::class);
    }

    public function testThrowExceptionWhenGettingUserListDefinitionMembersWithUnsupportedClass()
    {
        $this->expectException(UserListDefinitionException::class);
        $this->expectExceptionMessage('Class App\Entity\Committee does not use a trait EntityUserListDefinitionTrait');

        $this->userListDefinitionManager->getUserListDefinitionMembers(UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE, [1, 2], Committee::class);
    }

    public function testGetUserListDefinitionMembers()
    {
        $members = [
            [
                'code' => 'instances_member',
                'ids' => '99',
            ],
            [
                'code' => 'supporting_la_rem',
                'ids' => '99,100',
            ],
        ];
        $expectedMembers = self::TYPES;
        $expectedMembers[0]['ids'] = ['99'];
        $expectedMembers[1]['ids'] = ['99', '100'];
        $userListDefinitionRepository = $this->createMock(UserListDefinitionRepository::class);
        $userListDefinitionRepository
            ->expects($this->once())
            ->method('getForType')
            ->with(UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
            ->willReturn(self::TYPES)
        ;
        $userListDefinitionRepository
            ->expects($this->once())
            ->method('getMemberIdsForType')
            ->with(UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE, [99, 100], ElectedRepresentative::class)
            ->willReturn($members)
        ;

        $userListDefinitionManager = new UserListDefinitionManager(
            $this->createMock(EntityManagerInterface::class),
            $this->createBus(),
            $userListDefinitionRepository,
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(UserListDefinitionHistoryManager::class)
        );

        $gotMembers = $userListDefinitionManager->getUserListDefinitionMembers(
            UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
            [99, 100],
            ElectedRepresentative::class
        );
        $this->assertSame($expectedMembers, $gotMembers);
    }

    public function testThrowExceptionWhenUpdatingUserListDefinitionMembersWithUnsupportedClass()
    {
        $this->expectException(UserListDefinitionException::class);
        $this->expectExceptionMessage('Class App\Entity\Committee does not use a trait EntityUserListDefinitionTrait');

        $this->userListDefinitionManager->updateUserListDefinitionMembers(
            [],
            Committee::class
        );
    }

    public function testThrowExceptionWhenUpdatingUserListDefinitionMembersWithWrongId()
    {
        $userListDefinitions = [4 => ['member_of' => [111], 'not_member_of' => [2]]];
        $electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $electedRepresentativeRepository->expects($this->once())
            ->method('find')
            ->with(4)
            ->willReturn($this->createMock(ElectedRepresentative::class))
        ;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(ElectedRepresentative::class)
            ->willReturn($electedRepresentativeRepository)
        ;

        $userListDefinitionRepository = $this->createMock(UserListDefinitionRepository::class);
        $userListDefinitionRepository
            ->expects($this->once())
            ->method('find')
            ->with(111)
            ->willReturn(null)
        ;

        $userListDefinitionManager = new UserListDefinitionManager(
            $em,
            $this->createBus(),
            $userListDefinitionRepository,
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(UserListDefinitionHistoryManager::class)
        );

        $this->expectException(UserListDefinitionException::class);
        $this->expectExceptionMessage('UserListDefinition with id "111" has not been found');

        $userListDefinitionManager->updateUserListDefinitionMembers(
            $userListDefinitions,
            ElectedRepresentative::class
        );
    }

    public function testThrowExceptionWhenUpdatingUserListDefinitionMembersWithNoTypePermission()
    {
        $userListDefinitions = [
            1 => [
                'member_of' => [2],
                'not_member_of' => [],
            ],
        ];
        $electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $electedRepresentativeRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($this->createMock(ElectedRepresentative::class))
        ;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(ElectedRepresentative::class)
            ->willReturn($electedRepresentativeRepository)
        ;

        $userListDefinition = $this->createMock(UserListDefinition::class);
        $userListDefinition
            ->expects($this->any())
            ->method('getType')
            ->willReturn(UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
        ;

        $userListDefinitionRepository = $this->createMock(UserListDefinitionRepository::class);
        $userListDefinitionRepository
            ->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn($userListDefinition)
        ;
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with(UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE, UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
            ->willReturn(false)
        ;

        $userListDefinitionManager = new UserListDefinitionManager(
            $em,
            $this->createBus(),
            $userListDefinitionRepository,
            $authorizationChecker,
            $this->createMock(UserListDefinitionHistoryManager::class)
        );

        $this->expectException(UserListDefinitionException::class);
        $this->expectExceptionMessage('UserListDefinition type "elected_representative" cannot be managed by connected user');

        $userListDefinitionManager->updateUserListDefinitionMembers(
            $userListDefinitions,
            ElectedRepresentative::class
        );
    }

    public function testThrowExceptionWhenUpdatingUserListDefinitionMembersWithWrongMemberId()
    {
        $userListDefinitions = [
            1 => [
                'member_of' => [2],
                'not_member_of' => [],
            ],
        ];
        $electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $electedRepresentativeRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null)
        ;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(ElectedRepresentative::class)
            ->willReturn($electedRepresentativeRepository)
        ;

        $userListDefinition = $this->createMock(UserListDefinition::class);
        $userListDefinition
            ->expects($this->any())
            ->method('getType')
            ->willReturn(UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
        ;

        $userListDefinitionManager = new UserListDefinitionManager(
            $em,
            $this->createBus(),
            $this->createMock(UserListDefinitionRepository::class),
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(UserListDefinitionHistoryManager::class)
        );

        $this->expectException(UserListDefinitionMemberException::class);
        $this->expectExceptionMessage('App\Entity\ElectedRepresentative\ElectedRepresentative with id "1" has not been found');

        $userListDefinitionManager->updateUserListDefinitionMembers(
            $userListDefinitions,
            ElectedRepresentative::class
        );
    }

    public function testThrowExceptionWhenUpdatingUserListDefinitionMembersWithNoMemberPermission()
    {
        $userListDefinitions = [
            1 => [
                'member_of' => [2],
                'not_member_of' => [],
            ],
        ];
        $electedRepresentative = $this->createMock(ElectedRepresentative::class);
        $electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $electedRepresentativeRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($this->createMock(ElectedRepresentative::class))
        ;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(ElectedRepresentative::class)
            ->willReturn($electedRepresentativeRepository)
        ;

        $userListDefinition = $this->createMock(UserListDefinition::class);
        $userListDefinition
            ->expects($this->any())
            ->method('getType')
            ->willReturn(UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
        ;

        $userListDefinitionRepository = $this->createMock(UserListDefinitionRepository::class);
        $userListDefinitionRepository
            ->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn($userListDefinition)
        ;
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects($this->at(0))
            ->method('isGranted')
            ->with(UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE, UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
            ->willReturn(true)
        ;
        $authorizationChecker
            ->expects($this->at(1))
            ->method('isGranted')
            ->with(UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER, $electedRepresentative)
            ->willReturn(false)
        ;

        $userListDefinitionManager = new UserListDefinitionManager(
            $em,
            $this->createBus(),
            $userListDefinitionRepository,
            $authorizationChecker,
            $this->createMock(UserListDefinitionHistoryManager::class)
        );

        $this->expectException(UserListDefinitionMemberException::class);
        $this->expectExceptionMessageMatches('/Connected user cannot manage .+ member$/');

        $userListDefinitionManager->updateUserListDefinitionMembers(
            $userListDefinitions,
            ElectedRepresentative::class
        );
    }

    public function testUpdateUserListDefinitionMembers()
    {
        $userListDefinitions = [
            1 => [
                'member_of' => [10],
                'not_member_of' => [],
            ],
            2 => [
                'member_of' => [20],
                'not_member_of' => [10],
            ],
            3 => [
                'member_of' => [],
                'not_member_of' => [10],
            ],
        ];
        $userListDefinitionSLR = new UserListDefinition(
            UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
            'supporting_la_rem',
            'Sympathisant(e) LaREM'
        );
        $userListDefinitionIM = new UserListDefinition(
            UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
            'instances_member',
            'Participe aux instances'
        );
        $electedRepresentative1 = $this->createMock(ElectedRepresentative::class);
        $electedRepresentative1->expects($this->once())
            ->method('addUserListDefinition')
            ->with($userListDefinitionSLR)
        ;
        $electedRepresentative1->expects($this->once())
            ->method('getUserListDefinitions')
            ->willReturn(new ArrayCollection())
        ;
        $electedRepresentative2 = $this->createMock(ElectedRepresentative::class);
        $electedRepresentative2->expects($this->once())
            ->method('addUserListDefinition')
            ->with($userListDefinitionIM)
        ;
        $electedRepresentative2->expects($this->once())
            ->method('removeUserListDefinition')
            ->with($userListDefinitionSLR)
        ;
        $electedRepresentative2->expects($this->once())
            ->method('getUserListDefinitions')
            ->willReturn(new ArrayCollection())
        ;
        $electedRepresentative3 = $this->createMock(ElectedRepresentative::class);
        $electedRepresentative3->expects($this->once())
            ->method('removeUserListDefinition')
            ->with($userListDefinitionSLR)
        ;
        $electedRepresentative3->expects($this->once())
            ->method('getUserListDefinitions')
            ->willReturn(new ArrayCollection())
        ;

        $electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $electedRepresentativeRepository->expects($this->at(0))
            ->method('find')
            ->with(1)
            ->willReturn($electedRepresentative1)
        ;
        $electedRepresentativeRepository->expects($this->at(1))
            ->method('find')
            ->with(2)
            ->willReturn($electedRepresentative2)
        ;
        $electedRepresentativeRepository->expects($this->at(2))
            ->method('find')
            ->with(3)
            ->willReturn($electedRepresentative3)
        ;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(ElectedRepresentative::class)
            ->willReturn($electedRepresentativeRepository)
        ;
        $em->expects($this->once())
            ->method('flush')
        ;

        $userListDefinitionRepository = $this->createMock(UserListDefinitionRepository::class);
        $userListDefinitionRepository
            ->expects($this->at(0))
            ->method('find')
            ->with(10)
            ->willReturn($userListDefinitionSLR)
        ;
        $userListDefinitionRepository
            ->expects($this->at(1))
            ->method('find')
            ->with(20)
            ->willReturn($userListDefinitionIM)
        ;
        $userListDefinitionRepository
            ->expects($this->at(2))
            ->method('find')
            ->with(10)
            ->willReturn($userListDefinitionSLR)
        ;
        $userListDefinitionRepository
            ->expects($this->at(3))
            ->method('find')
            ->with(10)
            ->willReturn($userListDefinitionSLR)
        ;
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->willReturn(true)
        ;

        $userListDefinitionManager = new UserListDefinitionManager(
            $em,
            $this->createBus(),
            $userListDefinitionRepository,
            $authorizationChecker,
            $this->createMock(UserListDefinitionHistoryManager::class)
        );

        $userListDefinitionManager->updateUserListDefinitionMembers(
            $userListDefinitions,
            ElectedRepresentative::class
        );
    }

    private function createBus(): MessageBusInterface
    {
        return $this->createConfiguredMock(MessageBusInterface::class, [
            'dispatch' => new Envelope(new \stdClass()),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userListDefinitionManager = new UserListDefinitionManager(
            $this->createMock(EntityManagerInterface::class),
            $this->createBus(),
            $this->createMock(UserListDefinitionRepository::class),
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(UserListDefinitionHistoryManager::class)
        );
    }

    protected function tearDown(): void
    {
        $this->userListDefinitionManager = null;

        parent::tearDown();
    }
}
