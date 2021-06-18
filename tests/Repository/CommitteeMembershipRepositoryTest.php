<?php

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Entity\CommitteeMembership;
use App\Repository\CommitteeMembershipRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class CommitteeMembershipRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var CommitteeMembershipRepository
     */
    private $repository;

    public function testFindCommitteeMembersMemberships()
    {
        $this->assertNull($this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getCommitteeMembershipRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}
