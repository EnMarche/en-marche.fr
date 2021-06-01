<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommitteeAdherentMandateData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $committee1 = $this->getReference('committee-1');
        $committee3 = $this->getReference('committee-3');
        $committee4 = $this->getReference('committee-4');
        $committee5 = $this->getReference('committee-5');
        $committee6 = $this->getReference('committee-6');
        $committee7 = $this->getReference('committee-7');
        $committee8 = $this->getReference('committee-8');
        $committee9 = $this->getReference('committee-9');
        $committee10 = $this->getReference('committee-10');
        $committee11 = $this->getReference('committee-11');
        $committee12 = $this->getReference('committee-12');
        $committee13 = $this->getReference('committee-13');
        $committee15 = $this->getReference('committee-15');
        $carl = $this->getReference('adherent-2');
        $jacques = $this->getReference('adherent-3');
        $lucie = $this->getReference('adherent-4');
        $gisele = $this->getReference('adherent-5');
        $francis = $this->getReference('adherent-7');
        $referent = $this->getReference('adherent-8');
        $laura = $this->getReference('adherent-9');
        $martine = $this->getReference('adherent-10');
        $lolodie = $this->getReference('adherent-11');
        $kiroule = $this->getReference('adherent-12');
        $senatorialCandidate = $this->getReference('senatorial-candidate');
        $adherent31 = $this->getReference('adherent-31');
        $adherent32 = $this->getReference('adherent-32');

        $mandateFemale1 = new CommitteeAdherentMandate($gisele, Genders::FEMALE, $committee1, new \DateTime('2020-06-06'));
        $this->setReference('committee-mandate-female', $mandateFemale1);
        $manager->persist($mandateFemale1);

        $mandateMale1 = new CommitteeAdherentMandate($carl, Genders::MALE, $committee1, new \DateTime('2020-08-08'));
        $this->setReference('committee-mandate-male', $mandateMale1);
        $manager->persist($mandateMale1);

        $mandateMale2 = new CommitteeAdherentMandate($jacques, Genders::MALE, $committee1, new \DateTime('2018-01-01'), null, false, new \DateTime('2019-12-31'));
        $this->setReference('committee-mandate-male', $mandateMale2);
        $manager->persist($mandateMale2);

        $mandateFemale2 = new CommitteeAdherentMandate($lucie, Genders::FEMALE, $committee3, new \DateTime('2020-06-06'));
        $this->setReference('committee-mandate-female', $mandateFemale2);
        $manager->persist($mandateFemale2);

        $mandateMale3 = new CommitteeAdherentMandate($jacques, Genders::MALE, $committee3, new \DateTime('2020-08-08'));
        $this->setReference('committee-mandate-male', $mandateMale3);
        $manager->persist($mandateMale3);

        // Committee supervisors
        $supervisorMandateC1Finished = new CommitteeAdherentMandate($gisele, $gisele->getGender(), $committee1, new \DateTime('2017-05-05 11:11:11'), CommitteeMandateQualityEnum::SUPERVISOR, true, new \DateTime('2018-05-05 12:12:12'));
        $manager->persist($supervisorMandateC1Finished);
        $supervisorMandateC1 = new CommitteeAdherentMandate($jacques, $jacques->getGender(), $committee1, new \DateTime('2017-01-12 13:25:54'), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandateC1);
        $supervisorMandateC3 = new CommitteeAdherentMandate($francis, $francis->getGender(), $committee3, new \DateTime('2017-01-26 16:08:24'), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandateC3);
        $provisionalSupervisorMandateC3 = new CommitteeAdherentMandate($senatorialCandidate, $senatorialCandidate->getGender(), $committee3, new \DateTime('2017-01-27 17:09:25'), CommitteeMandateQualityEnum::SUPERVISOR, true);
        $manager->persist($provisionalSupervisorMandateC3);
        $provisionalSupervisorMandateC3_2 = new CommitteeAdherentMandate($gisele, $gisele->getGender(), $committee3, new \DateTime('2017-01-27 17:09:25'), CommitteeMandateQualityEnum::SUPERVISOR, true);
        $manager->persist($provisionalSupervisorMandateC3_2);
        $supervisorMandateC4 = new CommitteeAdherentMandate($francis, $francis->getGender(), $committee4, new \DateTime(), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandateC4);
        $supervisorMandateC5 = new CommitteeAdherentMandate($francis, $francis->getGender(), $committee5, new \DateTime(), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandateC5);
        $provisionalSupervisorMandateC5 = new CommitteeAdherentMandate($gisele, $gisele->getGender(), $committee5, new \DateTime('-2 months'), CommitteeMandateQualityEnum::SUPERVISOR, true);
        $manager->persist($provisionalSupervisorMandateC5);
        $supervisorMandate = new CommitteeAdherentMandate($laura, $laura->getGender(), $committee6, new \DateTime(), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandate);
        $supervisorMandateC7 = new CommitteeAdherentMandate($martine, $martine->getGender(), $committee7, new \DateTime('2020-10-11 11:11:11'), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandateC7);
        $provisionalSupervisorMandateC7 = new CommitteeAdherentMandate($senatorialCandidate, $senatorialCandidate->getGender(), $committee7, new \DateTime('2020-10-10 10:10:10'), CommitteeMandateQualityEnum::SUPERVISOR, true);
        $manager->persist($provisionalSupervisorMandateC7);
        $supervisorMandateC8 = new CommitteeAdherentMandate($lolodie, $lolodie->getGender(), $committee8, new \DateTime(), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandateC8);
        $supervisorMandateC9 = new CommitteeAdherentMandate($kiroule, $kiroule->getGender(), $committee9, new \DateTime(), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandateC9);
        $supervisorMandate = new CommitteeAdherentMandate($referent, $referent->getGender(), $committee10, new \DateTime(), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandate);
        $provisionalSupervisorMandateC11 = new CommitteeAdherentMandate($lolodie, $lolodie->getGender(), $committee11, new \DateTime('2021-01-01 01:01:01'), CommitteeMandateQualityEnum::SUPERVISOR, true);
        $manager->persist($provisionalSupervisorMandateC11);
        $supervisorMandate = new CommitteeAdherentMandate($senatorialCandidate, $senatorialCandidate->getGender(), $committee12, new \DateTime('-2 months'), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandate);
        $supervisorMandate = new CommitteeAdherentMandate($adherent31, $adherent31->getGender(), $committee13, new \DateTime('-2 months'), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandate);
        $supervisorMandate = new CommitteeAdherentMandate($adherent32, $adherent32->getGender(), $committee15, new \DateTime('-2 months'), CommitteeMandateQualityEnum::SUPERVISOR);
        $manager->persist($supervisorMandate);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeData::class,
        ];
    }
}
