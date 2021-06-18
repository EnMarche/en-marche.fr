<?php

namespace Tests\App\Security\Voter;

use App\Entity\Event\CommitteeEvent;
use App\Event\EventPermissions;
use App\Repository\EventRegistrationRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\AttendEventVoter;
use PHPUnit\Framework\MockObject\MockObject;

class AttendEventVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var EventRegistrationRepository|MockObject
     */
    private $registrationRepository;

    protected function setUp(): void
    {
        $this->registrationRepository = $this->createMock(EventRegistrationRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->registrationRepository = null;

        parent::tearDown();
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new AttendEventVoter($this->registrationRepository);
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, EventPermissions::UNREGISTER, $this->createMock(CommitteeEvent::class)];
    }

    /**
     * @dataProvider provideRegisteredEventCases
     */
    public function testAdherentIsGrantedToUnregisterIfRegistered(bool $granted, string $attribute, $subject)
    {
        $adherent = $this->createAdherentMock();
        $email = $adherent->getEmailAddress();

        $adherent
            ->expects($this->once())
            ->method('getEmailAddress')
        ;

        $this->registrationRepository->expects($this->once())
            ->method('isAlreadyRegistered')
            ->with($email, $subject)
            ->willReturn($granted)
        ;

        $this->assertGrantedForAdherent($granted, true, $adherent, $attribute, $subject);
    }

    public function provideRegisteredEventCases(): iterable
    {
        $event = $this->createMock(CommitteeEvent::class);

        yield [true, EventPermissions::UNREGISTER, $event];
        yield [false, EventPermissions::UNREGISTER, $event];
    }
}
