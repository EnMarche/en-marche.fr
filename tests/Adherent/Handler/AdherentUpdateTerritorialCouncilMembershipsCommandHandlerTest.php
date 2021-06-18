<?php

namespace Tests\App\Adherent\Handler;

use App\Adherent\Handler\AdherentUpdateTerritorialCouncilMembershipsCommandHandler;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\PoliticalCommitteeQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\ElectedRepresentative\MandateRepository;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\TerritorialCouncil\Command\AdherentUpdateTerritorialCouncilMembershipsCommand;
use App\TerritorialCouncil\Handlers\TerritorialCouncilActiveMandateHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilBoroughCouncilorHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilCityCouncilorHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilCommitteeSupervisorHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilConsularCouncilorHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilCorsicaAssemblyMemberHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilDepartmentalCouncilPresidentHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilDepartmentCouncilorHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilDeputyHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilElectedCandidateAdherentHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilEmptyMembershipHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilEuropeanDeputyHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilMayorHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilRegionalCouncilorHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilRegionalCouncilPresidentHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilSenatorHandler;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdherentUpdateTerritorialCouncilMembershipsCommandHandlerTest extends TestCase
{
    private const QUALITIES_DEPENDING_ON_FUNCTION = [
        PoliticalFunctionNameEnum::PRESIDENT_OF_DEPARTMENTAL_COUNCIL => TerritorialCouncilQualityEnum::DEPARTMENTAL_COUNCIL_PRESIDENT,
        PoliticalFunctionNameEnum::MAYOR => TerritorialCouncilQualityEnum::MAYOR,
        PoliticalFunctionNameEnum::PRESIDENT_OF_REGIONAL_COUNCIL => TerritorialCouncilQualityEnum::REGIONAL_COUNCIL_PRESIDENT,
    ];

    /** @var Adherent */
    private $adherent;
    /** @var TerritorialCouncil */
    private $actualTC;
    /** @var TerritorialCouncil */
    private $newTC;

    /**
     * @dataProvider provideData
     */
    public function testInvoke(array $data): void
    {
        $uuid = Uuid::uuid4();
        $this->prepareAdherent($data, $uuid);
        $handler = $this->createHandler($data);
        $command = new AdherentUpdateTerritorialCouncilMembershipsCommand($uuid, false);

        // before
        if (isset($data['actual_coterr_membership'])) {
            $actualTCM = $this->adherent->getTerritorialCouncilMembership();
            self::assertNotNull($actualTCM);
            self::assertNotNull($actualTCM->getTerritorialCouncil());
            if (isset($data['actual_coterr_membership']['qualities'])) {
                self::assertCount(
                    0,
                    array_diff($data['actual_coterr_membership']['qualities'], $actualTCM->getQualityNames())
                );
            }

            $actualPCM = $this->adherent->getPoliticalCommitteeMembership();
            if (isset($data['actual_coterr_membership']['copol_qualities'])) {
                self::assertSame(
                    $this->actualTC->getPoliticalCommittee(),
                    $actualPCM->getPoliticalCommittee()
                );
                self::assertCount(
                    0,
                    array_diff($data['actual_coterr_membership']['copol_qualities'], $actualPCM->getQualityNames())
                );
            }

            if (isset($data['actual_coterr_membership']['candidacy'])) {
                $election = $actualTCM->getTerritorialCouncil()->getCurrentElection();

                self::assertNotNull($election);
                self::assertTrue($election->isOngoing());
                self::assertNotNull($actualCandidacy = $actualTCM->getCandidacyForElection($election));
                self::assertSame($data['actual_coterr_membership']['candidacy'], $actualCandidacy->getQuality());
            }
        } else {
            self::assertNull($this->adherent->getTerritorialCouncilMembership());
        }

        $handler($command);

        // after
        $tcMembership = $this->adherent->getTerritorialCouncilMembership();
        if (!isset($data['expected_coterr_membership']['coterr'])) {
            self::assertNull($tcMembership);

            return;
        }

        self::assertNotNull($tcMembership);

        self::assertSame(
            'actual' === $data['expected_coterr_membership']['coterr'] ? $this->actualTC : $this->newTC,
            $tcMembership->getTerritorialCouncil()
        );

        if (isset($data['expected_coterr_membership']['qualities'])) {
            self::assertCount(
                0,
                array_diff($data['expected_coterr_membership']['qualities'], $tcMembership->getQualityNames())
            );
        }

        $pcMembership = $this->adherent->getPoliticalCommitteeMembership();
        if (isset($data['expected_coterr_membership']['copol_qualities'])) {
            self::assertSame(
                'actual' === $data['expected_coterr_membership']['coterr'] ? $this->actualTC->getPoliticalCommittee() : $this->newTC->getPoliticalCommittee(),
                $pcMembership->getPoliticalCommittee()
            );
            self::assertCount(
                0,
                array_diff($data['expected_coterr_membership']['copol_qualities'], $pcMembership->getQualityNames())
            );
        }
    }

    public function provideData(): \Generator
    {
        yield 'add AL' => [[
            'al' => ['in_new_coterr'],
            'expected_coterr_membership' => [
                'coterr' => 'new',
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
        ]];
        yield 'no more CITY_COUNCILOR' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                ],
            ],
            'expected_coterr_membership' => [],
        ]];
        yield 'add DEPARTMENT_COUNCILOR' => [[
            'external_mandates' => [
                'in_new_coterr' => [
                    MandateTypeEnum::DEPARTMENTAL_COUNCIL,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'new',
                'qualities' => [
                    TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
                ],
            ],
        ]];
        yield 'still DEPARTMENT_COUNCILOR with a candidacy' => [[
            'external_mandates' => [
                'in_actual_coterr' => [
                    MandateTypeEnum::DEPARTMENTAL_COUNCIL,
                ],
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
                ],
                'candidacy' => TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
                ],
            ],
        ]];
        yield 'COMMITTEE_SUPERVISOR keeps his CoTerr membership because of a mandate in CoPol' => [[
            'internal_mandates' => [
                TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
                'copol_qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
                'copol_qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
        ]];
        yield 'no more COMMITTEE_SUPERVISOR' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
            'expected_coterr_membership' => [],
        ]];
        yield 'no more COMMITTEE_SUPERVISOR, no more CoPol member' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
                'copol_qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
            'expected_coterr_membership' => [],
        ]];
        yield 'still COMMITTEE_SUPERVISOR with a candidacy' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
                'candidacy' => TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
        ]];
        yield 'add ELECTED_CANDIDATE_ADHERENT' => [[
            'internal_mandates' => [
                TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'new',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
        ]];
        yield 'still ELECTED_CANDIDATE_ADHERENT because of a candidacy' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
                'candidacy' => TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
        ]];
        yield 'was COMMITTEE_SUPERVISOR, became ELECTED_CANDIDATE_ADHERENT' => [[
            'internal_mandates' => [
                TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
                'copol_qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
        ]];
        yield 'still ELECTED_CANDIDATE_ADHERENT because of a candidacy, even if qualities in another CoTerr' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
                'candidacy' => TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'external_mandates' => [
                'in_new_coterr' => [
                    MandateTypeEnum::DEPARTMENTAL_COUNCIL,
                    MandateTypeEnum::CITY_COUNCIL,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
        ]];
        yield 'was COMMITTEE_SUPERVISOR and ELECTED_CANDIDATE_ADHERENT, became only ELECTED_CANDIDATE_ADHERENT' => [[
            'internal_mandates' => [
                TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
        ]];
        yield 'was COMMITTEE_SUPERVISOR and ELECTED_CANDIDATE_ADHERENT, became only COMMITTEE_SUPERVISOR' => [[
            'internal_mandates' => [
                TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
        ]];
        yield 'still ELECTED_CANDIDATE_ADHERENT because of a candidacy and add another' => [[
            'external_mandates' => [
                'in_actual_coterr' => [
                    MandateTypeEnum::DEPARTMENTAL_COUNCIL,
                    MandateTypeEnum::CITY_COUNCIL,
                ],
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
                'candidacy' => TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                    TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
                ],
            ],
        ]];
        yield 'still ELECTED_CANDIDATE_ADHERENT because of a candidacy, but not COMMITTEE_SUPERVISOR' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
                'candidacy' => TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
        ]];
        yield 'remove all, except of ELECTED_CANDIDATE_ADHERENT because of a candidacy' => [[
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                    TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
                    TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR,
                ],
                'candidacy' => TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                ],
            ],
        ]];
        yield 'still CITY_COUNCIL in actual, even if COMMITTEE_SUPERVISOR in another' => [[
            'external_mandates' => [
                'in_actual_coterr' => [
                    MandateTypeEnum::CITY_COUNCIL,
                ],
            ],
            'al' => ['in_new_coterr'],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                ],
            ],
        ]];
        yield 'becomes CITY_COUNCIL in a new, even if COMMITTEE_SUPERVISOR in actual' => [[
            'external_mandates' => [
                'in_new_coterr' => [
                    MandateTypeEnum::CITY_COUNCIL,
                ],
            ],
            'al' => [
                'in_actual_coterr',
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'new',
                'qualities' => [
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                ],
            ],
        ]];
        yield 'add COMMITTEE_SUPERVISOR because of the internal mandate' => [[
            'internal_mandates' => [
                TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            ],
            'actual_coterr_membership' => [
                'qualities' => [
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'actual',
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
        ]];
        yield 'add COMMITTEE_SUPERVISOR because of the internal mandate, ignoring qualities in another TC' => [[
            'external_mandates' => [
                'in_new_coterr' => [
                    MandateTypeEnum::CITY_COUNCIL,
                ],
            ],
            'internal_mandates' => [
                TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            ],
            'expected_coterr_membership' => [
                'coterr' => 'new',
                'qualities' => [
                    TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
                ],
            ],
        ]];
        yield 'add CITY_COUNCIL AND MAYOR' => [[
            'external_mandates' => [
                'in_new_coterr' => [
                    MandateTypeEnum::CITY_COUNCIL => [
                        'functions' => [
                            PoliticalFunctionNameEnum::MAYOR,
                        ],
                    ],
                ],
            ],
            'expected_coterr_membership' => [
                'coterr' => 'new',
                'qualities' => [
                    TerritorialCouncilQualityEnum::CITY_COUNCILOR,
                    TerritorialCouncilQualityEnum::MAYOR,
                ],
            ],
        ]];
    }

    private function prepareAdherent(array $data, UuidInterface $uuid): void
    {
        $this->adherent = new class() extends Adherent {
            protected $uuid;

            public function setUuid(UuidInterface $uuid)
            {
                $this->uuid = $uuid;
            }

            public function addAdherentMandate(AdherentMandateInterface $mandate): void
            {
                if (!$this->adherentMandates->contains($mandate)) {
                    $this->adherentMandates->add($mandate);
                }
            }

            public function addMembership(CommitteeMembership $committeeMembership): void
            {
                if (!$this->memberships->contains($committeeMembership)) {
                    $this->memberships->add($committeeMembership);
                }
            }
        };

        $this->adherent->setUuid($uuid);
        if (isset($data['actual_coterr_membership'])) {
            $territorialCouncil = new class('Existing TC', '00') extends TerritorialCouncil {
                public function getId(): int
                {
                    return 1;
                }
            };
            $politicalCommittee = new PoliticalCommittee(
                'PC for TC '.$territorialCouncil->getName(),
                $territorialCouncil,
                true
            );
            $territorialCouncil->setPoliticalCommittee($politicalCommittee);

            $tcMembership = new class($territorialCouncil, $this->adherent) extends TerritorialCouncilMembership {
                public function addCandidacy(Candidacy $candidacy): void
                {
                    if (!$this->candidacies->contains($candidacy)) {
                        $this->candidacies->add($candidacy);
                    }
                }
            };

            $tcMembership->setTerritorialCouncil($territorialCouncil);
            $tcMembership->setAdherent($this->adherent);
            $this->adherent->setTerritorialCouncilMembership($tcMembership);

            if (isset($data['actual_coterr_membership']['qualities'])) {
                foreach ($data['actual_coterr_membership']['qualities'] as $qualityName) {
                    $tcMembership->addQuality(
                        new TerritorialCouncilQuality($qualityName, "Zone for $qualityName")
                    );
                }
            }

            if (isset($data['actual_coterr_membership']['copol_qualities'])) {
                $pcMembership = new PoliticalCommitteeMembership(
                    $territorialCouncil->getPoliticalCommittee(),
                    $this->adherent,
                    new \DateTime('-1 day')
                );
                foreach ($data['actual_coterr_membership']['copol_qualities'] as $qualityName) {
                    $pcMembership->addQuality(
                        new PoliticalCommitteeQuality($qualityName)
                    );
                }
                $this->adherent->setPoliticalCommitteeMembership($pcMembership);
            }

            if (isset($data['actual_coterr_membership']['candidacy'])) {
                $designation = new Designation();
                $designation->setCandidacyStartDate(new \DateTime('-2 days'));
                $election = new Election($designation, Uuid::uuid4());
                $territorialCouncil->setCurrentElection($election);

                $candidacy = new Candidacy(
                    $tcMembership,
                    $election,
                    Genders::MALE
                );
                $candidacy->setQuality($data['actual_coterr_membership']['candidacy']);
                $tcMembership->addCandidacy($candidacy);
            }

            $this->adherent->setTerritorialCouncilMembership($tcMembership);
        }
    }

    private function createHandler(array $data): AdherentUpdateTerritorialCouncilMembershipsCommandHandler
    {
        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects($this->once())->method('findByUuid')->willReturn($this->adherent);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('refresh')->with($this->adherent);
        $entityManager->expects($this->any())->method('flush');

        $handlers = $this->prepareHandlers($data, $entityManager);
        $busMock = $this->createMock(MessageBusInterface::class);
        $busMock->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        return new AdherentUpdateTerritorialCouncilMembershipsCommandHandler(
            $adherentRepository,
            $entityManager,
            $busMock,
            $handlers
        );
    }

    public function prepareHandlers(array $data, EntityManagerInterface $entityManager): \ArrayObject
    {
        $this->actualTC = $this->adherent->getTerritorialCouncilMembership()
            ? $this->adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()
            : null;
        $this->newTC = new class('New TC', '11') extends TerritorialCouncil {
            public function getId(): int
            {
                return 100;
            }
        };
        $pc = new PoliticalCommittee(
            'PC for TC '.$this->newTC->getName(),
            $this->newTC,
            true
        );
        $this->newTC->setPoliticalCommittee($pc);

        $tcRepository = $this->createMock(TerritorialCouncilRepository::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $committeeMandateRepository = $this->createMock(CommitteeAdherentMandateRepository::class);
        $tcMandateRepository = $this->createMock(TerritorialCouncilAdherentMandateRepository::class);
        $mandateRepository = $this->createMock(MandateRepository::class);

        $membershipRepository = $this->createMock(PoliticalCommitteeMembershipRepository::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $politicalCommitteeManager = new PoliticalCommitteeManager(
            $entityManager,
            $mandateRepository,
            $membershipRepository,
            $tcMandateRepository,
            $translator
        );

        // take into account existing ElectedRepresentative mandates
        if (isset($data['external_mandates']['in_actual_coterr'])) {
            $this->prepareForElectedRepresentativeMandates(
                $data['external_mandates']['in_actual_coterr'],
                $this->actualTC,
                $mandateRepository,
                $tcRepository
            );
        }

        if (isset($data['external_mandates']['in_new_coterr'])) {
            $this->prepareForElectedRepresentativeMandates(
                $data['external_mandates']['in_new_coterr'],
                $this->newTC,
                $mandateRepository,
                $tcRepository
            );
        }

        $committeeRepository = $this->createMock(CommitteeRepository::class);
        // take into account existing adherent mandates
        if (isset($data['internal_mandates'])) {
            foreach ($data['internal_mandates'] as $qualityName) {
                if (TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT === $qualityName) {
                    $committee = $this->createCommittee('AD');
                    $committeeRepository->expects($this->once())
                        ->method('findForAdherentWithCommitteeMandates')
                        ->with($this->adherent)
                        ->willReturn([$committee])
                    ;

                    // find a TerritorialCouncil
                    $tcRepository->expects($this->once())
                        ->method('findByCommittees')
                        ->with([$committee])
                        ->willReturn([$this->actualTC ?? $this->newTC])
                    ;

                    $committeeMandate = new CommitteeAdherentMandate(
                        $this->adherent,
                        Genders::MALE,
                        new \DateTime('-1 day'),
                    );
                    $committeeMandate->setCommittee($committee);

                    $committeeMandateRepository->expects($this->any())
                        ->method('findActiveCommitteeMandates')
                        ->with($this->adherent, [])
                        ->willReturn([$committeeMandate])
                    ;
                } elseif (\in_array($qualityName, TerritorialCouncilQualityEnum::ABLE_TO_CANDIDATE, true)) {
                    $tcMandate = TerritorialCouncilAdherentMandate::create(
                        $this->actualTC ?? $this->newTC,
                        $this->adherent,
                        new \DateTime('-1 day'),
                        Genders::MALE,
                        $qualityName
                    );
                    $tcMandateRepository->expects($this->any())
                        ->method('findActiveMandateWithQuality')
                        ->willReturnCallback(
                            function (Adherent $adherent, TerritorialCouncil $tc, string $quality) use ($qualityName, $tcMandate) {
                                return ($quality === $qualityName
                                    && $adherent === $this->adherent
                                    && (($this->actualTC && $tc === $this->actualTC) || $tc === $this->newTC))
                                ? $tcMandate
                                : null;
                            })
                    ;
                    $this->adherent->addAdherentMandate($tcMandate);
                }

                if (\in_array($qualityName, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_OFFICIO_MEMBERS)
                    && $this->actualTC) {
                    $tcMandateRepository->expects($this->once())
                        ->method('findForAdherentWithCommitteeMandates')
                        ->with($this->adherent)
                        ->willReturn(true)
                    ;
                }
            }
        }

        // Take into account an AL
        if (isset($data['al'])) {
            foreach ($data['al'] as $al) {
                $committee = $this->createCommittee('AL');
                $cm = $this->adherent->followCommittee($committee);
                $this->adherent->addMembership($cm);
                $committeeAdherentMandate = new CommitteeAdherentMandate(
                    $this->adherent,
                    Genders::MALE,
                    new \DateTime('-2 days'),
                    null,
                    CommitteeMandateQualityEnum::SUPERVISOR,
                    false
                );
                $committeeAdherentMandate->setCommittee($committee);
                $this->adherent->addAdherentMandate($committeeAdherentMandate);

                $tcRepository->expects($this->once())
                    ->method('findForSupervisor')
                    ->with($this->adherent)
                    ->willReturn(['in_actual_coterr' === $al ? $this->actualTC : $this->newTC])
                ;
            }
        }

        $boroughCouncilorHandler = new TerritorialCouncilBoroughCouncilorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $cityCouncilorHandler = new TerritorialCouncilCityCouncilorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $consularCouncilorHandler = new TerritorialCouncilConsularCouncilorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $corsicaAssemblyCouncilorHandler = new TerritorialCouncilCorsicaAssemblyMemberHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $departmentCouncilorHandler = new TerritorialCouncilDepartmentCouncilorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $deputyHandler = new TerritorialCouncilDeputyHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $europeanDeputyHandler = new TerritorialCouncilEuropeanDeputyHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $regionalCouncilorHandler = new TerritorialCouncilRegionalCouncilorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $senatorHandler = new TerritorialCouncilSenatorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $departmentalCouncilPresidentHandler = new TerritorialCouncilDepartmentalCouncilPresidentHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $mayorHandler = new TerritorialCouncilMayorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $regionalCouncilPresidentHandler = new TerritorialCouncilRegionalCouncilPresidentHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $mandateRepository
        );

        $electedCandidateAdherentHandler = new TerritorialCouncilElectedCandidateAdherentHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository,
            $committeeRepository
        );

        $committeeSupervisorHandler = new TerritorialCouncilCommitteeSupervisorHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository
        );

        $emptyMembershipHandler = new TerritorialCouncilEmptyMembershipHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository
        );

        $activeMandateHandler = new TerritorialCouncilActiveMandateHandler(
            $entityManager,
            $tcRepository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository
        );

        return new \ArrayObject([
            $boroughCouncilorHandler,
            $cityCouncilorHandler,
            $consularCouncilorHandler,
            $corsicaAssemblyCouncilorHandler,
            $departmentCouncilorHandler,
            $deputyHandler,
            $europeanDeputyHandler,
            $regionalCouncilorHandler,
            $senatorHandler,
            $departmentalCouncilPresidentHandler,
            $mayorHandler,
            $regionalCouncilPresidentHandler,
            $electedCandidateAdherentHandler,
            $committeeSupervisorHandler,
            $emptyMembershipHandler,
            $activeMandateHandler,
        ]);
    }

    private function prepareForElectedRepresentativeMandates(
        array $mandates,
        TerritorialCouncil $tc,
        MockObject $mandateRepository,
        MockObject $tcRepository
    ): void {
        $foundMandates = [];
        $withFunctions = false;
        foreach ($mandates as $type => $mandateType) {
            if (isset($mandateType['functions'])) {
                $withFunctions = true;
                $functions = $mandateType['functions'];
                $mandateType = $type;
                $mandate = $this->createMandate($mandateType, $functions);
                foreach ($functions as $function) {
                    if (\array_key_exists($function, self::QUALITIES_DEPENDING_ON_FUNCTION)) {
                        $foundMandates[$function] = $mandate;
                    }
                }
            } else {
                $mandate = $this->createMandate($mandateType);
            }

            $foundMandates[$mandateType] = $mandate;
        }

        // find a mandate
        $mandateRepository->expects($this->any())
            ->method('findByTypesAndUserListDefinitionForAdherent')
            ->willReturnCallback(function (array $types) use ($foundMandates) {
                return \array_key_exists($types[0], $foundMandates) ? [$foundMandates[$types[0]]] : [];
            })
        ;
        // find a mandate with a function
        if ($withFunctions) {
            $mandateRepository->expects($this->any())
                ->method('findByFunctionAndUserListDefinitionForAdherent')
                ->willReturnCallback(function (string $functionName) use ($foundMandates) {
                    return \array_key_exists($functionName, $foundMandates) ? [$foundMandates[$functionName]] : [];
                })
            ;
        }
        // find a TerritorialCouncil
        $tcRepository->expects($this->any())
            ->method('findByMandates')
            ->willReturnCallback(function (array $mandates) use ($foundMandates, $tc) {
                return array_intersect($mandates, $foundMandates) ? [$tc] : [];
            })
        ;
    }

    private function createMandate(string $mandateType, array $functions = []): Mandate
    {
        $mandate = new Mandate(
            $mandateType,
            true,
            null,
            null,
            null,
            null,
            true,
            new \DateTime('-2 days')
        );

        $zone = new Zone(Zone::CITY, "0-$mandateType", "Zone for '$mandateType' mandate");
        $mandate->setGeoZone($zone);

        foreach ($functions as $function) {
            $politicalFunction = new PoliticalFunction(
                $function,
                null,
                null,
                $mandate,
                true,
                new \DateTime('-1 day')
            );

            $mandate->addPoliticalFunction($politicalFunction);
        }

        return $mandate;
    }

    private function createCommittee(string $type): Committee
    {
        return new Committee(
            Uuid::uuid4(),
            Uuid::uuid4(),
            "Committee for $type",
            "Description of the committee for $type",
            PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.8705073, 2.3132432)
        );
    }
}
