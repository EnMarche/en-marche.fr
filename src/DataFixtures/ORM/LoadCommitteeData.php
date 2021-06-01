<?php

namespace App\DataFixtures\ORM;

use App\Committee\CommitteeFactory;
use App\DataFixtures\AutoIncrementResetter;
use App\Entity\CommitteeElection;
use App\Entity\PostAddress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommitteeData extends Fixture implements DependentFixtureInterface
{
    public const COMMITTEE_1_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';
    public const COMMITTEE_2_UUID = '182d8586-8b05-4b70-a727-704fa701e816';
    public const COMMITTEE_3_UUID = 'b0cd0e52-a5a4-410b-bba3-37afdd326a0a';
    public const COMMITTEE_4_UUID = 'd648d486-fbb3-4394-b4b3-016fac3658af';
    public const COMMITTEE_5_UUID = '464d4c23-cf4c-4d3a-8674-a43910da6419';
    public const COMMITTEE_6_UUID = '508d4ac0-27d6-4635-8953-4cc8600018f9';
    public const COMMITTEE_7_UUID = '40b6e2e5-2499-438b-93ab-ef08860a1845';
    public const COMMITTEE_8_UUID = '93b72179-7d27-40c4-948c-5188aaf264b6';
    public const COMMITTEE_9_UUID = '62ea97e7-6662-427b-b90a-23429136d0dd';
    public const COMMITTEE_10_UUID = '79638242-5101-11e7-b114-b2f933d5fe66';
    public const COMMITTEE_11_UUID = 'eb050b5e-9444-49ec-b3dc-005c98024507';
    public const COMMITTEE_12_UUID = '138140a5-1dd2-11b2-88c6-671b351502ee';
    public const COMMITTEE_13_UUID = '1381405c-1dd2-11b2-9a2f-bc94782bb639';
    public const COMMITTEE_14_UUID = 'bb256335-aa42-134a-8fba-525d3ea32b7d';
    public const COMMITTEE_15_UUID = '13814081-1dd2-11b2-abfc-9a31f72792e5';
    public const COMMITTEE_16_UUID = '9640c5fc-c904-428f-8a79-2d90e555478a';

    private $committeeFactory;

    public function __construct(CommitteeFactory $committeeFactory)
    {
        $this->committeeFactory = $committeeFactory;
    }

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'committees');

        // Create some default committees and make people join them
        $committee1 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_1_UUID,
            'created_by' => LoadAdherentData::ADHERENT_3_UUID,
            'created_at' => '2017-01-12 13:25:54',
            'name' => 'En Marche Paris 8',
            'slug' => 'en-marche-paris-8',
            'description' => 'Le comité « En Marche ! » des habitants du 8ème arrondissement de Paris.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.8705073, 2.3132432),
            'phone' => '+33187264236',
            'facebook_page_url' => 'https://facebook.com/enmarche-paris-8',
            'twitter_nickname' => 'enmarche75008',
        ]);
        $committee1->approved('2017-01-12 15:54:18');
        $committee1->addElection(new CommitteeElection($this->getReference('designation-3')));
        $committee1->addElection(new CommitteeElection($this->getReference('designation-4')));
        $this->addReference('committee-1', $committee1);

        $committee2 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_2_UUID,
            'created_by' => LoadAdherentData::ADHERENT_6_UUID,
            'created_at' => '2017-01-12 19:34:12',
            'name' => 'En Marche Marseille 3',
            'description' => "En Marche ! C'est aussi à Marseille !",
            'address' => PostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', null, 43.3256095, 5.374416),
            'phone' => '+33673643424',
        ]);
        $this->addReference('committee-2', $committee2);

        $committee3 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_3_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-01-26 16:08:24',
            'name' => 'En Marche Dammarie-les-Lys',
            'slug' => 'en-marche-dammarie-les-lys',
            'description' => 'Les jeunes avec En Marche !',
            'address' => PostAddress::createFrenchAddress('826 Avenue du Lys', '77190-77152', null, 48.5182194, 2.6220158),
            'phone' => '+33673654349',
            'name_locked' => true,
        ]);
        $committee3->approved('2017-01-27 09:18:33');
        $committee3->addElection(new CommitteeElection($this->getReference('designation-3')));
        $this->addReference('committee-3', $committee3);

        $committee4 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_4_UUID,
            'created_by' => LoadAdherentData::ADHERENT_4_UUID,
            'created_at' => '2017-01-19 08:36:55',
            'name' => 'Antenne En Marche de Fontainebleau',
            'description' => 'Vous êtes Bellifontain ? Nous aussi ! Rejoignez-nous !',
            'address' => PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.4047652, 2.6987591),
            'phone' => '+33673654349',
        ]);
        $committee4->approved('-35 days');
        $committee4->setCurrentElection(new CommitteeElection($this->getReference('designation-3')));
        $this->addReference('committee-4', $committee4);

        $committee5 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_5_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-01-19 10:54:28',
            'name' => 'En Marche - Comité de Évry',
            'description' => 'En Marche pour une nouvelle vision, du renouveau pour la France.',
            'address' => PostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', null, 48.6241569, 2.4265995),
            'phone' => '+33673654349',
        ]);
        $committee5->approved();
        $committee5->setCurrentElection(new CommitteeElection($this->getReference('designation-2')));
        $this->addReference('committee-5', $committee5);

        $committee6 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_6_UUID,
            'created_by' => LoadAdherentData::ADHERENT_9_UUID,
            'created_at' => '2017-03-18 20:12:33',
            'name' => 'En Marche - Comité de Rouen',
            'description' => 'En Marche pour la France et la ville de Rouen.',
            'address' => PostAddress::createFrenchAddress('2 Place du Général de Gaulle', '76000-76540', null, 49.443232, 1.099971),
            'phone' => '+33234823644',
        ]);
        $committee6->approved('2017-03-19 09:17:24');
        $committee6->setCurrentElection(new CommitteeElection($this->getReference('designation-1')));
        $this->addReference('committee-6', $committee6);

        $committee7 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_7_UUID,
            'created_by' => LoadAdherentData::ADHERENT_10_UUID,
            'created_at' => '2017-03-19 08:14:45',
            'name' => 'En Marche - Comité de Berlin',
            'description' => 'En Marche pour la France et nos partenaires Allemands.',
            'address' => PostAddress::createForeignAddress('DE', '10369', 'Berlin', '7 Hohenschönhauser Str.', null, 52.5330939, 13.4662418),
            'phone' => '+492211653540',
        ]);
        $committee7->approved('2017-03-19 13:43:26');
        $committee7->setCurrentElection(new CommitteeElection($this->getReference('designation-1')));
        $this->addReference('committee-7', $committee7);

        $committee8 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_8_UUID,
            'created_by' => LoadAdherentData::ADHERENT_11_UUID,
            'created_at' => '2017-04-10 17:34:18',
            'name' => 'En Marche - Comité de Singapour',
            'description' => 'En Marche pour la France mais depuis Singapour.',
            'address' => PostAddress::createForeignAddress('SG', '368645', 'Singapour', '47 Jln Mulia', null, 1.3329126, 103.8795163),
            'phone' => '+6566888868',
        ]);
        $committee8->approved('2017-04-10 20:23:18');
        $committee8->setCurrentElection(new CommitteeElection($this->getReference('designation-5')));
        $this->addReference('committee-8', $committee8);

        $committee9 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_9_UUID,
            'created_by' => LoadAdherentData::ADHERENT_12_UUID,
            'created_at' => '2017-04-09 12:16:22',
            'name' => 'En Marche - Comité de New York City',
            'description' => 'Les expats sont en En Marche.',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', null, 40.7625289, -73.9859927),
            'phone' => '+12123150100',
        ]);
        $committee9->approved('2017-04-09 13:27:42');
        $committee9->setCurrentElection(new CommitteeElection($this->getReference('designation-5')));
        $this->addReference('committee-9', $committee9);

        $committee10 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_10_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2021-01-02 12:18:22',
            'name' => 'En Marche - Suisse',
            'description' => 'En Marche pour la France et nos partenaires en Suisse.',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'phone' => '+33673654349',
        ]);
        $committee10->approved('2021-01-02 13:17:42');
        $committee10->setCurrentElection(new CommitteeElection($this->getReference('designation-5')));
        $this->addReference('committee-10', $committee10);

        $committee11 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_11_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-05-12 12:18:22',
            'name' => 'En Marche - Suisse refused',
            'description' => 'En Marche pour la France et nos partenaires en Suisse. (refused)',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'phone' => '+33673654567',
        ]);
        $committee11->approved('2017-05-13 13:17:42');
        $committee11->refused('2017-05-14 13:17:42');
        $this->addReference('committee-11', $committee11);

        $committee12 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_12_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Allemagne',
            'description' => 'En Marche Allemagne.',
            'address' => PostAddress::createForeignAddress('DE', '10789', 'Berlin', 'Breitscheidplatz', null, 52.5065133, 13.1445545),
            'phone' => '+33673654349',
        ]);
        $committee12->approved('2020-10-29 12:00:00');
        $committee12->setCurrentElection(new CommitteeElection($this->getReference('designation-5')));
        $this->addReference('committee-12', $committee12);

        $committee13 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_13_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Prague',
            'description' => 'En Marche Prague',
            'address' => PostAddress::createForeignAddress('CZ', '12000', 'Prague', 'Vinohradská 17-11', null, 50.078647, 14.434630),
            'phone' => '+33673654349',
        ]);
        $committee13->approved('2020-10-29 12:00:00');
        $committee13->setCurrentElection(new CommitteeElection($this->getReference('designation-8')));
        $this->addReference('committee-13', $committee13);

        $committee14 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_14_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Allemagne 2',
            'description' => 'En Marche Allemagne.',
            'address' => PostAddress::createForeignAddress('DE', '10789', 'Berlin', 'Breitscheidplatz', null, 52.5065133, 13.1445545),
            'phone' => '+33673654349',
        ]);
        $committee14->approved('2020-10-29 12:00:00');
        $committee14->setCurrentElection(new CommitteeElection($this->getReference('designation-8')));
        $this->addReference('committee-14', $committee14);

        $committee15 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_15_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Allemagne 3',
            'description' => 'En Marche Allemagne.',
            'address' => PostAddress::createForeignAddress('DE', '10789', 'Berlin', 'Breitscheidplatz', null, 52.5065133, 13.1445545),
            'phone' => '+33673654349',
        ]);
        $committee15->approved('2020-10-29 12:00:00');
        $committee15->setCurrentElection(new CommitteeElection($this->getReference('designation-9')));
        $this->addReference('committee-15', $committee15);

        $committee16 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_16_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2021-01-03 09:00:00',
            'name' => 'Une nouvelle demande',
            'description' => 'Nouveau dans l\'année 2021',
            'address' => PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', null, 48.5182194, 2.6220158),
            'phone' => '+33673654349',
        ]);
        $this->addReference('committee-16', $committee16);

        $manager->persist($committee1);
        $manager->persist($committee2);
        $manager->persist($committee3);
        $manager->persist($committee4);
        $manager->persist($committee5);
        $manager->persist($committee6);
        $manager->persist($committee7);
        $manager->persist($committee8);
        $manager->persist($committee9);
        $manager->persist($committee10);
        $manager->persist($committee11);
        $manager->persist($committee12);
        $manager->persist($committee13);
        $manager->persist($committee14);
        $manager->persist($committee15);
        $manager->persist($committee16);

        // Make adherents join committees
        $adherent2 = $this->getReference('adherent-2');
        $adherent3 = $this->getReference('adherent-3');
        $adherent4 = $this->getReference('adherent-4');
        $adherent5 = $this->getReference('adherent-5');
        $adherent6 = $this->getReference('adherent-6');
        $adherent7 = $this->getReference('adherent-7');
        $adherent9 = $this->getReference('adherent-9');
        $adherent10 = $this->getReference('adherent-10');
        $adherent11 = $this->getReference('adherent-11');
        $adherent12 = $this->getReference('adherent-12');
        $adherent13 = $this->getReference('adherent-13');
        $adherent14 = $this->getReference('adherent-14');
        $adherent19 = $this->getReference('adherent-19');
        $adherent20 = $this->getReference('senatorial-candidate');
        $adherent21 = $this->getReference('municipal-manager-1');
        $adherent22 = $this->getReference('adherent-20');
        $adherent23 = $this->getReference('adherent-49');
        $referent = $this->getReference('adherent-8');
        $assessor = $this->getReference('assessor-1');

        // Committee 1
        $manager->persist($membership = $adherent3->followCommittee($committee1, new \DateTime('2017-01-12 13:25:54')));
        $membership->enableVote();
        $manager->persist($membership = $adherent5->hostCommittee($committee1));
        $membership->enableVote();
        $manager->persist($adherent2->followCommittee($committee1));
        $manager->persist($adherent4->followCommittee($committee1));

        // Committee 2
        $committee2->addProvisionalSupervisor($adherent6);
        $manager->persist($adherent6->followCommittee($committee2));
        $manager->persist($adherent4->followCommittee($committee2));

        // Committee 3
        $manager->persist($membership = $adherent7->followCommittee($committee3, new \DateTime('2017-01-26 16:08:24')));
        $membership->enableVote();
        $manager->persist($adherent3->hostCommittee($committee3));
        $manager->persist($adherent5->followCommittee($committee3));

        // Committee 4
        $manager->persist($adherent7->followCommittee($committee4));
        $manager->persist($adherent3->followCommittee($committee4));
        $manager->persist($adherent19->followCommittee($committee4));

        // Committee 5
        $manager->persist($adherent7->followCommittee($committee5));
        $manager->persist($adherent5->followCommittee($committee16, new \DateTime('-2 months')));
        $manager->persist($adherent3->followCommittee($committee5));
        $manager->persist($adherent22->followCommittee($committee5));
        $manager->persist($adherent9->followCommittee($committee5));
        $manager->persist($assessor->followCommittee($committee5));
        $manager->persist($membership = $adherent19->followCommittee($committee5));
        $membership->enableVote();

        // Committee 6
        $manager->persist($membership = $adherent9->followCommittee($committee6));
        $membership->enableVote();
        $manager->persist($adherent2->followCommittee($committee6));
        $manager->persist($adherent3->followCommittee($committee6));
        $manager->persist($assessor->followCommittee($committee6));

        // Committee 7
        $manager->persist($membership = $adherent10->followCommittee($committee7));
        $membership->enableVote();
        $manager->persist($membership = $adherent20->followCommittee($committee7));
        $membership->enableVote();
        $manager->persist($adherent3->followCommittee($committee7));
        $manager->persist($assessor->followCommittee($committee7, new \DateTime('-2 months')));

        // Committee 8
        $manager->persist($membership = $adherent11->followCommittee($committee8));
        $membership->enableVote();
        $manager->persist($adherent3->followCommittee($committee8));
        $manager->persist($assessor->followCommittee($committee8));

        // Committee 9
        $manager->persist($membership = $adherent12->followCommittee($committee9));
        $membership->enableVote();
        $manager->persist($adherent3->followCommittee($committee9));
        $manager->persist($adherent11->followCommittee($committee9));

        // Committee 10
        $manager->persist($membership = $referent->followCommittee($committee10));
        $membership->enableVote();
        $manager->persist($adherent13->followCommittee($committee10));
        $manager->persist($adherent14->followCommittee($committee10));

        // Committee 11
        $committee11->addProvisionalSupervisor($adherent13);
        $manager->persist($adherent13->followCommittee($committee11));
        $manager->persist($adherent14->followCommittee($committee11));

        // Committee 12
        $manager->persist($adherent10->hostCommittee($committee12, new \DateTime('-2 months')));
        $manager->persist($adherent20->followCommittee($committee12, new \DateTime('-2 months')));
        $manager->persist($adherent23->followCommittee($committee12, new \DateTime('-2 months')));
        $manager->persist($adherent13->followCommittee($committee12));
        $manager->persist($adherent14->followCommittee($committee12));
        $manager->persist($adherent21->followCommittee($committee12, new \DateTime('-2 months')));

        // Committee 13
        $manager->persist($this->getReference('adherent-21')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-22')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-23')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-24')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-25')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-26')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-27')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-28')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-29')->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-30')->followCommittee($committee13, new \DateTime('-2 months')));
        $adherent31 = $this->getReference('adherent-31');
        $manager->persist($adherent31->followCommittee($committee13, new \DateTime('-2 months')));

        // Committee 14
        $manager->persist($this->getReference('adherent-21')->followCommittee($committee14, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-29')->followCommittee($committee14, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-30')->followCommittee($committee14, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-31')->followCommittee($committee14, new \DateTime('-2 months')));

        // Committee 15
        $adherent32 = $this->getReference('adherent-32');
        $manager->persist($adherent32->followCommittee($committee15, new \DateTime('-2 months')));
        foreach (range(33, 50) as $index) {
            $manager->persist($this->getReference('adherent-'.$index)->followCommittee($committee15, new \DateTime('-2 months')));
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadDesignationData::class,
        ];
    }
}
