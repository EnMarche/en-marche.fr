<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\Thread;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadIdeaThreadData extends Fixture implements DependentFixtureInterface
{
    public const THREAD_01_UUID = 'dfd6a2f2-5579-421f-96ac-98993d0edea1';
    public const THREAD_02_UUID = '6b077cc4-1cbd-4615-b607-c23009119406';
    public const THREAD_03_UUID = 'a508a7c5-8b07-41f4-8515-064f674a65e8';
    public const THREAD_04_UUID = '78d7daa1-657c-4e7e-87bc-24eb4ea26ea2';
    public const THREAD_05_UUID = 'b191f13a-5a05-49ed-8ec3-c335aa68f439';
    public const THREAD_06_UUID = '7857957c-2044-4469-bd9f-04a60820c8bd';
    public const THREAD_07_UUID = '2512539f-47fe-4a13-869e-78b81e6f9dd0';
    public const THREAD_08_UUID = '1474504d-8024-4e54-85f7-59666a11cd77';
    public const THREAD_09_UUID = 'f5cfb1c0-e6a4-4775-a595-ebd2625c4831';
    public const THREAD_10_UUID = 'c8b67e90-18b2-42d7-9e62-3fa612a2efb8';
    public const THREAD_11_UUID = 'f3f52bb4-5a83-4d21-9720-73cd443c42c8';
    public const THREAD_12_UUID = 'be9cb632-7ebf-46c5-8f1d-068d10800272';

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_thread');

        $adherent2 = $this->getReference('adherent-2');
        $adherent3 = $this->getReference('adherent-3');
        $adherent4 = $this->getReference('adherent-4');
        $adherent5 = $this->getReference('adherent-5');
        $adherent12 = $this->getReference('adherent-12');
        $adherent13 = $this->getReference('adherent-13');

        $threadAQProblemAdherent2 = Thread::create(
            Uuid::fromString(self::THREAD_01_UUID),
        'J\'ouvre une discussion sur le problème.',
        $adherent2,
        $this->getReference('answer-q-problem'),
        new \DateTime('-30 minutes')
        );
        $this->addReference('thread-aq-problem', $threadAQProblemAdherent2);

        $threadAQAnswerAdherent4 = Thread::create(
            Uuid::fromString(self::THREAD_02_UUID),
            'J\'ouvre une discussion sur la solution.',
            $adherent4,
            $this->getReference('answer-q-answer'),
            new \DateTime('-25 minutes')
        );
        $this->setReference('thread-aq-answer', $threadAQAnswerAdherent4);

        $threadAQCompareAdherent5 = Thread::create(
            Uuid::fromString(self::THREAD_03_UUID),
            'J\'ouvre une discussion sur la comparaison.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            new \DateTime('-20 minutes')
        );
        $this->setReference('thread-aq-compare', $threadAQCompareAdherent5);

        $threadRefused = Thread::create(
            Uuid::fromString(self::THREAD_04_UUID),
            'Une nouvelle discussion.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            new \DateTime('-15 minutes')
        );

        $threadReported = Thread::create(
            Uuid::fromString(self::THREAD_05_UUID),
            'Une discussion signalée.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            new \DateTime('-10 minutes'),
            false
        );
        $this->setReference('thread-reported', $threadReported);

        $threadOnSecondPage = Thread::create(
            Uuid::fromString(self::THREAD_12_UUID),
            'Une discussion en 2 eme page.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            new \DateTime('-8 minutes'),
            false
        );
        $this->setReference('thread-reported', $threadReported);

        $threadHE = Thread::create(
            Uuid::fromString(self::THREAD_06_UUID),
            '[Help Ecology] J\'ouvre une discussion sur le problème.',
            $adherent5,
            $this->getReference('answer-q-problem-idea-he'),
            new \DateTime('-5 minutes')
        );
        $this->setReference('thread-he', $threadHE);

        $threadHEU = Thread::create(
            Uuid::fromString(self::THREAD_07_UUID),
            '[Help Ecology] Une discussion d\'un adhérent à desadhérer',
            $adherent13,
            $this->getReference('answer-q-problem-idea-he'),
            new \DateTime('-1 minute')
        );

        $threadReduceWaste = Thread::create(
            Uuid::fromString(self::THREAD_08_UUID),
            '[Reduce Waste] Une discussion avec un commentaire',
            $adherent3,
            $this->getReference('answer-q-problem-idea-reduce-waste'),
            new \DateTime('-7 minute')
        );
        $this->setReference('thread-idea-reduce-waste', $threadReduceWaste);

        $threadDisabled = Thread::create(
            Uuid::fromString(self::THREAD_09_UUID),
            '[Help Ecology] Une discussion modérée d\'un adhérent à desadhérer',
            $adherent13,
            $this->getReference('answer-q-problem-idea-he'),
            new \DateTime('-2 minutes'),
            false,
            false
        );

        $threadDisabledOnPublishedIdea = Thread::create(
            Uuid::fromString(self::THREAD_10_UUID),
            '[Reduce Noise] Une discussion modérée',
            $adherent12,
            $this->getReference('answer-q-problem-idea-reduce-noise'),
            new \DateTime('-2 minutes'),
            false,
            false
        );

        $threadDeletedOnPublishedIdea = Thread::create(
            Uuid::fromString(self::THREAD_11_UUID),
            '[Reduce Noise] Une discussion supprimée',
            $adherent12,
            $this->getReference('answer-q-problem-idea-reduce-noise'),
            new \DateTime('-5 minutes'),
            false,
            true
        );

        $manager->persist($threadAQProblemAdherent2);
        $manager->persist($threadAQAnswerAdherent4);
        $manager->persist($threadAQCompareAdherent5);
        $manager->persist($threadRefused);
        $manager->persist($threadReported);
        $manager->persist($threadHE);
        $manager->persist($threadHEU);
        $manager->persist($threadReduceWaste);
        $manager->persist($threadDisabled);
        $manager->persist($threadDisabledOnPublishedIdea);
        $manager->persist($threadDeletedOnPublishedIdea);
        $manager->persist($threadOnSecondPage);

        $manager->flush();

        $manager->remove($threadDeletedOnPublishedIdea);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaAnswerData::class,
        ];
    }
}
