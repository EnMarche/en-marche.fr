<?php

namespace App\DataFixtures\ORM;

use App\CitizenProject\CitizenProjectFactory;
use App\DataFixtures\AutoIncrementResetter;
use App\Entity\NullablePostAddress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemInterface;

class LoadCitizenProjectData extends Fixture implements DependentFixtureInterface
{
    public const CITIZEN_PROJECT_1_UUID = 'aa364092-3999-4102-930c-f711ef971195';
    public const CITIZEN_PROJECT_2_UUID = '552934ed-2ac6-4a3a-a490-ddc8bf959444';
    public const CITIZEN_PROJECT_3_UUID = '942201fe-bffa-4fed-a551-71c3e49cea43';
    public const CITIZEN_PROJECT_4_UUID = '31fe9de2-5ba2-4305-be82-8e9a329e2579';
    public const CITIZEN_PROJECT_5_UUID = '0ac45a9f-8495-4b32-bd2d-e43a27f5e4b6';
    public const CITIZEN_PROJECT_6_UUID = 'cff414ca-3ee7-43db-8201-0852b0c05334';
    public const CITIZEN_PROJECT_7_UUID = 'fc83efde-17e5-4e87-b9e9-71b165aecd10';
    public const CITIZEN_PROJECT_8_UUID = '55bc9c81-612b-4108-b5ae-d065a69456d1';
    public const CITIZEN_PROJECT_9_UUID = 'eacefe0b-ace6-4ed5-a747-61f874f165f6';
    public const CITIZEN_PROJECT_10_UUID = 'ac98b08c-4e2d-4894-aa61-140b5be89645';
    public const CITIZEN_PROJECT_11_UUID = '695af719-ccfb-4754-813b-6685c757a855';
    public const CITIZEN_PROJECT_12_UUID = '3251ff14-cb9e-4f50-aab4-c332e50e9ff1';
    public const CITIZEN_PROJECT_13_UUID = '9f78a464-ddce-45cf-9cc1-3303c50842f2';

    private $storage;
    private $citizenProjectFactory;

    public function __construct(FilesystemInterface $storage, CitizenProjectFactory $citizenProjectFactory)
    {
        $this->storage = $storage;
        $this->citizenProjectFactory = $citizenProjectFactory;
    }

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'citizen_projects');

        // Add CitizenProject default image
        $this->storage->put('images/citizen_projects/default.png', file_get_contents(__DIR__.'/../citizen-projects/default.png'));

        $citizenProject1 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_1_UUID,
            'name' => 'Le projet citoyen à Paris 8',
            'subtitle' => 'Le projet citoyen des habitants du 8ème arrondissement de Paris.',
            'category' => $this->getReference('cpc001'),
            'problem_description' => 'Problème 1',
            'proposed_solution' => 'Solution 1',
            'required_means' => 'Les moyens 1',
            'created_by' => LoadAdherentData::ADHERENT_3_UUID,
            'created_at' => '2017-10-12 12:25:54',
            'address' => NullablePostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.8705073, 2.3032432),
            'district' => 'Paris 8e',
        ]);

        $citizenProject1->addSkill($this->getReference('cps001'));
        $citizenProject1->addSkill($this->getReference('cps002'));
        $citizenProject1->addSkill($this->getReference('cps003'));
        $citizenProject1->addSkill($this->getReference('cps004'));
        $citizenProject1->addSkill($this->getReference('cps005'));
        $citizenProject1->setImageName('default.png');
        $citizenProject1->approved('2017-10-12 15:54:18');
        $citizenProject1->addCommitteeOnSupport($this->getReference('committee-10'));
        $this->addReference('citizen-project-1', $citizenProject1);

        $citizenProject2 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_2_UUID,
            'name' => 'Le projet citoyen à Marseille',
            'subtitle' => 'Le projet citoyen à Marseille !',
            'category' => $this->getReference('cpc002'),
            'problem_description' => 'Problème 2',
            'proposed_solution' => 'Solution 2',
            'required_means' => 'Les moyens 2',
            'created_by' => LoadAdherentData::ADHERENT_6_UUID,
            'created_at' => '2017-10-12 18:34:12',
            'address' => NullablePostAddress::createFrenchAddress('26 Boulevard Louis Guichoux', '13003-13203', null, 43.325543, 5.376076),
            'phone' => '+33673643424',
        ]);
        $citizenProject2->setImageName('default.png');
        $citizenProject2->preApproved();
        $this->addReference('citizen-project-2', $citizenProject2);

        $citizenProject3 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_3_UUID,
            'name' => 'Le projet citoyen à Dammarie-les-Lys',
            'subtitle' => 'Le projet citoyen sans adresse et téléphone',
            'category' => $this->getReference('cpc003'),
            'problem_description' => 'Problème 3',
            'proposed_solution' => 'Solution 3',
            'required_means' => 'Les moyens 3',
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-26 16:08:24',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', null, 43.3256095, 5.374416),
        ]);
        $citizenProject3->addSkill($this->getReference('cps007'));
        $citizenProject3->addSkill($this->getReference('cps008'));
        $citizenProject3->addSkill($this->getReference('cps009'));
        $citizenProject3->setImageName('default.png');
        $citizenProject3->approved('2017-10-27 10:18:33');
        $this->addReference('citizen-project-3', $citizenProject3);

        $citizenProject4 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_4_UUID,
            'subtitle' => 'Encore un projet citoyen',
            'category' => $this->getReference('cpc004'),
            'problem_description' => 'Problème 4',
            'proposed_solution' => 'Solution 4',
            'required_means' => 'Les moyens 4',
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-09-19 07:36:55',
            'name' => 'Massive Open Online Course',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', null, 43.3256095, 5.374416),
        ]);
        $citizenProject4->setImageName('default.png');
        $citizenProject4->approved();
        $this->addReference('citizen-project-4', $citizenProject4);

        $citizenProject5 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_5_UUID,
            'name' => 'Formation en ligne ouverte à tous à Évry',
            'subtitle' => 'Équipe de la formation en ligne ouverte à tous à Évry',
            'category' => $this->getReference('cpc005'),
            'problem_description' => 'Problème 5',
            'proposed_solution' => 'Solution 5',
            'required_means' => 'Les moyens 5',
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-19 11:54:28',
            'address' => NullablePostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', null, 48.6241569, 2.4265995),
            'phone' => '+33673654349',
        ]);
        $citizenProject5->setImageName('default.png');
        $citizenProject5->approved();
        $this->addReference('citizen-project-5', $citizenProject5);

        $citizenProject6 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_6_UUID,
            'name' => 'Formation en ligne ouverte à tous',
            'subtitle' => 'Équipe de la formation en ligne ouverte à tous',
            'category' => $this->getReference('cpc005'),
            'problem_description' => 'Problème 6',
            'proposed_solution' => 'Solution 6',
            'required_means' => 'Les moyens 6',
            'created_by' => LoadAdherentData::ADHERENT_9_UUID,
            'created_at' => '2017-09-18 20:12:33',
            'address' => NullablePostAddress::createFrenchAddress('28 Boulevard Louis Guichoux', '13003-13203', null, 43.32560, 5.376207),
            'phone' => '+33234823644',
        ]);
        $citizenProject6->setImageName('default.png');
        $citizenProject6->approved('2017-10-19 09:17:24');
        $this->addReference('citizen-project-6', $citizenProject6);

        $citizenProject7 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_7_UUID,
            'name' => 'Projet citoyen à Berlin',
            'subtitle' => 'Projet citoyen de nos partenaires Allemands.',
            'category' => $this->getReference('cpc001'),
            'problem_description' => 'Problème 7',
            'proposed_solution' => 'Solution 7',
            'required_means' => 'Les moyens 7',
            'created_by' => LoadAdherentData::ADHERENT_10_UUID,
            'created_at' => '2017-09-18 09:14:45',
            'address' => NullablePostAddress::createFrenchAddress('34 Boulevard Louis Guichoux', '13003-13203', null, 43.325524, 5.376792),
        ]);
        $citizenProject7->setImageName('default.png');
        $this->addReference('citizen-project-7', $citizenProject7);

        $citizenProject8 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_8_UUID,
            'name' => 'En Marche - Projet citoyen',
            'subtitle' => 'Projet citoyen.',
            'category' => $this->getReference('cpc002'),
            'problem_description' => 'Problème 8',
            'proposed_solution' => 'Solution 8',
            'required_means' => 'Les moyens 8',
            'created_by' => LoadAdherentData::ADHERENT_11_UUID,
            'created_at' => '2017-10-10 17:34:18',
            'address' => NullablePostAddress::createFrenchAddress('32 Boulevard Louis Guichoux', '13003-13203', null, 43.325534, 5.376733),
        ]);
        $citizenProject8->setImageName('default.png');
        $citizenProject8->approved('2017-10-10 18:23:18');
        $this->addReference('citizen-project-8', $citizenProject8);

        $citizenProject9 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_9_UUID,
            'name' => 'Projet citoyen à New York City',
            'subtitle' => 'Projet citoyen à New York City.',
            'category' => $this->getReference('cpc003'),
            'problem_description' => 'Problème 3',
            'proposed_solution' => 'Solution 3',
            'required_means' => 'Les moyens 3',
            'created_by' => LoadAdherentData::ADHERENT_12_UUID,
            'created_at' => '2017-10-09 12:16:22',
            'address' => NullablePostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            'district' => 'Brooklyn',
            'phone' => '+12123150100',
        ]);
        $citizenProject9->setImageName('default.png');
        $citizenProject9->approved('2017-10-09 13:27:42');
        $this->addReference('citizen-project-9', $citizenProject9);

        $citizenProject10 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_10_UUID,
            'name' => 'Projet citoyen refusé à Paris 8',
            'subtitle' => 'Le projet citoyen refusé',
            'category' => $this->getReference('cpc001'),
            'problem_description' => 'Problème refusé',
            'proposed_solution' => 'Solution refusée',
            'required_means' => 'Les moyens refusés',
            'created_by' => LoadAdherentData::ADHERENT_3_UUID,
            'created_at' => '2018-09-01 10:22:13',
            'address' => NullablePostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.8705073, 2.3032432),
        ]);
        $citizenProject10->refused('2018-09-09 10:10:10');
        $citizenProject10->setImageName('default.png');
        $this->addReference('citizen-project-10', $citizenProject10);

        $citizenProject11 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_11_UUID,
            'name' => '[En attente] Projet citoyen à New York City',
            'subtitle' => '[En attente] Projet citoyen à New York City.',
            'category' => $this->getReference('cpc003'),
            'problem_description' => 'Problème en attente',
            'proposed_solution' => 'Solution en attente',
            'required_means' => 'Les moyens en attente',
            'created_by' => LoadAdherentData::ADHERENT_12_UUID,
            'created_at' => '2018-09-09 12:12:12',
            'address' => NullablePostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 'New York', 40.7625289, -73.9859927),
            'phone' => '+12123150100',
        ]);
        $citizenProject11->setImageName('default.png');
        $this->addReference('citizen-project-11', $citizenProject11);

        $citizenProject12 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_12_UUID,
            'name' => 'Un stage pour tous',
            'subtitle' => 'Aider les collégiens à trouver un stage même sans réseau',
            'category' => $this->getReference('cpc002'),
            'problem_description' => 'Les collégiens ont parfois des difficultés à trouver un stage de découverte par manque de relations, de réseau.',
            'proposed_solution' => 'Le projet a pour objectif de mettre en relation ces élèves avec un réseau de professionnels volontaires pour les accueillir.',
            'required_means' => 'Les moyens en attente',
            'created_by' => LoadAdherentData::ADHERENT_11_UUID,
            'created_at' => '2018-09-19 18:34:18',
            'address' => NullablePostAddress::createFrenchAddress('32 Boulevard Louis Guichoux', '13003-13203', null, 43.325534, 5.376733),
            'turnkey_project' => $this->getReference('turnkey-project-education'),
        ]);
        $citizenProject12->setImageName('default.png');
        $citizenProject12->approved('2018-09-19 19:34:18');
        $this->addReference('citizen-project-12', $citizenProject12);

        $citizenProject13 = $this->citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_13_UUID,
            'name' => 'Un stage pour tous',
            'subtitle' => 'Aider les collégiens à trouver un stage même sans réseau',
            'category' => $this->getReference('cpc002'),
            'problem_description' => 'Les collégiens ont parfois des difficultés à trouver un stage de découverte par manque de relations, de réseau.',
            'proposed_solution' => 'Le projet a pour objectif de mettre en relation ces élèves avec un réseau de professionnels volontaires pour les accueillir.',
            'required_means' => 'Les moyens en attente',
            'created_by' => LoadAdherentData::ADHERENT_9_UUID,
            'created_at' => '2018-09-19 18:34:18',
            'address' => NullablePostAddress::createFrenchAddress('32 Boulevard Louis Guichoux', '13003-13203', null, 43.325534, 5.376733),
            'turnkey_project' => $this->getReference('turnkey-project-education'),
        ]);
        $citizenProject13->setImageName('default.png');
        $this->addReference('citizen-project-13', $citizenProject13);

        $manager->persist($citizenProject1);
        $manager->persist($citizenProject2);
        $manager->persist($citizenProject3);
        $manager->persist($citizenProject4);
        $manager->persist($citizenProject5);
        $manager->persist($citizenProject6);
        $manager->persist($citizenProject7);
        $manager->persist($citizenProject8);
        $manager->persist($citizenProject9);
        $manager->persist($citizenProject10);
        $manager->persist($citizenProject11);
        $manager->persist($citizenProject12);
        $manager->persist($citizenProject13);

        // Make adherents join citizen projects
        $manager->persist($this->getReference('adherent-3')->administrateCitizenProject($citizenProject1, '2017-10-12 17:25:54'));
        $manager->persist($this->getReference('adherent-2')->followCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-4')->followCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-5')->followCitizenProject($citizenProject1));

        $manager->persist($this->getReference('adherent-6')->followCitizenProject($citizenProject2));

        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject3, '2017-10-26 17:08:24'));
        $manager->persist($this->getReference('adherent-3')->administrateCitizenProject($citizenProject3));

        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject4));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject4));

        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-9')->followCitizenProject($citizenProject5));

        $manager->persist($this->getReference('adherent-9')->administrateCitizenProject($citizenProject6));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject6));

        $manager->persist($this->getReference('adherent-10')->administrateCitizenProject($citizenProject7));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject7));

        $manager->persist($this->getReference('adherent-11')->administrateCitizenProject($citizenProject8));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject8));

        $manager->persist($this->getReference('adherent-12')->administrateCitizenProject($citizenProject9));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject9));
        $manager->persist($this->getReference('adherent-11')->followCitizenProject($citizenProject9));
        $manager->persist($this->getReference('adherent-13')->followCitizenProject($citizenProject9));

        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject10));

        $manager->persist($this->getReference('adherent-12')->followCitizenProject($citizenProject11));

        $manager->persist($this->getReference('adherent-11')->administrateCitizenProject($citizenProject12));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject12));

        $manager->persist($this->getReference('adherent-9')->administrateCitizenProject($citizenProject13));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeData::class,
            LoadCitizenProjectCategoryData::class,
            LoadCitizenProjectSkillData::class,
            LoadTurnkeyProjectData::class,
        ];
    }
}
