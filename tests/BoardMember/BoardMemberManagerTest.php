<?php

namespace Tests\App\BoardMember;

use App\BoardMember\BoardMemberFilter;
use App\BoardMember\BoardMemberManager;
use App\Collection\AdherentCollection;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\BoardMember\Role;
use App\Repository\AdherentRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group boardMember
 */
class BoardMemberManagerTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var BoardMemberManager
     */
    private $boardMemberManager;

    public function testSearchMembers()
    {
        $filter = BoardMemberFilter::createFromArray([]);
        $excludedMember = $this->adherentRepository->findOneByEmail('kiroule.p@blabla.tld');

        $members = $this->boardMemberManager->paginateMembers($filter, $excludedMember);

        $this->assertCount(8, $members);
        $this->assertContainsOnlyInstancesOf(Adherent::class, $members);
        $this->assertNotContains($excludedMember, $members);
    }

    public function testPaginateMembers()
    {
        $filter = BoardMemberFilter::createFromArray([]);
        $excludedMember = $this->adherentRepository->findOneByEmail('kiroule.p@blabla.tld');

        $paginator = $this->boardMemberManager->paginateMembers($filter, $excludedMember);

        $this->assertInstanceOf(Paginator::class, $paginator);
        $this->assertCount(8, $paginator);
        $this->assertContainsOnlyInstancesOf(Adherent::class, $paginator);
        $this->assertNotContains($excludedMember, $paginator);
    }

    public function testFindSavedMembers()
    {
        $adherent = $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_12_UUID);

        $savedMembers = $this->boardMemberManager->findSavedMembers($adherent);

        $this->assertInstanceOf(AdherentCollection::class, $savedMembers);
        $this->assertCount(4, $savedMembers);
        $this->assertContainsOnlyInstancesOf(Adherent::class, $savedMembers);

        $expectedSavedMembers = [
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_2_UUID),
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_9_UUID),
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_10_UUID),
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_11_UUID),
        ];

        foreach ($expectedSavedMembers as $expectedSavedMember) {
            $this->assertContains($expectedSavedMember, $savedMembers);
        }
    }

    public function testFindRoles()
    {
        $roles = $this->boardMemberManager->findRoles();

        $this->assertCount(15, $roles);
        $this->assertContainsOnlyInstancesOf(Role::class, $roles);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->boardMemberManager = $this->get(BoardMemberManager::class);
    }

    protected function tearDown(): void
    {
        $this->boardMemberManager = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
