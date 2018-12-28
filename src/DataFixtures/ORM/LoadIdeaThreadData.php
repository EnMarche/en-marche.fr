<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadCommentStatusEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadIdeaThreadData extends AbstractFixture implements DependentFixtureInterface
{
    public const THREAD_01_UUID = 'dfd6a2f2-5579-421f-96ac-98993d0edea1';
    public const THREAD_02_UUID = '6b077cc4-1cbd-4615-b607-c23009119406';
    public const THREAD_03_UUID = 'a508a7c5-8b07-41f4-8515-064f674a65e8';
    public const THREAD_04_UUID = '78d7daa1-657c-4e7e-87bc-24eb4ea26ea2';
    public const THREAD_05_UUID = 'b191f13a-5a05-49ed-8ec3-c335aa68f439';
    public const THREAD_06_UUID = '7857957c-2044-4469-bd9f-04a60820c8bd';

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_thread');

        $adherent2 = $this->getReference('adherent-2');
        $adherent4 = $this->getReference('adherent-4');
        $adherent5 = $this->getReference('adherent-5');

        $threadAQProblemAdherent2 = Thread::create(
            Uuid::fromString(self::THREAD_01_UUID),
        'J\'ouvre une discussion sur le problème.',
        $adherent2,
        $this->getReference('answer-q-problem'),
        ThreadCommentStatusEnum::POSTED,
        new \DateTime('2 hours ago')
        );
        $this->addReference('thread-aq-problem', $threadAQProblemAdherent2);

        $threadAQAnswerAdherent4 = Thread::create(
            Uuid::fromString(self::THREAD_02_UUID),
            'J\'ouvre une discussion sur la solution.',
            $adherent4,
            $this->getReference('answer-q-answer'),
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('1 hour ago')
        );
        $this->setReference('thread-aq-answer', $threadAQAnswerAdherent4);

        $threadAQCompareAdherent5 = Thread::create(
            Uuid::fromString(self::THREAD_03_UUID),
            'J\'ouvre une discussion sur la comparaison.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('30 minutes ago')
        );
        $this->setReference('thread-aq-compare', $threadAQCompareAdherent5);

        $threadRefused = Thread::create(
            Uuid::fromString(self::THREAD_04_UUID),
            'Une discussion refusée.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            ThreadCommentStatusEnum::REFUSED,
            new \DateTime('10 minutes ago')
        );

        $threadReported = Thread::create(
            Uuid::fromString(self::THREAD_05_UUID),
            'Une discussion signalée.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            ThreadCommentStatusEnum::REPORTED,
            new \DateTime('5 minutes ago')
        );

        $threadHE = Thread::create(
            Uuid::fromString(self::THREAD_06_UUID),
            '[Help Ecology] J\'ouvre une discussion sur le problème.',
            $adherent5,
            $this->getReference('answer-q-problem-idea-he'),
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('2 minutes ago')
        );

        $manager->persist($threadAQProblemAdherent2);
        $manager->persist($threadAQAnswerAdherent4);
        $manager->persist($threadAQCompareAdherent5);
        $manager->persist($threadRefused);
        $manager->persist($threadReported);
        $manager->persist($threadHE);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaAnswerData::class,
        ];
    }
}
