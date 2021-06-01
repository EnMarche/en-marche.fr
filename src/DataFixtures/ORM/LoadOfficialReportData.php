<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\OfficialReport;
use App\Entity\TerritorialCouncil\OfficialReportDocument;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadOfficialReportData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $coPol75 = $this->getReference('coPol_75');
        $referent = $this->getReference('adherent-8');
        $referent75and77 = $this->getReference('adherent-19');

        $report1 = $this->createReport(
            $coPol75,
            $referent,
            $referent75and77,
            'Test PV 75 1',
            new \DateTime('2020-10-10 10:10:10'),
            new \DateTime('2020-10-10 10:10:10')
        );
        $doc1 = $this->createDocument($report1, 'test_report_1');
        $report1->addDocument($doc1);

        $manager->persist($doc1);
        $manager->persist($report1);

        $report2 = $this->createReport(
            $coPol75,
            $referent,
            $referent75and77,
            'Deuxième PV 75',
            new \DateTime('2020-10-15 15:15:15'),
            new \DateTime('2020-10-20 10:20:10')
        );
        $doc2_1 = $this->createDocument($report2, 'test_report_2_1');
        $doc2_2 = $this->createDocument($report2, 'test_report_2_2', 2);
        $report2->addDocument($doc2_1);
        $report2->addDocument($doc2_2);

        $manager->persist($doc2_1);
        $manager->persist($doc2_2);
        $manager->persist($report2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadPoliticalCommitteeData::class,
        ];
    }

    private function createReport(
        PoliticalCommittee $coPol,
        Adherent $author,
        Adherent $creator,
        string $name,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ): OfficialReport {
        $report = new OfficialReport();
        $report->setPoliticalCommittee($coPol);
        $report->setAuthor($author);
        $report->setCreatedAt($createdAt);
        $report->setUpdatedAt($updatedAt);
        $report->getCreatedBy($creator);
        $report->getUpdatedBy($creator);
        $report->setName($name);

        return $report;
    }

    private function createDocument(OfficialReport $report, $filename, int $version = 1): OfficialReportDocument
    {
        return new OfficialReportDocument(
            $report,
            $filename,
            '.pdf',
            'application/pdf',
            $version
        );
    }
}
