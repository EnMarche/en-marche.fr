<?php

namespace App\DataFixtures\ORM;

use App\Entity\Report\CitizenProjectReport;
use App\Entity\Report\IdeasWorkshop\IdeaReport;
use App\Entity\Report\IdeasWorkshop\ThreadCommentReport;
use App\Entity\Report\IdeasWorkshop\ThreadReport;
use App\Entity\Report\ReportReasonEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadReportData extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $report1 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-1'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $report2 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-2'),
            [ReportReasonEnum::REASON_OTHER, ReportReasonEnum::REASON_COMMERCIAL_CONTENT],
            'Ce projet n\'est pas intéressant.'
        );
        $report2->resolve();

        $report3 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-3'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );
        $report3->resolve();

        $report4 = new CitizenProjectReport(
            $this->getReference('citizen-project-2'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $report5 = new CitizenProjectReport(
            $this->getReference('citizen-project-2'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $report6 = new CitizenProjectReport(
            $this->getReference('citizen-project-3'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $report7 = new IdeaReport(
            $this->getReference('idea-disabled'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_OTHER],
            'Je suis scandalisé!'
        );

        $report8 = new ThreadReport(
            $this->getReference('thread-reported'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_OTHER],
            'Je suis choqué!'
        );

        $report9 = new ThreadCommentReport(
            $this->getReference('thread-comment-reported'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_OTHER],
            'Je suis décu...'
        );

        $manager->persist($report1);
        $manager->persist($report2);
        $manager->persist($report3);
        $manager->persist($report4);
        $manager->persist($report5);
        $manager->persist($report6);
        $manager->persist($report7);
        $manager->persist($report8);
        $manager->persist($report9);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
            LoadIdeaData::class,
            LoadIdeaThreadData::class,
            LoadIdeaThreadCommentData::class,
        ];
    }
}
