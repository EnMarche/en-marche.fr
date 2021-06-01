<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Election\VoteListNuanceEnum;
use App\Entity\ElectedRepresentative\CandidateNameEnum;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\ElectedRepresentativeLabel;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\LaREMSupportEnum;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\ElectedRepresentative\SocialLinkTypeEnum;
use App\Entity\ElectedRepresentative\SocialNetworkLink;
use App\Utils\PhoneNumberUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadElectedRepresentativeData extends Fixture implements DependentFixtureInterface
{
    public const ELECTED_REPRESENTATIVE_1_UUID = '34b0b236-b72e-4161-8f9f-7f23f935758f';
    public const ELECTED_REPRESENTATIVE_2_UUID = '4b8bb9fd-0645-47fd-bb9a-3515bf46618a';
    public const ELECTED_REPRESENTATIVE_3_UUID = '84187220-348c-44ef-a297-28e3af88b74b';
    public const ELECTED_REPRESENTATIVE_4_UUID = '07f02feb-86e3-4de9-9970-a1528fd0165e';
    public const ELECTED_REPRESENTATIVE_5_UUID = '2e6a1018-71bd-4ae9-b17c-f93626e306f6';
    public const ELECTED_REPRESENTATIVE_6_UUID = '82ec811a-45f7-4527-97ef-3dea61af131b';
    public const ELECTED_REPRESENTATIVE_7_UUID = '867aed08-9e1a-4ec1-b2da-097e1de70132';

    public function load(ObjectManager $manager): void
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'elected_representative');

        // with adherent, mandate 92 CITY_COUNCIL : functions OTHER_MEMBER, PRESIDENT_OF_EPCI
        $erAdherent92 = $this->createElectedRepresentative(
            'Michelle',
            'DUFOUR',
            new \DateTime('1972-11-23'),
            'female',
            Uuid::fromString(self::ELECTED_REPRESENTATIVE_1_UUID)
        );
        $erAdherent92->setAdherent($this->getReference('adherent-5'));
        $erAdherent92->addUserListDefinition($this->getReference('user-list-definition-instances_member'));
        foreach ($erAdherent92->getSponsorships() as $sponsorship) {
            if (2012 === $sponsorship->getPresidentialElectionYear()) {
                $sponsorship->setCandidate(CandidateNameEnum::FRANCOIS_HOLLANDE, $erAdherent92);
            }

            if (2017 === $sponsorship->getPresidentialElectionYear()) {
                $sponsorship->setCandidate(CandidateNameEnum::EMMANUEL_MACRON, $erAdherent92);
            }
        }
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::LAREM,
            $erAdherent92,
            true,
            '2017'
        );
        $mandate = new Mandate(
            MandateTypeEnum::CITY_COUNCIL,
            true,
            VoteListNuanceEnum::REM,
            LaREMSupportEnum::OFFICIAL,
            null,
            $erAdherent92,
            true,
            new \DateTime('2019-07-23')
        );
        $mandate->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_community_200054781'));
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::OTHER_MEMBER,
            'Some precisions',
            $erAdherent92,
            $mandate,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $erAdherent92,
            $mandate,
            false,
            new \DateTime('2015-03-24')
        );
        $erAdherent92->addLabel($label);
        $erAdherent92->addMandate($mandate);
        $erAdherent92->addPoliticalFunction($politicalFunction1);
        $erAdherent92->addPoliticalFunction($politicalFunction2);

        $manager->persist($erAdherent92);

        // with mandate 92 CITY_COUNCIL : functions MAYOR, PRESIDENT_OF_EPCI (finished)
        $erCityCouncilWithFinishedFunction = $this->createElectedRepresentative(
            'Delphine',
            'BOUILLOUX',
            new \DateTime('1977-08-02'),
            'female',
            Uuid::fromString(self::ELECTED_REPRESENTATIVE_2_UUID)
        );
        $erCityCouncilWithFinishedFunction->setContactPhone(PhoneNumberUtils::create('+330999887766'));
        $erCityCouncilWithFinishedFunction->addUserListDefinition($this->getReference('user-list-definition-supporting_la_rem'));
        $erCityCouncilWithFinishedFunction->addUserListDefinition($this->getReference('user-list-definition-instances_member'));
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::PS,
            $erCityCouncilWithFinishedFunction,
            true,
            '2016'
        );
        $mandate = new Mandate(
            MandateTypeEnum::CITY_COUNCIL,
            true,
            VoteListNuanceEnum::NC,
            LaREMSupportEnum::OFFICIAL,
            null,
            $erCityCouncilWithFinishedFunction,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::MAYOR,
            null,
            $erCityCouncilWithFinishedFunction,
            $mandate,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $erCityCouncilWithFinishedFunction,
            $mandate,
            false,
            new \DateTime('2016-06-02'),
            new \DateTime('2019-01-06')
        );
        $twitter = new SocialNetworkLink('https://twitter.com/DeBou', SocialLinkTypeEnum::TWITTER, $erCityCouncilWithFinishedFunction);
        $instagram = new SocialNetworkLink('https://instagram.com/deBou', SocialLinkTypeEnum::INSTAGRAM, $erCityCouncilWithFinishedFunction);
        $telegram = new SocialNetworkLink('https://telegram.com/deBou', SocialLinkTypeEnum::TELEGRAM, $erCityCouncilWithFinishedFunction);
        $facecbook = new SocialNetworkLink('https://facebook.com/deBou', SocialLinkTypeEnum::FACEBOOK, $erCityCouncilWithFinishedFunction);
        $youtube = new SocialNetworkLink('https://youtube.com/deBou', SocialLinkTypeEnum::YOUTUBE, $erCityCouncilWithFinishedFunction);
        $erCityCouncilWithFinishedFunction->addLabel($label);
        $erCityCouncilWithFinishedFunction->addMandate($mandate);
        $erCityCouncilWithFinishedFunction->addPoliticalFunction($politicalFunction1);
        $erCityCouncilWithFinishedFunction->addPoliticalFunction($politicalFunction2);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($twitter);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($instagram);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($telegram);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($facecbook);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($youtube);

        $manager->persist($erCityCouncilWithFinishedFunction);

        // with mandate 76 CITY_COUNCIL : functions DEPUTY_MAYOR
        // with mandate 76 EPCI_MEMBER (not elected) : functions PRESIDENT_OF_EPCI
        $er2Mandates = $this->createElectedRepresentative(
            'Daniel',
            'BOULON',
            new \DateTime('1951-03-04'),
            null,
            Uuid::fromString(self::ELECTED_REPRESENTATIVE_3_UUID)
        );
        $er2Mandates->addUserListDefinition($this->getReference('user-list-definition-supporting_la_rem'));
        $label1 = new ElectedRepresentativeLabel(
            LabelNameEnum::PS,
            $er2Mandates,
            false,
            '2014',
            '2018'
        );
        $label2 = new ElectedRepresentativeLabel(
            LabelNameEnum::GS,
            $er2Mandates,
            true,
            '2018'
        );
        $mandate1 = new Mandate(
            MandateTypeEnum::CITY_COUNCIL,
            true,
            VoteListNuanceEnum::DIV,
            LaREMSupportEnum::NOT_SUPPORTED,
            null,
            $er2Mandates,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate1->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'));
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::DEPUTY_MAYOR,
            null,
            $er2Mandates,
            $mandate1,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate2 = new Mandate(
            MandateTypeEnum::EPCI_MEMBER,
            false,
            VoteListNuanceEnum::DIV,
            LaREMSupportEnum::NOT_SUPPORTED,
            null,
            $er2Mandates,
            true,
            new \DateTime('2017-01-11')
        );
        $mandate2->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'));
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $er2Mandates,
            $mandate2,
            false,
            new \DateTime('2019-07-23')
        );
        $er2Mandates->addLabel($label1);
        $er2Mandates->addLabel($label2);
        $er2Mandates->addMandate($mandate1);
        $er2Mandates->addMandate($mandate2);
        $er2Mandates->addPoliticalFunction($politicalFunction1);
        $er2Mandates->addPoliticalFunction($politicalFunction2);

        $manager->persist($er2Mandates);

        // with mandate 94 SENATOR, no function
        // with mandate 76 DEPUTY finished : functions OTHER_MEMBER
        $er2MandatesOneFinished = $this->createElectedRepresentative(
            'Roger',
            'BUET',
            new \DateTime('1952-04-21'),
            'male',
            Uuid::fromString(self::ELECTED_REPRESENTATIVE_4_UUID)
        );
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::OTHER,
            $er2MandatesOneFinished,
            true,
            '2014'
        );
        $mandate1 = new Mandate(
            MandateTypeEnum::SENATOR,
            true,
            VoteListNuanceEnum::FN,
            LaREMSupportEnum::INFORMAL,
            null,
            $er2MandatesOneFinished,
            true,
            new \DateTime('2016-03-23')
        );
        $mandate1->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_region_94'));
        $mandate2 = new Mandate(
            MandateTypeEnum::DEPUTY,
            true,
            VoteListNuanceEnum::FN,
            LaREMSupportEnum::NOT_SUPPORTED,
            null,
            $er2MandatesOneFinished,
            false,
            new \DateTime('2011-12-23'),
            new \DateTime('2015-02-23')
        );
        $mandate2->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'));
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::OTHER_MEMBER,
            null,
            $er2MandatesOneFinished,
            $mandate2,
            false,
            new \DateTime('2019-07-23')
        );
        $er2MandatesOneFinished->addLabel($label);
        $er2MandatesOneFinished->addMandate($mandate1);
        $er2MandatesOneFinished->addMandate($mandate2);
        $er2MandatesOneFinished->addPoliticalFunction($politicalFunction2);

        $manager->persist($er2MandatesOneFinished);

        // with mandate EURO_DEPUTY, no function
        $erEuroDeputy2Labels = $this->createElectedRepresentative(
            'Sans',
            'OFFICIELID',
            new \DateTime('1951-11-03'),
            'male',
            Uuid::fromString(self::ELECTED_REPRESENTATIVE_5_UUID)
        );
        $label1 = new ElectedRepresentativeLabel(
            LabelNameEnum::MRC,
            $erEuroDeputy2Labels,
            false,
            '2014',
            '2017'
        );
        $label2 = new ElectedRepresentativeLabel(
            LabelNameEnum::GS,
            $erEuroDeputy2Labels,
            true,
            '2017'
        );
        $mandate = new Mandate(
            MandateTypeEnum::EURO_DEPUTY,
            true,
            VoteListNuanceEnum::ALLI,
            LaREMSupportEnum::INVESTED,
            null,
            $erEuroDeputy2Labels,
            true,
            new \DateTime('2016-03-23')
        );
        $erEuroDeputy2Labels->addLabel($label1);
        $erEuroDeputy2Labels->addLabel($label2);
        $erEuroDeputy2Labels->addMandate($mandate);

        $manager->persist($erEuroDeputy2Labels);

        // with mandate 13 DEPUTY : functions VICE_PRESIDENT_OF_EPCI
        // with mandate 13 REGIONAL_COUNCIL : functions PRESIDENT_OF_EPCI
        $er2Mandates2Functions = $this->createElectedRepresentative(
            'André',
            'LOBELL',
            new \DateTime('1951-11-03'),
            'male',
            Uuid::fromString(self::ELECTED_REPRESENTATIVE_6_UUID)
        );
        $mandate1 = new Mandate(
            MandateTypeEnum::DEPUTY,
            true,
            VoteListNuanceEnum::RN,
            LaREMSupportEnum::INFORMAL,
            null,
            $er2Mandates2Functions,
            true,
            new \DateTime('2015-03-13')
        );
        $mandate1->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_13'));
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::VICE_PRESIDENT_OF_EPCI,
            null,
            $er2Mandates2Functions,
            $mandate1,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate2 = new Mandate(
            MandateTypeEnum::REGIONAL_COUNCIL,
            true,
            VoteListNuanceEnum::DIV,
            LaREMSupportEnum::NOT_SUPPORTED,
            null,
            $er2Mandates2Functions,
            true,
            new \DateTime('2017-07-18')
        );
        $mandate2->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'));
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::MAYOR_ASSISTANT,
            null,
            $er2Mandates2Functions,
            $mandate2,
            true,
            new \DateTime('2019-05-10')
        );
        $politicalFunction3 = new PoliticalFunction(
            PoliticalFunctionNameEnum::VICE_PRESIDENT_OF_EPCI,
            null,
            $er2Mandates2Functions,
            $mandate2,
            false,
            new \DateTime('2016-05-10'),
            new \DateTime('2019-05-09')
        );
        $er2Mandates2Functions->addMandate($mandate1);
        $er2Mandates2Functions->addMandate($mandate2);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction1);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction2);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction3);
        $er2Mandates2Functions->setAdherent($this->getReference('deputy-75-1'));

        $manager->persist($er2Mandates2Functions);

        // with not elected mandate Corsica CORSICA_ASSEMBLY_MEMBER
        $erWithNotElectedMandate = $this->createElectedRepresentative(
            'Jesuis',
            'PASELU',
            new \DateTime('1981-01-03'),
            'male',
            Uuid::fromString(self::ELECTED_REPRESENTATIVE_7_UUID)
        );
        $mandate = new Mandate(
            MandateTypeEnum::CORSICA_ASSEMBLY_MEMBER,
            false,
            VoteListNuanceEnum::ALLI,
            LaREMSupportEnum::INFORMAL,
            null,
            $erWithNotElectedMandate,
            false,
            new \DateTime('2020-03-15')
        );
        $mandate->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_region_94'));
        $erWithNotElectedMandate->addMandate($mandate);

        $manager->persist($erWithNotElectedMandate);

        // with mandate CITY_COUNCIL 75007
        $erParis = $this->createElectedRepresentative('Arrondissement', 'PARIS', new \DateTime('1972-02-02'), 'male');
        $mandate = new Mandate(
            MandateTypeEnum::CITY_COUNCIL,
            true,
            VoteListNuanceEnum::NC,
            null,
            null,
            $erWithNotElectedMandate,
            true,
            new \DateTime('2019-03-15')
        );
        $mandate->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75107'));
        $erParis->addMandate($mandate);

        $manager->persist($erParis);

        // with mandate DEPUTY CIRCO 75
        $erParis2 = $this->createElectedRepresentative('Circonscription', 'PARISS', new \DateTime('1982-03-03'), 'female');
        $mandate = new Mandate(
            MandateTypeEnum::DEPUTY,
            true,
            VoteListNuanceEnum::REM,
            LaREMSupportEnum::OFFICIAL,
            null,
            $erWithNotElectedMandate,
            true,
            new \DateTime('2019-01-11')
        );
        $mandate->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-8'));
        $erParis2->addMandate($mandate);

        $manager->persist($erParis2);

        // with mandate SENATOR 75
        $erParis3 = $this->createElectedRepresentative('Département', 'PARIS', new \DateTime('1962-04-04'), 'male');
        $mandate = new Mandate(
            MandateTypeEnum::SENATOR,
            true,
            VoteListNuanceEnum::RN,
            null,
            null,
            $erWithNotElectedMandate,
            true,
            new \DateTime('2018-01-11')
        );
        $mandate->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_75'));
        $erParis3->addMandate($mandate);

        $manager->persist($erParis3);

        $erDepartment59 = $this->createElectedRepresentative('Département', 'Nord', new \DateTime('1962-04-04'), 'male');
        $mandate = new Mandate(
            MandateTypeEnum::SENATOR,
            true,
            VoteListNuanceEnum::RN,
            null,
            null,
            $erWithNotElectedMandate,
            true,
            new \DateTime('2018-01-11')
        );
        $mandate->setGeoZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'));
        $erDepartment59->addMandate($mandate);

        $manager->persist($erDepartment59);

        $manager->flush();
    }

    private function createElectedRepresentative(
        string $firstName,
        string $lastName,
        \DateTime $birthDate,
        string $gender = null,
        string $uuid = null
    ): ElectedRepresentative {
        return ElectedRepresentative::create(
            $firstName,
            $lastName,
            $birthDate,
            $gender,
            $uuid ? Uuid::fromString($uuid) : null
        );
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadUserListDefinitionData::class,
        ];
    }
}
