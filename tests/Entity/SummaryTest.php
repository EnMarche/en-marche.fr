<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\MemberSummary\JobExperience;
use App\Entity\MemberSummary\Language;
use App\Entity\MemberSummary\Training;
use App\Entity\PostAddress;
use App\Entity\Skill;
use App\Entity\Summary;
use App\Membership\ActivityPositions;
use App\Summary\Contribution;
use App\Summary\JobDuration;
use App\Summary\JobLocation;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class SummaryTest extends TestCase
{
    public function testPublishing()
    {
        $summary = $this->createSummary();

        $this->assertSame(0, $summary->getCompletion());

        $summary->setCurrentProfession('Professeur');

        $this->assertSame(0, $summary->getCompletion());

        $summary->setCurrentProfession('Professeur');

        $this->assertSame(0, $summary->getCompletion());

        $summary->setCurrentPosition(ActivityPositions::UNEMPLOYED);

        $this->assertSame(8, $summary->getCompletion());

        $summary->setContributionWish(Contribution::VOLUNTEER);

        $this->assertSame(15, $summary->getCompletion());

        $summary->setAvailabilities([JobDuration::PUNCTUALLY]);

        $this->assertSame(22, $summary->getCompletion());

        $summary->setJobLocations([JobLocation::ON_SITE]);

        $this->assertSame(29, $summary->getCompletion());

        $summary->setProfessionalSynopsis('This is a fake summary.');

        $this->assertSame(36, $summary->getCompletion());

        $summary->setMissionTypeWishes($this->createSummaryItemCollectionMock());

        $this->assertSame(43, $summary->getCompletion());

        $summary->setMotivation('I\m motivated as far as a fake can be.');

        $this->assertSame(50, $summary->getCompletion());

        $summary->setShowingRecentActivities(true);

        $this->assertSame(50, $summary->getCompletion());

        $summary->addExperience(new JobExperience());

        $this->assertSame(58, $summary->getCompletion());

        $summary->addSkill(new Skill());

        $this->assertSame(65, $summary->getCompletion());

        $summary->addLanguage(new Language());

        $this->assertSame(72, $summary->getCompletion());

        $summary->setMemberInterests(['complete test']);

        $this->assertSame(79, $summary->getCompletion());

        $summary->addTraining(new Training());

        $this->assertSame(86, $summary->getCompletion());

        $summary->setLinkedInUrl('linkedin.com/in/fake');
        $summary->setWebsiteUrl('www.fake.com');
        $summary->setFacebookUrl('facebook.com/fake');
        $summary->setTwitterNickname('@fake');
        $summary->setViadeoUrl('viadeo.com/fake');

        $this->assertSame(86, $summary->getCompletion());

        $summary->setContactEmail('fake@contact.com');

        $this->assertSame(93, $summary->getCompletion());

        $summary->setPictureUploaded(true);

        $this->assertSame(100, $summary->getCompletion());
    }

    public function createSummary(): Summary
    {
        return Summary::createFromMember(
            Adherent::create(Uuid::uuid4(), 'fake@example.org', '', 'male', 'Fake', 'Member', new \DateTime('-30 years'), '', PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024')),
            'fake.member'
        );
    }

    public function createSummaryItemCollectionMock(int $count = 1): Collection
    {
        $mock = $this->createMock(Collection::class);
        $mock->expects($this->any())
            ->method('count')
            ->willReturn($count)
        ;

        return $mock;
    }
}
