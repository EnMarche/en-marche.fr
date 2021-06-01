<?php

namespace App\DataFixtures\ORM;

use App\Entity\TonMacronChoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadTonMacronData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Step 0
        $choices[] = $this->createNoStepChoice(
            '8a2fdb59-357f-4e74-9aeb-c2b064d31064',
            TonMacronChoice::MAIL_INTRODUCTION_KEY,
            'Début du message',
            file_get_contents(__DIR__.'/../ton-macron/mail-introduction.txt')
        );

        $choices[] = $this->createNoStepChoice(
            '31276b63-a4f3-4994-aca8-ed4ca78c173e',
            TonMacronChoice::MAIL_CONCLUSION_KEY,
            'Find du message',
            'Dis-moi ce que tu en penses. Tu trouveras tous les détails de ces propositions ici.'
        );

        $choices[] = $this->createNoStepChoice(
            '31276b63-c013-4719-85a9-c2b064d31064',
            TonMacronChoice::FEMALE_KEY,
            'Madame',
            'Argument pour les femmes'
        );

        // Step 1
        $choices[] = $this->createFirstStepChoice(
            '5d0c4a88-c013-4719-85a9-e02a7f41266c',
            'S01C01',
            'Cadre (fonction publique)',
            'S01C01'
        );

        $choices[] = $this->createFirstStepChoice(
            '8d4114b1-e940-4e9d-aa6f-51925f22cafc',
            'S01C02',
            'Cadre (entreprise)',
            'S01C02'
        );

        $choices[] = $this->createFirstStepChoice(
            'a702b524-0fda-4949-a866-cc5aa3a81e43',
            'S01C03',
            'Employés et professions intermédiaires (fonction publique)',
            'S01C03'
        );

        $choices[] = $this->createFirstStepChoice(
            '19354434-44ed-44f2-80b9-237af40638dd',
            'S01C04',
            'Employés et professions intermédiaires (entreprise)',
            'S01C04'
        );

        $choices[] = $this->createFirstStepChoice(
            '8c82e4a0-e567-4631-b978-1412b95c57a5',
            'S01C05',
            'Artisan, commerçant, chef d’entreprise',
            'S01C05'
        );

        $choices[] = $this->createFirstStepChoice(
            'd472accc-4f42-477d-b8a3-13bc25b63bd8',
            'S01C06',
            'Profession libérale',
            'S01C06'
        );

        $choices[] = $this->createFirstStepChoice(
            'abfdfcfa-1033-4116-bfb0-e330aed4617c',
            'S01C07',
            'Ouvrier',
            'S01C07'
        );

        $choices[] = $this->createFirstStepChoice(
            '94e1e26e-fe63-4f9f-ab87-28640483b9d4',
            'S01C08',
            'Retraité',
            'S01C08'
        );

        $choices[] = $this->createFirstStepChoice(
            'ce833041-8ec2-44db-8f00-2dc542139f72',
            'S01C09',
            "En recherche d'emploi",
            'S01C09'
        );

        $choices[] = $this->createFirstStepChoice(
            '82333e37-f057-43d3-b505-5931f497781b',
            'S01C10',
            'Militaire',
            'S01C10'
        );

        $choices[] = $this->createFirstStepChoice(
            '5cc696d9-fa8c-47cc-a193-836d0ce00b66',
            'S01C11',
            'Etudiant',
            'S01C11'
        );

        $choices[] = $this->createFirstStepChoice(
            '3d1e66e6-ad09-4c57-8309-61ff1f3b537d',
            'S01C12',
            'Ne travaille pas',
            'S01C12'
        );

        // Step 2
        $choices[] = $this->createSecondStepChoice(
            '974f1860-2e07-407d-8b30-3d19dca65c3c',
            'S02C01',
            'Créer une entreprise',
            'S02C01'
        );

        $choices[] = $this->createSecondStepChoice(
            '1c52b133-6560-4f9a-8ea2-089251204b87',
            'S02C02',
            'Se reconvertir professionnellement',
            'S02C02'
        );

        $choices[] = $this->createSecondStepChoice(
            '441129d4-d2be-4ec9-a725-e5d583fd2f67',
            'S02C03',
            'Vivre à l\'étranger',
            'S02C03'
        );

        $choices[] = $this->createSecondStepChoice(
            'e256e2a7-6609-4622-826b-a3f353415c7b',
            'S02C04',
            'Déménager ailleurs en France',
            'S02C04'
        );

        $choices[] = $this->createSecondStepChoice(
            '86987229-c80e-4069-a937-c890bac78c25',
            'S02C05',
            'Épargner davantage',
            'S02C05'
        );

        $choices[] = $this->createSecondStepChoice(
            '40436c78-bba2-4d3a-8dbd-e41d690b2be3',
            'S02C06',
            'Devenir propriétaire',
            'S02C06'
        );

        $choices[] = $this->createSecondStepChoice(
            '9c70c1e7-fee0-47a7-bc8d-5d8ec3c189bb',
            'S02C07',
            'Partir vivre à la campagne',
            'S02C07'
        );

        $choices[] = $this->createSecondStepChoice(
            '09f5029f-2c52-4653-b780-6281d2bfdf14',
            'S02C08',
            'Mieux se former sur son métier',
            'S02C08'
        );

        $choices[] = $this->createSecondStepChoice(
            '7eb52093-11bb-4825-ba7b-42d99075417b',
            'S02C09',
            'Investir dans une PME',
            'S02C09'
        );

        $choices[] = $this->createSecondStepChoice(
            '72c3a73e-a7b2-488f-a9fc-7a08cd4c39ed',
            'S02C10',
            'Devenir aidant d\'un parent âgé',
            'S02C10'
        );

        $choices[] = $this->createSecondStepChoice(
            '2fdd8341-1458-42de-adba-fb3f08b693e4',
            'S02C11',
            'Aider un collègue aidant',
            'S02C11'
        );

        $choices[] = $this->createSecondStepChoice(
            'cfa1e12a-d7c2-4316-8284-6603bb713d6d',
            'S02C12',
            'Changer une vieille voiture',
            'S02C12'
        );

        $choices[] = $this->createSecondStepChoice(
            'c1611082-b5b6-49f2-a3f6-8f89b1002bd1',
            'S02C13',
            'Renouveler son logement énergivore',
            'S02C13'
        );

        // Step 3
        $choices[] = $this->createThirdStepChoice(
            '72535d47-be2b-4906-9ae7-f58bcb7debe9',
            'S03C01',
            'Emploi des jeunes',
            'S03C01'
        );

        $choices[] = $this->createThirdStepChoice(
            '739601ed-19d6-4a16-97b0-8b83779ca983',
            'S03C02',
            'Agriculture',
            'S03C02'
        );

        $choices[] = $this->createThirdStepChoice(
            '7d9e3959-e28b-4980-b14d-8e57db11f51d',
            'S03C03',
            'Culture',
            'S03C03'
        );

        $choices[] = $this->createThirdStepChoice(
            'd4a01ed2-0f94-4be1-8874-9a521ed412c8',
            'S03C04',
            'Compétitivité',
            'S03C04'
        );

        $choices[] = $this->createThirdStepChoice(
            'd28a2517-d16e-42a0-b714-b05490372d00',
            'S03C05',
            'Défense',
            'S03C05'
        );

        $choices[] = $this->createThirdStepChoice(
            '1530b9bd-b2f8-4d63-a3eb-be0b56e8c38a',
            'S03C06',
            'Education',
            'S03C06'
        );

        $choices[] = $this->createThirdStepChoice(
            'b43629b6-5df5-4184-b698-bed57c4bac42',
            'S03C07',
            'Enseignement supérieur et recherche',
            'S03C07'
        );

        $choices[] = $this->createThirdStepChoice(
            '5184c787-9720-463e-b51d-289aea912b62',
            'S03C08',
            'Entreprises et dialogue social',
            'S03C08'
        );

        $choices[] = $this->createThirdStepChoice(
            '6ef8a131-8366-451e-8813-0c9186a2e2c1',
            'S03C09',
            'Environnement et transition écologique',
            'S03C09'
        );

        $choices[] = $this->createThirdStepChoice(
            '38f9da95-bc62-4648-b10c-989cc96e01bb',
            'S03C10',
            'Europe',
            'S03C10'
        );

        $choices[] = $this->createThirdStepChoice(
            '35503645-1b93-48d7-beea-3b0322e05cc6',
            'S03C11',
            'Familles et société',
            'S03C11'
        );

        $choices[] = $this->createThirdStepChoice(
            'b2608f8e-7954-41ab-847b-43485bd1fff2',
            'S03C12',
            'Finances Publiques',
            'S03C12'
        );

        $choices[] = $this->createThirdStepChoice(
            '01363f79-bd92-418d-b5a0-7ae9fd04031f',
            'S03C13',
            'Handicap',
            'S03C13'
        );

        $choices[] = $this->createThirdStepChoice(
            '97cc2947-4470-47cf-83dc-81fa3e7479bd',
            'S03C14',
            'Immigration et asile',
            'S03C14'
        );

        $choices[] = $this->createThirdStepChoice(
            '5b8fe3ea-6c04-4528-b7e0-faa1c008659a',
            'S03C15',
            'International',
            'S03C15'
        );

        $choices[] = $this->createThirdStepChoice(
            'b98b8182-12ea-4493-9e3d-2940433fa191',
            'S03C16',
            'Justice',
            'S03C16'
        );

        $choices[] = $this->createThirdStepChoice(
            'eb9c209a-f8f9-45c5-9384-1e45fe838f64',
            'S03C17',
            'Logement',
            'S03C17'
        );

        $choices[] = $this->createThirdStepChoice(
            '6624dcc1-81fb-4614-ad5a-9158cd7f6398',
            'S03C18',
            'Mobilité',
            'S03C18'
        );

        $choices[] = $this->createThirdStepChoice(
            'f1901bf4-ca55-41af-878e-73bc4ecb14de',
            'S03C19',
            'Numérique',
            'S03C19'
        );

        $choices[] = $this->createThirdStepChoice(
            'fc161a49-d370-4667-bddd-8ad1c6e898f9',
            'S03C20',
            'Outre-mer',
            'S03C20'
        );

        $choices[] = $this->createThirdStepChoice(
            'f577af9e-2a29-4cd1-999a-41802ad58035',
            'S03C21',
            'Pauvreté',
            'S03C21'
        );

        $choices[] = $this->createThirdStepChoice(
            '835a6bf4-a4f6-4551-8689-87c873f8f69f',
            'S03C22',
            'Pouvoir d\'achat et fiscalité',
            'S03C22'
        );

        $choices[] = $this->createThirdStepChoice(
            'f1a51929-b05c-467e-a851-ec6438fd4ff1',
            'S03C23',
            'Questions religieuses et laïcité',
            'S03C23'
        );

        $choices[] = $this->createThirdStepChoice(
            'dbc585c9-8f3a-4daa-af88-04eb6605752c',
            'S03C24',
            'Dépendance',
            'S03C24'
        );

        $choices[] = $this->createThirdStepChoice(
            'e9e70d7d-932c-4c1c-9356-16f809f7597b',
            'S03C25',
            'Retraites',
            'S03C25'
        );

        $choices[] = $this->createThirdStepChoice(
            'a587df69-ab67-4e79-a877-9d061a018c41',
            'S03C26',
            'Santé',
            'S03C26'
        );

        $choices[] = $this->createThirdStepChoice(
            'ebf48e68-eb36-411e-8e25-e7c9dc4ea902',
            'S03C27',
            'Sécurité',
            'S03C27'
        );

        $choices[] = $this->createThirdStepChoice(
            '3d248691-5da1-4610-bcc3-e862b7b63969',
            'S03C28',
            'Sport',
            'S03C28'
        );

        $choices[] = $this->createThirdStepChoice(
            'e169718b-4b1f-445c-a441-fc9a6a722d45',
            'S03C29',
            'Service public et territoires',
            'S03C29'
        );

        $choices[] = $this->createThirdStepChoice(
            'd3f9438d-bc98-4b25-8a4e-5054ff736ce4',
            'S03C30',
            'Terrorisme',
            'S03C30'
        );

        $choices[] = $this->createThirdStepChoice(
            '5e828c51-d6a7-496d-b75b-30b96b45cd23',
            'S03C31',
            'Travail, Emploi et Chômage',
            'S03C31'
        );

        $choices[] = $this->createThirdStepChoice(
            '61da82c9-5646-45f7-bd7d-5e032a75e12a',
            'S03C32',
            'Renouveau démocratique',
            'S03C32'
        );

        // Step 4
        $choices[] = $this->createFourthStepChoice(
            'ad2f3c09-a3c3-4b71-af80-103b31c69229',
            'S04C01',
            'Sa jeunesse',
            'S04C01'
        );

        $choices[] = $this->createFourthStepChoice(
            '41062d11-a85b-46f5-ab38-6846def9a36a',
            'S04C02',
            'Son dépassement du clivage gauche/droite',
            'S04C02'
        );

        $choices[] = $this->createFourthStepChoice(
            'd7f6a7b7-7875-4852-ba01-b477f7e1ce52',
            'S04C03',
            'Son engagement européen',
            'S04C03'
        );

        $choices[] = $this->createFourthStepChoice(
            '82e6898a-e6a5-4531-becf-1d2c1fe10acd',
            'S04C04',
            'Sa nouveauté dans le monde politique',
            'S04C04'
        );

        $choices[] = $this->createFourthStepChoice(
            '538fab0b-1433-4fba-84a2-3d5cfacc9128',
            'S04C05',
            'Son féminisme',
            'S04C05'
        );

        // Persist all the things
        foreach ($choices as $choice) {
            $manager->persist($choice);
        }

        $manager->flush();
    }

    private function createNoStepChoice(string $uuid, string $key, string $label, string $content): TonMacronChoice
    {
        return static::createChoice($uuid, 0, $key, $label, $content);
    }

    private function createFirstStepChoice(string $uuid, string $key, string $label, string $content): TonMacronChoice
    {
        return static::createChoice($uuid, 1, $key, $label, $content);
    }

    private function createSecondStepChoice(string $uuid, string $key, string $label, string $content): TonMacronChoice
    {
        return static::createChoice($uuid, 2, $key, $label, $content);
    }

    private function createThirdStepChoice(string $uuid, string $key, string $label, string $content): TonMacronChoice
    {
        return static::createChoice($uuid, 3, $key, $label, $content);
    }

    private function createFourthStepChoice(string $uuid, string $key, string $label, string $content): TonMacronChoice
    {
        return static::createChoice($uuid, 4, $key, $label, $content);
    }

    private static function createChoice(
        string $uuid,
        int $step,
        string $key,
        string $label,
        string $content
    ): TonMacronChoice {
        return new TonMacronChoice(Uuid::fromString($uuid), $step, $key, $label, $content);
    }
}
