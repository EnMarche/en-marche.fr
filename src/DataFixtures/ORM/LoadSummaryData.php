<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\MemberSummary\JobExperience;
use App\Entity\MemberSummary\Language;
use App\Entity\MemberSummary\MissionType;
use App\Entity\MemberSummary\Training;
use App\Entity\Skill;
use App\Summary\Contract;
use App\Summary\Contribution;
use App\Summary\JobDuration;
use App\Summary\JobLocation;
use App\Summary\SummaryFactory;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadSummaryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $summaryFactory = $this->getSummaryFactory();

        $summary1 = $summaryFactory->createFromArray([
            'adherent' => $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_4_UUID),
            'slug' => 'lucie-olivera',
            'availabilities' => [JobDuration::PART_TIME],
            'contactEmail' => 'luciole1989@spambox.fr',
            'contributionWish' => Contribution::VOLUNTEER,
            'jobLocations' => [JobLocation::ON_REMOTE],
            'motivation' => 'C\'est mon secret',
            'professionalSynopsis' => '30 ans de travail dans le domaine scientifique',
            'currentProfession' => 'Bio-informaticienne',
            'websiteUrl' => 'https://lucie-olivera-fake.en-marche-dev.fr',
            'viadeoUrl' => 'https://fr.viadeo.com/lucie-olivera-fake',
            'facebookUr' => 'https://www.facebook.com/lucie-olivera-fake',
            'twitterNickname' => 'https://twitter.com/lucie-olivera-fake',
            'missionWishes' => $manager->getRepository(MissionType::class)->findBy(['name' => 'Faire remonter les opinions du terrain']),
            'pictureUploaded' => true,
        ]);
        $manager->persist($summary1);

        // Experiences
        $experience11 = new JobExperience();
        $experience11->setCompany('Institut KNURE');
        $experience11->setDescription('Bio-informaticien dans l\'institut KNURE');
        $experience11->setOnGoing(true);
        $experience11->setStartedAt(new \DateTime('2007-04-28 00:00:00'));
        $experience11->setDisplayOrder(1);
        $experience11->setCompanyFacebookPage('https://www.facebook.com/khure-fake');
        $experience11->setCompanyTwitterNickname('khureBioInformatique');
        $experience11->setContract(Contract::PERMANENT);
        $experience11->setDuration(JobDuration::FULL_TIME);
        $experience11->setLocation('Genève');
        $experience11->setPosition('Bio-informaticien');
        $experience11->setWebsite('http://khure.bioinformatique.ch');
        $summary1->addExperience($experience11);

        $experience12 = new JobExperience();
        $experience12->setCompany('Univérsité Lyon 1');
        $experience12->setDescription('Professeur à l\'université');
        $experience12->setOnGoing(false);
        $experience12->setStartedAt(new \DateTime('1995-10-26 00:00:00'));
        $experience12->setEndedAt(new \DateTime('2006-01-26 00:00:00'));
        $experience12->setDisplayOrder(2);
        $experience12->setCompanyFacebookPage('https://www.facebook.com/lyon1-fake');
        $experience12->setCompanyTwitterNickname('lyon1-fake');
        $experience12->setContract(Contract::PERMANENT);
        $experience12->setDuration(JobDuration::FULL_TIME);
        $experience12->setLocation('Lyon');
        $experience12->setPosition('Professeur');
        $experience12->setWebsite('http://lyon.bioinformatique.fr');
        $summary1->addExperience($experience12);

        // Trainings
        $training11 = new Training();
        $training11->setDescription('Master en Bio-Informatique');
        $training11->setOrganization('Lyon 1');
        $training11->setDiploma('Diplôme d\'ingénieur');
        $training11->setDisplayOrder(1);
        $training11->setStartedAt(new \DateTime('1993-09-01 00:00:00'));
        $training11->setEndedAt(new \DateTime('1995-10-01 00:00:00'));
        $training11->setOnGoing(false);
        $training11->setStudyField('Bio-Informatique');
        $summary1->addTraining($training11);

        $training12 = new Training();
        $training12->setDescription('Génie biologique option Bio-Informatique');
        $training12->setOrganization('Lyon 1');
        $training12->setDiploma('DUT Génie biologique');
        $training12->setDisplayOrder(2);
        $training11->setExtracurricular('Les activités musicales');
        $training12->setStartedAt(new \DateTime('1990-09-01 00:00:00'));
        $training12->setEndedAt(new \DateTime('1992-10-01 00:00:00'));
        $training12->setOnGoing(false);
        $training12->setStudyField('Bio-Informatique');
        $summary1->addTraining($training12);

        // Languages
        $language11 = new Language();
        $language11->setCode('fr');
        $language11->setLevel(Language::LEVEL_FLUENT);
        $summary1->addLanguage($language11);

        $language12 = new Language();
        $language12->setCode('en');
        $language12->setLevel(Language::LEVEL_HIGH);
        $summary1->addLanguage($language12);

        $language13 = new Language();
        $language13->setCode('es');
        $language13->setLevel(Language::LEVEL_MEDIUM);
        $summary1->addLanguage($language13);

        // Skills
        foreach ($manager->getRepository(Skill::class)->findBy(['name' => [
            LoadSkillData::SKILLS['S001'],
            LoadSkillData::SKILLS['S002'],
            LoadSkillData::SKILLS['S003'],
            LoadSkillData::SKILLS['S004'], ],
        ]) as $skill) {
            $summary1->addSkill($skill);
        }

        $summary1->publish();

        $summary2 = $summaryFactory->createFromArray([
            'adherent' => $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_2_UUID),
            'slug' => 'carl-mirabeau',
            'availabilities' => [JobDuration::PUNCTUALLY],
            'contactEmail' => 'carl9992@example.fr',
            'contributionWish' => Contribution::CONTRACTOR,
            'jobLocations' => [JobLocation::ON_REMOTE],
            'motivation' => 'Je le veux !',
            'professionalSynopsis' => 'Travail en média',
            'currentProfession' => 'Aucun',
            'websiteUrl' => 'https://carl-mirabeau-fake.en-marche-dev.fr',
            'viadeoUrl' => 'https://fr.viadeo.com/carl-mirabeau-fake',
            'facebookUr' => 'https://www.facebook.com/carl-mirabeau-fake',
            'twitterNickname' => 'https://twitter.com/carl-mirabeau-fake',
            'missionWishes' => $manager->getRepository(MissionType::class)->findBy(['name' => 'Faire émerger des idées nouvelles']),
        ]);
        $manager->persist($summary2);

        // Trainings
        $training21 = new Training();
        $training21->setDescription('Master en Média - Audiovisuel');
        $training21->setOrganization('Clermont');
        $training21->setDiploma('Master en Média');
        $training21->setDisplayOrder(1);
        $training21->setStartedAt(new \DateTime('2013-09-01 00:00:00'));
        $training21->setEndedAt(new \DateTime('2016-09-01 00:00:00'));
        $training21->setOnGoing(false);
        $training21->setStudyField('Média');
        $training21->setSummary($summary2);
        $manager->persist($training21);

        // Languages
        $language21 = new Language();
        $language21->setCode('fr');
        $language21->setLevel(Language::LEVEL_FLUENT);
        $language21->setSummary($summary2);
        $manager->persist($language21);

        $language22 = new Language();
        $language22->setCode('en');
        $language22->setLevel(Language::LEVEL_MEDIUM);
        $language22->setSummary($summary2);
        $manager->persist($language22);

        $language23 = new Language();
        $language23->setCode('zh');
        $language23->setLevel(Language::LEVEL_BASIC);
        $language23->setSummary($summary2);
        $manager->persist($language23);

        // Skills
        foreach ($manager->getRepository(Skill::class)->findBy(['name' => [
            LoadSkillData::SKILLS['S004'],
            LoadSkillData::SKILLS['S005'],
            LoadSkillData::SKILLS['S006'],
            LoadSkillData::SKILLS['S007'],
            LoadSkillData::SKILLS['S008'], ],
        ]) as $skill) {
            $summary2->addSkill($skill);
        }

        $summary3 = $summaryFactory->createFromArray([
            'adherent' => $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_3_UUID),
            'slug' => 'jacques-picard',
            'availabilities' => [JobDuration::FULL_TIME],
            'contactEmail' => 'jacques.picard@en-marche.fr',
            'contributionWish' => Contribution::EMPLOYEE,
            'jobLocations' => [JobLocation::ON_SITE],
            'motivation' => 'Je suis motivé',
            'professionalSynopsis' => 'Travaillé dans differents domaines.',
        ]);
        $manager->persist($summary3);

        $manager->flush();
    }

    private function getSummaryFactory(): SummaryFactory
    {
        return new SummaryFactory(new Slugify());
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadSkillData::class,
        ];
    }
}
