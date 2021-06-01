<?php

namespace App\DataFixtures\ORM;

use App\Address\Address;
use App\Coordinator\CoordinatorAreaSectors;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentCharter\CandidateCharter;
use App\Entity\AdherentCharter\CommitteeHostCharter;
use App\Entity\AdherentCharter\MunicipalChiefCharter;
use App\Entity\AdherentCharter\ReferentCharter;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\AssessorRoleAssociation;
use App\Entity\BoardMember\BoardMember;
use App\Entity\Coalition\CoalitionModeratorRoleAssociation;
use App\Entity\CoordinatorManagedArea;
use App\Entity\LreArea;
use App\Entity\ManagedArea\CandidateManagedArea;
use App\Entity\MunicipalChiefManagedArea;
use App\Entity\MunicipalManagerRoleAssociation;
use App\Entity\MunicipalManagerSupervisorRole;
use App\Entity\PostAddress;
use App\Entity\ReferentTeamMember;
use App\Entity\SenatorArea;
use App\Entity\SenatorialCandidateManagedArea;
use App\Jecoute\GenderEnum;
use App\Membership\ActivityPositions;
use App\Membership\AdherentFactory;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadAdherentData extends Fixture implements DependentFixtureInterface
{
    public const ADHERENT_1_UUID = '313bd28f-efc8-57c9-8ab7-2106c8be9697';
    public const ADHERENT_2_UUID = 'e6977a4d-2646-5f6c-9c82-88e58dca8458';
    public const ADHERENT_3_UUID = 'a046adbe-9c7b-56a9-a676-6151a6785dda';
    public const ADHERENT_4_UUID = '29461c49-6316-5be1-9ac3-17816bf2d819';
    public const ADHERENT_5_UUID = 'b4219d47-3138-5efd-9762-2ef9f9495084';
    public const ADHERENT_6_UUID = 'acc73b03-9743-47d8-99db-5a6c6f55ad67';
    public const ADHERENT_7_UUID = 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4';
    public const ADHERENT_9_UUID = '93de5d98-383a-4863-9f47-eb7a348873a8';
    public const ADHERENT_10_UUID = 'd4b1e7e1-ba18-42a9-ace9-316440b30fa7';
    public const ADHERENT_11_UUID = 'f458cc73-3678-4bd0-8e2f-d1c83be3a7e1';
    public const ADHERENT_12_UUID = 'cd76b8cf-af20-4976-8dd9-eb067a2f30c7';
    public const ADHERENT_13_UUID = '46ab0600-b5a0-59fc-83a7-cc23ca459ca0';
    public const ADHERENT_14_UUID = '511c21bf-1240-5271-abaa-3393d3f40740';
    public const ADHERENT_15_UUID = '0a68eb57-c88a-5f34-9e9d-27f85e68af4f';
    public const ADHERENT_16_UUID = '25e75e2f-2f73-4f51-8542-bd511ba6a945';
    public const ADHERENT_17_UUID = '69fcc468-598a-49ac-a651-d4d3ee856446';
    public const ADHERENT_18_UUID = 'a2acfbda-6d5d-4614-b96a-ba00ab6fc7ee';
    public const ADHERENT_19_UUID = '1529f096-12d7-42bb-8c98-a4966a730e2a';
    public const COORDINATOR_1_UUID = 'd72d88ee-44bf-5059-bd19-02af28f0c7dc';
    public const COORDINATOR_2_UUID = '1ebee762-4dc1-42f6-9884-1c83ba9c6d71';
    public const REFERENT_1_UUID = '29461c49-2646-4d89-9c82-50b3f9b586f4';
    public const REFERENT_2_UUID = '2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf';
    public const REFERENT_3_UUID = 'e1bee762-4dc1-42f6-9884-1c83ba9c6d17';
    public const DEPUTY_1_UUID = '918f07e5-676b-49c0-b76d-72ce01cb2404';
    public const DEPUTY_2_UUID = 'ccd87fb0-7d98-433f-81e1-3dd8b14f79c0';
    public const DEPUTY_3_UUID = '160cdf45-80c4-4663-aa21-0ae23091a381';
    public const SENATOR_UUID = '021268fe-d4b3-44a7-bce9-c001191249a7';
    public const ASSESSOR_UUID = 'ae341e67-6e4c-4ead-b4be-1ade6693d512';
    public const MUNICIPAL_MANAGER_UUID = 'c2ba1ce4-e103-415f-a67a-260b8c651b55';
    public const SENATORIAL_CANDIDATE_UUID = 'ab03c939-8f70-40a8-b2cd-d147ec7fd09e';

    public const MUNICIPAL_CHIEF_1_UUID = '15d9154e-22d0-45f4-9b82-7f383342a3b8';
    public const MUNICIPAL_CHIEF_2_UUID = 'bdc66cc7-ddf0-4406-b76a-447acb1594ab';
    public const MUNICIPAL_CHIEF_3_UUID = '991e29ff-0333-4a30-a228-067ac5bbe6a9';

    public const DEFAULT_PASSWORD = 'secret!12345';

    private $adherentFactory;

    public function __construct(AdherentFactory $adherentFactory)
    {
        $this->adherentFactory = $adherentFactory;
    }

    public function load(ObjectManager $manager)
    {
        // Create adherent users list
        $adherent1 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'michelle.dufour@example.ch',
            'gender' => 'female',
            'first_name' => 'Michelle',
            'last_name' => 'Dufour',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'birthdate' => '1972-11-23',
        ]);
        $adherent1->addReferentTag($this->getReference('referent_tag_ch'));
        $adherent1->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'));
        $adherent1->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $this->addReference('adherent-1', $adherent1);

        $adherent2 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'carl999@example.fr',
            'gender' => 'male',
            'nickname' => 'pont',
            'nationality' => 'FR',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', null, 45.570898, 5.927206),
            'birthdate' => '1950-07-08',
            'position' => 'retired',
            'phone' => '+33111223344',
            'registered_at' => '2016-11-16 20:45:33',
        ]);
        $adherent2->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent2->removeSubscriptionType($this->getReference('st-'.SubscriptionTypeEnum::LOCAL_HOST_EMAIL));

        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent2->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent2->addReferentTag($this->getReference('referent_tag_73'));
        $adherent2->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_73'));
        $this->addReference('adherent-2', $adherent2);

        $adherent3 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'jacques.picard@en-marche.fr',
            'gender' => 'male',
            'nickname' => 'kikouslove',
            'nationality' => 'FR',
            'nickname_used' => true,
            'first_name' => 'Jacques',
            'last_name' => 'Picard',
            'address' => PostAddress::createFrenchAddress('36 rue de la Paix', '75008-75108', null, 48.8699464, 2.3297187),
            'birthdate' => '1953-04-03',
            'position' => 'retired',
            'phone' => '+33187264236',
            'registered_at' => '2017-01-03 08:47:54',
        ]);
        $adherent3->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent3->addReferentTag($this->getReference('referent_tag_75'));
        $adherent3->addReferentTag($this->getReference('referent_tag_75008'));
        $adherent3->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'));
        $adherent3->addTag($this->getReference('adherent_tag_at007'));
        $adherent3->setCandidateManagedArea($candidateManagedAreaRegion = new CandidateManagedArea());
        $candidateManagedAreaRegion->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_region_11'));
        $adherent3->addCharter(new CandidateCharter());
        $adherent3->addCharter(new CommitteeHostCharter());
        $adherent3->setCoalitionModeratorRole(new CoalitionModeratorRoleAssociation());
        $adherent3->certify();
        $this->addReference('adherent-3', $adherent3);

        $adherent4 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_4_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'luciole1989@spambox.fr',
            'gender' => 'female',
            'nationality' => 'FR',
            'first_name' => 'Lucie',
            'last_name' => 'Olivera',
            'address' => PostAddress::createFrenchAddress('13 boulevard des Italiens', '75009-75109', null, 48.8713224, 2.3353755),
            'birthdate' => '1989-09-17',
            'position' => 'student',
            'phone' => '+33727363643',
            'registered_at' => '2017-01-18 13:15:28',
        ]);
        $adherent4->setPosition(ActivityPositions::UNEMPLOYED);
        $adherent4->setInterests(['jeunesse']);
        $adherent4->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent4->removeSubscriptionTypeByCode(SubscriptionTypeEnum::DEPUTY_EMAIL);
        $adherent4->setProcurationManagedAreaCodesAsString('75, 44, GB, 92130, 91300');
        $adherent4->addReferentTag($this->getReference('referent_tag_75'));
        $adherent4->addReferentTag($this->getReference('referent_tag_75009'));
        $adherent4->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'));
        $zoneDpt92 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_92');
        $candidateManagedAreaDpt = new CandidateManagedArea();
        $candidateManagedAreaDpt->setZone($zoneDpt92);
        $adherent4->addZone($zoneDpt92);
        $adherent4->setCandidateManagedArea($candidateManagedAreaDpt);
        $adherent4->addCharter(new CandidateCharter());
        $this->addReference('adherent-4', $adherent4);

        $adherent5 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_5_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'gisele-berthoux@caramail.com',
            'gender' => 'female',
            'nationality' => 'FR',
            'first_name' => 'Gisele',
            'last_name' => 'Berthoux',
            'address' => PostAddress::createFrenchAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684),
            'birthdate' => '1983-12-24',
            'position' => 'unemployed',
            'phone' => '+33138764334',
            'registered_at' => '2017-01-08 05:55:43',
        ]);
        $adherent5->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent5->removeSubscriptionTypeByCode(SubscriptionTypeEnum::CANDIDATE_EMAIL);
        $adherent5->removeSubscriptionTypeByCode(SubscriptionTypeEnum::REFERENT_EMAIL);
        $adherent5->addSubscriptionType($this->getReference('st-militant_action_sms'));
        $adherent5->setCoalitionsCguAccepted(true);
        $adherent5->addReferentTag($this->getReference('referent_tag_92'));
        $adherent5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'));
        $adherent5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $adherent5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_92-4'));
        $adherent5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_canton_9209'));
        $adherent5->addCharter(new CandidateCharter());
        $adherent5->addCharter(new CommitteeHostCharter());
        $this->addReference('adherent-5', $adherent5);

        $adherent6 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_6_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'benjyd@aol.com',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Benjamin',
            'last_name' => 'Duroc',
            'address' => PostAddress::createFrenchAddress('39 rue de Crimée', '13003-13203', null, 43.3062866, 5.3791498),
            'birthdate' => '1987-02-08',
            'position' => 'employed',
            'phone' => '+33673643424',
            'registered_at' => '2017-01-16 18:33:22',
        ]);
        $adherent6->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent6->setCoalitionSubscription(true);
        $adherent6->setCoalitionsCguAccepted(true);
        $adherent6->addTag($this->getReference('adherent_tag_at001'));
        $adherent6->addTag($this->getReference('adherent_tag_at002'));
        $adherent6->addTag($this->getReference('adherent_tag_at003'));
        $adherent6->addTag($this->getReference('adherent_tag_at007'));
        $adherent6->addReferentTag($this->getReference('referent_tag_13'));
        $adherent6->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_13055'));
        $adherent6->certify();
        $this->addReference('adherent-6', $adherent6);

        $adherent7 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_7_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'francis.brioul@yahoo.com',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Francis',
            'last_name' => 'Brioul',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-01-07',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent7->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent7->addReferentTag($this->getReference('referent_tag_77'));
        $adherent7->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));
        $zoneCanton7711 = LoadGeoZoneData::getZoneReference($manager, 'zone_canton_7711');
        $candidateManagedAreaCanton = new CandidateManagedArea();
        $candidateManagedAreaCanton->setZone($zoneCanton7711);
        $adherent7->setCandidateManagedArea($candidateManagedAreaCanton);
        $adherent7->addCharter(new CommitteeHostCharter());
        $this->addReference('adherent-7', $adherent7);

        $adherent9 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_9_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'laura@deloche.com',
            'gender' => 'female',
            'nationality' => 'FR',
            'first_name' => 'Laura',
            'last_name' => 'Deloche',
            'address' => PostAddress::createFrenchAddress('2 Place du Général de Gaulle', '76000-76540', null, 49.443232, 1.099971),
            'birthdate' => '1973-04-11',
            'position' => 'employed',
            'phone' => '+33234823644',
            'registered_at' => '2017-02-16 17:12:08',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent9->setBoardMember(BoardMember::AREA_FRANCE_METROPOLITAN, $roles);
        $adherent9->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent9->addReferentTag($this->getReference('referent_tag_76'));
        $adherent9->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'));
        $adherent9->addCharter(new CommitteeHostCharter());
        $adherent9->certify();
        $this->addReference('adherent-9', $adherent9);

        $adherent10 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_10_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'martine.lindt@gmail.com',
            'gender' => 'female',
            'nationality' => 'DE',
            'first_name' => 'Martine',
            'last_name' => 'Lindt',
            'address' => PostAddress::createForeignAddress('DE', '10369', 'Berlin', '7 Hohenschönhauser Str.', null, 52.5330939, 13.4662418),
            'birthdate' => '2000-11-14',
            'position' => 'student',
            'phone' => '+492211653540',
            'registered_at' => '2017-02-23 13:56:12',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent10->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent10->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent10->addReferentTag($this->getReference('referent_tag_de'));
        $adherent10->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_DE'));
        $this->addReference('adherent-10', $adherent10);

        $adherent11 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_11_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'lolodie.dutemps@hotnix.tld',
            'gender' => 'female',
            'nationality' => 'SG',
            'first_name' => 'Élodie',
            'last_name' => 'Dutemps',
            'address' => PostAddress::createForeignAddress('SG', '368645', 'Singapour', '47 Jln Mulia', null, 1.3329126, 103.8795163),
            'birthdate' => '2004-07-13',
            'position' => 'employed',
            'phone' => '+6566888868',
            'registered_at' => '2017-04-10 14:08:12',
        ]);
        $adherent11->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent11->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent11->addReferentTag($this->getReference('referent_tag_sg'));
        $adherent11->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_SG'));
        $this->addReference('adherent-11', $adherent11);

        $adherent12 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_12_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'kiroule.p@blabla.tld',
            'gender' => 'male',
            'nationality' => 'US',
            'first_name' => 'Pierre',
            'last_name' => 'Kiroule',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', null, 40.7625289, -73.9859927),
            'birthdate' => '1964-10-02',
            'position' => 'employed',
            'phone' => '+12123150100',
            'registered_at' => '2017-04-09 06:20:38',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent12->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent12->getBoardMember()->addSavedBoardMember($adherent11->getBoardMember());
        $adherent12->getBoardMember()->addSavedBoardMember($adherent10->getBoardMember());
        $adherent12->getBoardMember()->addSavedBoardMember($adherent9->getBoardMember());
        $adherent12->getBoardMember()->addSavedBoardMember($adherent2->getBoardMember());
        $adherent12->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent12->addReferentTag($this->getReference('referent_tag_us'));
        $adherent12->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_US'));
        $this->addReference('adherent-12', $adherent12);

        $adherent13 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_13_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'michel.vasseur@example.ch',
            'gender' => 'male',
            'first_name' => 'Michel',
            'last_name' => 'VASSEUR',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '12 Pilgerweg', null, 47.321569, 8.549968799999988),
            'birthdate' => '1987-05-13',
        ]);
        $adherent13->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent13->addReferentTag($this->getReference('referent_tag_ch'));
        $adherent13->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'));
        $adherent13->setMandates(['european_deputy']);
        $adherent13->addHandledThematicCommunity($this->getReference('tc-sante'));
        $adherent13->addHandledThematicCommunity($this->getReference('tc-education'));
        $lreArea = new LreArea();
        $lreArea->setAllTags(true);
        $adherent13->setLreArea($lreArea);
        $adherent13->certify();
        $this->addReference('adherent-13', $adherent13);

        $adherent14 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_14_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'damien.schmidt@example.ch',
            'gender' => 'male',
            'first_name' => 'Damien',
            'last_name' => 'SCHMIDT',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', 'Seestrasse 204', null, 47.3180696, 8.552615),
            'birthdate' => '1988-04-13',
            'phone' => '+33111223345',
        ]);
        $adherent14->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent14->addReferentTag($this->getReference('referent_tag_ch'));
        $adherent14->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'));
        $adherent14->certify();
        $this->addReference('adherent-14', $adherent14);

        // Non activated, enabled adherent
        $adherent15 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_15_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'thomas.leclerc@example.ch',
            'gender' => 'male',
            'first_name' => 'Thomas',
            'last_name' => 'Leclerc',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'birthdate' => '1982-05-12',
            'registered_at' => '2017-04-09 06:20:38',
        ]);
        $adherent15->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent15->setStatus(Adherent::ENABLED);
        $adherent15->addReferentTag($this->getReference('referent_tag_ch'));
        $adherent15->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'));
        $this->addReference('adherent-15', $adherent15);

        $adherent16 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_16_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'commissaire.biales@example.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Patrick',
            'last_name' => 'Bialès',
            'address' => PostAddress::createFrenchAddress('26 Rue Louis Blanc', '75000-75010', null, 50.649561, 3.0644126),
            'birthdate' => '1950-07-25',
            'position' => 'commissioner',
            'phone' => '+33712345678',
            'registered_at' => '1994-03-09 00:00:00',
        ]);
        $adherent16->setPosition(ActivityPositions::EMPLOYED);
        $adherent16->setAssessorManagedAreaCodesAsString('93, 59, GB');

        $adherent17 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_17_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'jean-claude.dusse@example.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Jean-Claude',
            'last_name' => 'Dusse',
            'address' => PostAddress::createFrenchAddress('13 Avenue du Peuple Belge', '59000-59350', null, 50.6420374, 3.0630445),
            'birthdate' => '1952-04-16',
            'position' => 'commissioner',
            'phone' => '+33712345678',
            'registered_at' => '1994-03-09 00:00:00',
        ]);
        $adherent17->setPosition(ActivityPositions::EMPLOYED);
        $adherent17->setStatus(Adherent::ENABLED);
        $adherent17->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $this->addReference('municipal-manager-lille', $adherent17);

        $adherent18 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_18_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'bernard.morin@example.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Bernard',
            'last_name' => 'Morin',
            'address' => PostAddress::createFrenchAddress('230 Rue du Moulin', '59246-59411', null, 50.481964435189084, 3.10317921728396),
            'birthdate' => '1951-05-04',
            'position' => 'commissioner',
            'phone' => '+33712345678',
            'registered_at' => '1994-03-09 00:00:00',
        ]);
        $adherent18->setPosition(ActivityPositions::EMPLOYED);
        $adherent18->setStatus(Adherent::ENABLED);
        $adherent18->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $this->addReference('municipal-manager-roubaix', $adherent18);

        $adherent19 = $this->adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_19_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'cedric.lebon@en-marche-dev.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'nickname_used' => true,
            'first_name' => 'Cédric',
            'last_name' => 'Lebon',
            'address' => PostAddress::createFrenchAddress('36 rue de la Paix', '75008-75108', null, 48.8699464, 2.3297187),
            'birthdate' => '1967-01-03',
            'position' => 'retired',
            'phone' => '+33187264236',
            'registered_at' => '2018-01-03 08:47:54',
        ]);
        $adherent19->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent19->setStatus(Adherent::ENABLED);
        $this->addReference('adherent-20', $adherent19);

        $referent = $this->adherentFactory->createFromArray([
            'uuid' => self::REFERENT_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'referent@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Referent',
            'last_name' => 'Referent',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-01-07',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $referent->setReferent(
            [
                $this->getReference('referent_tag_ch'),
                $this->getReference('referent_tag_es'),
                $this->getReference('referent_tag_92'),
                $this->getReference('referent_tag_76'),
                $this->getReference('referent_tag_77'),
                $this->getReference('referent_tag_13'),
                $this->getReference('referent_tag_59'),
            ],
            -1.6743,
            48.112
        );
        $roles = new ArrayCollection();
        $roles->add($this->getReference('referent'));
        $referent->setBoardMember(BoardMember::AREA_FRANCE_METROPOLITAN, $roles);
        $referent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $referent->addReferentTag($this->getReference('referent_tag_77'));
        $referent->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));
        $referent->addCharter(new ReferentCharter());
        $referent->addCharter(new CommitteeHostCharter());
        $referent->setLreArea(new LreArea($this->getReference('referent_tag_76')));
        $this->addReference('adherent-8', $referent);

        $referent75and77 = $this->adherentFactory->createFromArray([
            'uuid' => self::REFERENT_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'referent-75-77@en-marche-dev.fr',
            'gender' => 'female',
            'first_name' => 'Referent75and77',
            'last_name' => 'Referent75and77',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '75001-75101', null, 48.5278939, 2.6484923),
            'birthdate' => '1970-01-08',
            'position' => 'employed',
            'phone' => '+336765204050',
            'registered_at' => '2018-05-12 12:31:45',
        ]);
        $referent75and77->setReferent(
            [
                $this->getReference('referent_tag_75'),
                $this->getReference('referent_tag_77'),
                $this->getReference('referent_tag_75008'),
                $this->getReference('referent_tag_75009'),
            ],
            -1.6743,
            48.112
        );
        $referent75and77->setPrintPrivilege(true);
        $referent75and77->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $referent75and77->addReferentTag($this->getReference('referent_tag_75'));
        $referent75and77->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'));
        $this->addReference('adherent-19', $referent75and77);

        $referentChild = $this->adherentFactory->createFromArray([
            'uuid' => self::REFERENT_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'referent-child@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Referent child',
            'last_name' => 'Referent child',
            'address' => PostAddress::createFrenchAddress('3 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-02-07',
            'position' => 'employed',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $referentChild->setReferent(
            [
                $this->getReference('referent_tag_ch'),
                $this->getReference('referent_tag_93'),
            ],
            -1.6743,
            48.112
        );
        $referentChild->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $referentChild->addReferentTag($this->getReference('referent_tag_77'));
        $referentChild->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));

        $coordinator = $this->adherentFactory->createFromArray([
            'uuid' => self::COORDINATOR_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'coordinateur@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Coordinateur',
            'last_name' => 'Coordinateur',
            'address' => PostAddress::createFrenchAddress('75 Avenue Aristide Briand', '94110-94003', null, 48.805347, 2.325805),
            'birthdate' => '1969-04-10',
            'position' => 'employed',
            'phone' => '+33665859053',
            'registered_at' => '2017-09-20 15:31:21',
        ]);
        $coordinator->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $coordinator->setCoordinatorCommitteeArea(new CoordinatorManagedArea(['FR'], CoordinatorAreaSectors::COMMITTEE_SECTOR));
        $coordinator->addReferentTag($this->getReference('referent_tag_94'));
        $coordinator->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_94'));

        $coordinatorCP = $this->adherentFactory->createFromArray([
            'uuid' => self::COORDINATOR_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'coordinatrice-cp@en-marche-dev.fr',
            'gender' => 'female',
            'first_name' => 'Coordinatrice',
            'last_name' => 'CITIZEN PROJECT',
            'address' => PostAddress::createFrenchAddress('Place de la Madeleine', '75008-75108', null, 48.8704135, 2.324256),
            'birthdate' => '1989-03-13',
            'position' => 'employed',
            'phone' => '+33665859053',
            'registered_at' => '2017-09-20 15:31:21',
        ]);
        $coordinatorCP->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $coordinatorCP->setCoordinatorCitizenProjectArea(new CoordinatorManagedArea(['US', '59290', '77'], CoordinatorAreaSectors::CITIZEN_PROJECT_SECTOR));
        $coordinatorCP->addReferentTag($this->getReference('referent_tag_75'));
        $coordinatorCP->addReferentTag($this->getReference('referent_tag_75008'));
        $coordinatorCP->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'));
        $this->addReference('adherent-17', $coordinatorCP);

        $deputy_75_1 = $this->adherentFactory->createFromArray([
            'uuid' => self::DEPUTY_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'deputy@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Député',
            'last_name' => 'PARIS I',
            'address' => PostAddress::createFrenchAddress('3 Avenue du Général Eisenhower', '75008-75108', null, 48.8665777, 2.311635),
            'birthdate' => '1982-06-02',
            'registered_at' => '2017-06-01 09:26:31',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('deputy'));
        $deputy_75_1->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $deputy_75_1->setBoardMember(BoardMember::AREA_FRANCE_METROPOLITAN, $roles);
        $deputy_75_1->addReferentTag($this->getReference('referent_tag_75'));
        $deputy_75_1->addReferentTag($this->getReference('referent_tag_75008'));
        $deputy_75_1->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'));
        $deputy_75_1->certify();
        $this->addReference('deputy-75-1', $deputy_75_1);

        $deputy_75_2 = $this->adherentFactory->createFromArray([
            'uuid' => self::DEPUTY_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'deputy-75-2@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Député',
            'last_name' => 'PARIS II',
            'address' => PostAddress::createFrenchAddress('26 rue Vivienne', '75002-75102', null, 48.870025, 2.340985),
            'birthdate' => '1975-04-01',
            'registered_at' => '2018-08-05 15:02:34',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('deputy'));
        $deputy_75_2->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $deputy_75_2->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $deputy_75_2->addReferentTag($this->getReference('referent_tag_75'));
        $deputy_75_2->addReferentTag($this->getReference('referent_tag_75002'));
        $deputy_75_2->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'));
        $this->addReference('deputy-75-2', $deputy_75_2);

        $deputy_ch_li = $this->adherentFactory->createFromArray([
            'uuid' => self::DEPUTY_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'deputy-ch-li@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Député',
            'last_name' => 'CHLI FDESIX',
            'address' => PostAddress::createFrenchAddress('1 Place Colette', '75001-75101', null, 48.863571, 2.335938),
            'birthdate' => '1979-07-02',
            'registered_at' => '2017-06-26 10:15:17',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('deputy'));
        $deputy_ch_li->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $deputy_ch_li->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $deputy_ch_li->addReferentTag($this->getReference('referent_tag_ch'));
        $deputy_ch_li->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'));
        $this->addReference('deputy-ch-li', $deputy_ch_li);

        // senator
        $senator_59 = $this->adherentFactory->createFromArray([
            'uuid' => self::SENATOR_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'senateur@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Bob',
            'last_name' => 'Senateur (59)',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1992-07-28',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $senatorArea = new SenatorArea();
        $senatorArea->setDepartmentTag($this->getReference('referent_tag_59'));
        $senator_59->setSenatorArea($senatorArea);
        $senator_59->addCharter(new ReferentCharter());
        $this->addReference('senator-59', $senator_59);

        // municipal chief
        $municipalChief1 = $this->adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_CHIEF_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'municipal-chief@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Municipal 1',
            'last_name' => 'Chef 1',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1992-07-28',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $municipalChief1->addCharter(new MunicipalChiefCharter());
        $municipalChiefArea1 = new MunicipalChiefManagedArea();
        $municipalChiefArea1->setInseeCode('59350');
        $municipalChiefArea1->setJecouteAccess(true);
        $municipalChief1->setMunicipalChiefManagedArea($municipalChiefArea1);
        $this->addReference('municipal-chief-1', $municipalChief1);

        $municipalChief2 = $this->adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_CHIEF_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'municipal-chief-2@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Municipal 2',
            'last_name' => 'Chef 2',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $municipalChiefArea2 = new MunicipalChiefManagedArea();
        $municipalChiefArea2->setInseeCode('59124');
        $municipalChief2->setMunicipalChiefManagedArea($municipalChiefArea2);
        $this->addReference('municipal-chief-2', $municipalChief2);

        $municipalChief3 = $this->adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_CHIEF_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'municipal-chief-3@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Municipal 3',
            'last_name' => 'Chef 3',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $municipalChiefArea3 = new MunicipalChiefManagedArea();
        $municipalChiefArea3->setInseeCode('59411');
        $municipalChief3->setMunicipalChiefManagedArea($municipalChiefArea3);
        $this->addReference('municipal-chief-3', $municipalChief3);

        $assessor = $this->adherentFactory->createFromArray([
            'uuid' => self::ASSESSOR_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'assesseur@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Bob',
            'last_name' => 'Assesseur',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $assessor->certify();
        $assessor->setAssessorRole(new AssessorRoleAssociation($this->getReference('vote-place-lille-wazemmes')));
        $assessor->setElectionResultsReporter(true);
        $this->addReference('assessor-1', $assessor);

        $municipalManager = $this->adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_MANAGER_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'responsable-communal@en-marche-dev.fr',
            'gender' => 'female',
            'first_name' => 'Isabelle',
            'last_name' => 'Responsable Communal',
            'address' => PostAddress::createFrenchAddress('12 Avenue du Peuple Belge', '59000-59350', null, 50.6420374, 3.0630445),
            'birthdate' => '1983-08-27',
            'position' => 'employed',
            'phone' => '+33673654350',
            'registered_at' => '2019-07-10 09:19:00',
        ]);
        $municipalManager->setMunicipalManagerRole(new MunicipalManagerRoleAssociation([$this->getReference('city-lille')]));
        $this->addReference('municipal-manager-1', $municipalManager);

        $senatorialCandidate = $this->adherentFactory->createFromArray([
            'uuid' => self::SENATORIAL_CANDIDATE_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'senatorial-candidate@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Jean-Baptiste',
            'last_name' => 'Fortin',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '+33673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $senatorialCandidateManagedArea = new SenatorialCandidateManagedArea();
        $senatorialCandidateManagedArea->addDepartmentTag($this->getReference('referent_tag_59'));
        $senatorialCandidate->setSenatorialCandidateManagedArea($senatorialCandidateManagedArea);
        $senatorialCandidate->certify();
        $this->addReference('senatorial-candidate', $senatorialCandidate);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-male-a@en-marche-dev.fr',
            'gender' => GenderEnum::MALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Adrien',
            'last_name' => 'Petit',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1961-02-03',
            'registered_at' => '2017-01-25 19:31:45',
            'phone' => '+330699008800',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-21', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-female-a@en-marche-dev.fr',
            'gender' => GenderEnum::FEMALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Agathe',
            'last_name' => 'Petit',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-03-04',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-22', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-male-b@en-marche-dev.fr',
            'gender' => GenderEnum::MALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Étienne',
            'last_name' => 'Petit',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1961-02-03',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-23', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-female-b@en-marche-dev.fr',
            'gender' => GenderEnum::FEMALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Denise',
            'last_name' => 'Durand',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-03-04',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-24', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-male-c@en-marche-dev.fr',
            'gender' => GenderEnum::MALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Léon',
            'last_name' => 'Moreau',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1961-02-03',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-25', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-female-c@en-marche-dev.fr',
            'gender' => GenderEnum::FEMALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Claire',
            'last_name' => 'Moreau',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-03-04',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-26', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-male-d@en-marche-dev.fr',
            'gender' => GenderEnum::MALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Alfred',
            'last_name' => 'Leroy',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1961-02-03',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-27', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-female-d@en-marche-dev.fr',
            'gender' => GenderEnum::FEMALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Geneviève',
            'last_name' => 'Leroy',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-03-04',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-28', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-male-e@en-marche-dev.fr',
            'gender' => GenderEnum::MALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Guillaume',
            'last_name' => 'Richard',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1961-02-03',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-29', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-female-e@en-marche-dev.fr',
            'gender' => GenderEnum::FEMALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Denise',
            'last_name' => 'Richard',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-03-04',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-30', $adherent);

        $adherent = $this->adherentFactory->createFromArray([
            'uuid' => Uuid::uuid4(),
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'adherent-female-f@en-marche-dev.fr',
            'gender' => GenderEnum::FEMALE,
            'nationality' => Address::FRANCE,
            'first_name' => 'Jeanne',
            'last_name' => 'Simon',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-03-04',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent->certify();
        $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
        $manager->persist($adherent);
        $this->addReference('adherent-31', $adherent);

        foreach (range(32, 50) as $index) {
            $gender = 0 === $index % 2 ? GenderEnum::FEMALE : GenderEnum::MALE;

            $adherent = $this->adherentFactory->createFromArray([
                'uuid' => Uuid::uuid4(),
                'password' => self::DEFAULT_PASSWORD,
                'email' => sprintf('adherent-%s-%d@en-marche-dev.fr', $gender, $index),
                'gender' => $gender,
                'nationality' => Address::FRANCE,
                'first_name' => 'Adherent '.$index,
                'last_name' => 'Fa'.$index.'ke',
                'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
                'birthdate' => '1962-03-04',
                'registered_at' => '2017-01-25 19:31:45',
            ]);
            $adherent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
            $adherent->certify();
            $adherent->activate(AdherentActivationToken::generate($adherent), '-1 year');
            $manager->persist($adherent);
            $this->addReference('adherent-'.$index, $adherent);
        }

        $this->getReference('adherent-32')->setReferent([$this->getReference('referent_tag_de')]);

        // Create adherents accounts activation keys
        $key1 = AdherentActivationToken::generate($adherent1);
        $key2 = AdherentActivationToken::generate($adherent2);
        $key3 = AdherentActivationToken::generate($adherent3);
        $key4 = AdherentActivationToken::generate($adherent4);
        $key5 = AdherentActivationToken::generate($adherent5);
        $key6 = AdherentActivationToken::generate($adherent6);
        $key7 = AdherentActivationToken::generate($adherent7);
        $key8 = AdherentActivationToken::generate($referent);
        $key9 = AdherentActivationToken::generate($adherent9);
        $key10 = AdherentActivationToken::generate($adherent10);
        $key11 = AdherentActivationToken::generate($adherent11);
        $key12 = AdherentActivationToken::generate($adherent12);
        $key13 = AdherentActivationToken::generate($adherent13);
        $key14 = AdherentActivationToken::generate($adherent14);
        $key15 = AdherentActivationToken::generate($adherent15);
        $key16 = AdherentActivationToken::generate($coordinator);
        $key17 = AdherentActivationToken::generate($coordinatorCP);
        $key18 = AdherentActivationToken::generate($referentChild);
        $key19 = AdherentActivationToken::generate($referent75and77);
        $key20 = AdherentActivationToken::generate($deputy_75_1);
        $key21 = AdherentActivationToken::generate($deputy_ch_li);
        $key22 = AdherentActivationToken::generate($adherent16);
        $key23 = AdherentActivationToken::generate($municipalChief1);
        $key24 = AdherentActivationToken::generate($municipalChief2);
        $key25 = AdherentActivationToken::generate($municipalChief3);
        $key26 = AdherentActivationToken::generate($adherent17);
        $key27 = AdherentActivationToken::generate($adherent18);
        $key28 = AdherentActivationToken::generate($senator_59);
        $key29 = AdherentActivationToken::generate($assessor);
        $key30 = AdherentActivationToken::generate($municipalManager);
        $key31 = AdherentActivationToken::generate($deputy_75_2);
        $key32 = AdherentActivationToken::generate($adherent19);
        $key33 = AdherentActivationToken::generate($senatorialCandidate);

        // Enable some adherents accounts
        $adherent2->activate($key2, '2016-11-16 20:54:13');
        $adherent3->activate($key3, '2017-01-03 09:12:37');
        $adherent4->activate($key4, '2017-01-18 13:23:50');
        $adherent5->activate($key5, '2017-01-08 06:42:56');
        $adherent6->activate($key6, '2017-01-17 08:07:45');
        $adherent7->activate($key7, '2017-01-25 19:34:02');
        $adherent9->activate($key9, '2017-02-16 17:23:15');
        $adherent10->activate($key10, '2017-02-23 14:02:18');
        $adherent11->activate($key11, '2017-04-10 14:12:56');
        $adherent12->activate($key12, '2017-04-09 06:26:14');
        $adherent13->activate($key13, '2017-05-03 09:16:54');
        $adherent14->activate($key14, '2017-05-04 09:34:21');
        $adherent16->activate($key22, '2017-05-04 09:34:21');
        $adherent17->activate($key26, '2017-06-25 11:36:48');
        $adherent18->activate($key27, '2017-06-25 11:36:48');
        $adherent19->activate($key32, '2017-06-25 11:36:48');
        // $key15 is not activated, but adherent is enabled
        $referent->activate($key8, '2017-02-07 13:20:45');
        $coordinator->activate($key16, '2017-09-20 17:44:32');
        $coordinatorCP->activate($key17, '2018-01-20 14:34:11');
        $referentChild->activate($key18, '2017-02-07 13:20:45');
        $referent75and77->activate($key19, '2018-05-13 07:21:01');
        $deputy_75_1->activate($key20, '2017-06-01 12:14:51');
        $deputy_75_2->activate($key31, '2017-07-26 12:14:51');
        $deputy_ch_li->activate($key21, '2017-06-26 12:14:51');
        $senator_59->activate($key28, '2017-06-26 12:14:51');
        $municipalChief1->activate($key23, '2019-06-10 09:19:00');
        $municipalChief2->activate($key24, '2019-06-10 09:19:00');
        $municipalChief3->activate($key25, '2019-06-10 09:19:00');
        $assessor->activate($key29, '2019-06-10 09:19:00');
        $municipalManager->activate($key30, '2019-07-10 09:19:00');
        $senatorialCandidate->activate($key33, '2019-07-10 09:19:00');

        // Make an adherent request a new password
        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent1);

        // °\_O_/° Persist all the things (in memory) !!!
        $manager->persist($adherent1);
        $manager->persist($adherent2);
        $manager->persist($adherent3);
        $manager->persist($adherent4);
        $manager->persist($adherent5);
        $manager->persist($adherent6);
        $manager->persist($adherent7);
        $manager->persist($adherent9);
        $manager->persist($adherent10);
        $manager->persist($adherent11);
        $manager->persist($adherent12);
        $manager->persist($adherent13);
        $manager->persist($adherent14);
        $manager->persist($adherent15);
        $manager->persist($adherent16);
        $manager->persist($referent);
        $manager->persist($referent75and77);
        $manager->persist($referentChild);
        $manager->persist($coordinator);
        $manager->persist($coordinatorCP);
        $manager->persist($deputy_75_1);
        $manager->persist($deputy_75_2);
        $manager->persist($deputy_ch_li);
        $manager->persist($senator_59);
        $manager->persist($municipalChief1);
        $manager->persist($municipalChief2);
        $manager->persist($municipalChief3);
        $manager->persist($adherent17);
        $manager->persist($adherent18);
        $manager->persist($adherent19);
        $manager->persist($assessor);
        $manager->persist($municipalManager);
        $manager->persist($senatorialCandidate);

        // For Organizational chart: adherent which is co-referent and municipal manager supervisor in the referent@en-marche-dev.fr team
        $adherent6->setReferentTeamMember(new ReferentTeamMember($this->getReference('adherent-8')));
        $adherent6->setMunicipalManagerSupervisorRole(new MunicipalManagerSupervisorRole($this->getReference('adherent-8')));
        // For Organizational chart: adherent which is co-referent in another referent team
        $adherent4->setReferentTeamMember(new ReferentTeamMember($this->getReference('adherent-19')));

        $adherent14->setJecouteManagedZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_77'));

        $manager->persist($key1);
        $manager->persist($key2);
        $manager->persist($key3);
        $manager->persist($key4);
        $manager->persist($key5);
        $manager->persist($key6);
        $manager->persist($key7);
        $manager->persist($key8);
        $manager->persist($key9);
        $manager->persist($key10);
        $manager->persist($key11);
        $manager->persist($key12);
        $manager->persist($key13);
        $manager->persist($key14);
        $manager->persist($key15);
        $manager->persist($key16);
        $manager->persist($key17);
        $manager->persist($key18);
        $manager->persist($key19);
        $manager->persist($key20);
        $manager->persist($key22);
        $manager->persist($key23);
        $manager->persist($key24);
        $manager->persist($key25);
        $manager->persist($key26);
        $manager->persist($key27);
        $manager->persist($key31);
        $manager->persist($key33);

        $manager->persist($resetPasswordToken);

        $manager->flush();
    }

    private function getStandardSubscriptionTypes(): array
    {
        return array_map(function (string $type) {
            return $this->getReference('st-'.$type);
        }, SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES);
    }

    public function getDependencies()
    {
        return [
            LoadBoardMemberRoleData::class,
            LoadAdherentTagData::class,
            LoadReferentTagData::class,
            LoadSubscriptionTypeData::class,
            LoadVotePlaceData::class,
            LoadCityData::class,
            LoadTerritorialCouncilData::class,
            LoadThematicCommunityData::class,
            LoadGeoZoneData::class,
        ];
    }
}
