<?php

namespace App\DataFixtures\ORM;

use App\Entity\LegislativeCandidate;
use App\Entity\LegislativeDistrictZone;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @see https://fr.wikipedia.org/wiki/Liste_des_circonscriptions_l%C3%A9gislatives_de_la_France
 */
class LoadLegislativesData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($zones = $this->createLegislativeZones() as $zone) {
            $manager->persist($zone);
        }

        $candidate001 = $this->createLegislativeCandidate(
            $zones['0001'],
            "Troisième circonscription de l'Ain",
            '3',
            Genders::MALE,
            'Alban',
            'Martin',
            null,
            'alban-martin'
        );
        $candidate001->setEmailAddress('alban.martin@en-marche-dev.fr');
        $candidate001->setFacebookPageUrl('https://www.facebook.com/albanmartin-fake');
        $candidate001->setTwitterPageUrl('https://twitter.com/albanmartin-fake');
        $candidate001->setWebsiteUrl('https://albanmartin.en-marche-dev.fr');
        $candidate001->setDonationPageUrl('https://albanmartin.en-marche-dev.fr/dons');
        $candidate001->setCareer(LegislativeCandidate::CAREERS[0]);

        $manager->persist($candidate001);

        $manager->persist($this->createLegislativeCandidate(
            $zones['0073'],
            'Première circonscription de Savoie',
            '1',
            Genders::FEMALE,
            'Michelle',
            'Dumoulin',
            file_get_contents(__DIR__.'/../legislatives/geojson.json'),
            null,
            LegislativeCandidate::STATUS_WON
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0073'],
            'Deuxième circonscription de Savoie',
            '2',
            Genders::MALE,
            'Pierre',
            'Etchebest',
            null,
            null,
            LegislativeCandidate::STATUS_LOST
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0074'],
            'Cinquième circonscription de Haute-Savoie',
            '5',
            Genders::FEMALE,
            'Monique',
            'Albert',
            null,
            null,
            LegislativeCandidate::STATUS_WON
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0075'],
            'Première circonscription de Paris',
            '1',
            Genders::MALE,
            'Etienne',
            'de Monté-Cristo',
            file_get_contents(__DIR__.'/../legislatives/geojson-paris-1.json'),
            null,
            LegislativeCandidate::STATUS_QUALIFIED
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0075'],
            'Deuxième circonscription de Paris',
            '2',
            Genders::FEMALE,
            'Valérie',
            'Langlade',
            null,
            null,
            LegislativeCandidate::STATUS_WON
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0075'],
            'Troisième circonscription de Paris',
            '3',
            Genders::FEMALE,
            'Isabelle',
            'Piémontaise',
            null,
            null,
            LegislativeCandidate::STATUS_QUALIFIED
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0974'],
            'Première circonscription de la Réunion',
            '1',
            Genders::FEMALE,
            'Estelle',
            'Antonov',
            null,
            null,
            LegislativeCandidate::STATUS_WON
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0974'],
            'Deuxième circonscription de la Réunion',
            '2',
            Genders::MALE,
            'Jacques',
            'Arditi',
            null,
            null,
            LegislativeCandidate::STATUS_WON
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0974'],
            'Troisième circonscription de la Réunion',
            '3',
            Genders::MALE,
            'Albert',
            'Bérégovoy',
            null,
            null,
            LegislativeCandidate::STATUS_WON
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['1001'],
            'Première circonscription des Français établis hors de France',
            '1',
            Genders::MALE,
            'Franck',
            'de Lavalle',
            null,
            null,
            LegislativeCandidate::STATUS_LOST
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['1011'],
            'Onzième circonscription des Français établis hors de France',
            '11',
            Genders::FEMALE,
            'Emmanuelle',
            'Parfait',
            null,
            'emmanuelle-parfait',
            LegislativeCandidate::STATUS_QUALIFIED
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['002A'],
            'Première circonscription de Corse Sud',
            '1',
            Genders::MALE,
            'Michel',
            'Patulacci',
            null,
            'michel-patulacci',
            LegislativeCandidate::STATUS_LOST
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['002B'],
            'Première circonscription de Haute Corse',
            '1',
            Genders::FEMALE,
            'Josiane',
            'Dupuis',
            null,
            'josiane-dupuis'
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0019'],
            'Première circonscription de Corrèze',
            '1',
            Genders::MALE,
            'Paul',
            'Arty',
            null,
            'paul-arty'
        ));

        $manager->persist($this->createLegislativeCandidate(
            $zones['0021'],
            'Première circonscription de Côte d\'Or',
            '1',
            Genders::MALE,
            'Nathan',
            'Enquillé',
            null,
            'nathan-enquille'
        ));

        $manager->flush();
    }

    private function createLegislativeCandidate(
        LegislativeDistrictZone $zone,
        string $districtName,
        string $districtNumber,
        string $gender,
        string $firstName,
        string $lastName,
        string $geojson = null,
        string $slug = null,
        string $status = LegislativeCandidate::STATUS_NONE
    ): LegislativeCandidate {
        $directory = __DIR__.'/../../DataFixtures/legislatives';
        $description = sprintf('%s/description.txt', $directory);
        if ($slug && file_exists($path = sprintf('%s/%s.txt', $directory, $slug))) {
            $description = $path;
        }

        $candidate = new LegislativeCandidate();
        $candidate->setGender($gender);
        $candidate->setFirstName($firstName);
        $candidate->setLastName($lastName);
        $candidate->setDistrictZone($zone);
        $candidate->setDistrictName($districtName);
        $candidate->setDistrictNumber($districtNumber);
        $candidate->setGeojson($geojson);
        $candidate->setDescription(file_get_contents($description));
        $candidate->setCareer(LegislativeCandidate::CAREERS[1]);
        $candidate->setStatus($status);

        if ($slug) {
            $candidate->setSlug($slug);
        }

        return $candidate;
    }

    /**
     * @return LegislativeDistrictZone[]
     */
    private function createLegislativeZones(): array
    {
        // France Métropolitaine
        $zones['0001'] = LegislativeDistrictZone::createDepartmentZone('01', 'Ain', ['01']);
        $zones['0002'] = LegislativeDistrictZone::createDepartmentZone('02', 'Aisne', ['02']);
        $zones['0019'] = LegislativeDistrictZone::createDepartmentZone('19', 'Corrèze', ['19']);
        $zones['002A'] = LegislativeDistrictZone::createDepartmentZone('2A', 'Corse Sud', ['20', '2A', '2B', 'Corse']);
        $zones['002B'] = LegislativeDistrictZone::createDepartmentZone('2B', 'Haute Corse', ['20', '2A', '2B', 'Corse']);
        $zones['0021'] = LegislativeDistrictZone::createDepartmentZone('21', "Côte d'Or", ['21']);
        $zones['0073'] = LegislativeDistrictZone::createDepartmentZone('73', 'Savoie', ['73']);
        $zones['0074'] = LegislativeDistrictZone::createDepartmentZone('74', 'Haute-Savoie', ['74', 'Haute Savoie']);
        $zones['0075'] = LegislativeDistrictZone::createDepartmentZone('75', 'Paris', ['75']);
        $zones['0092'] = LegislativeDistrictZone::createDepartmentZone('92', 'Hauts-de-Seine', ['92', 'Hauts de Seine']);

        // Outre-Mer
        $zones['0971'] = LegislativeDistrictZone::createDepartmentZone('971', 'Guadeloupe', ['971']);
        $zones['0972'] = LegislativeDistrictZone::createDepartmentZone('972', 'Martinique', ['972']);
        $zones['0973'] = LegislativeDistrictZone::createDepartmentZone('973', 'Guyane', ['973']);
        $zones['0974'] = LegislativeDistrictZone::createDepartmentZone('974', 'La Réunion', ['974']);
        $zones['0975'] = LegislativeDistrictZone::createDepartmentZone('975', 'Saint-Pierre-et-Miquelon', ['975', 'Saint Pierre et Miquelon']);
        $zones['0976'] = LegislativeDistrictZone::createDepartmentZone('976', 'Mayotte', ['976']);
        $zones['0977'] = LegislativeDistrictZone::createDepartmentZone('977', 'Saint-Barthélemy', ['977', 'Saint Barthelemy']);
        $zones['0978'] = LegislativeDistrictZone::createDepartmentZone('978', 'Saint-Martin', ['978', 'Saint Martin']);
        $zones['0986'] = LegislativeDistrictZone::createDepartmentZone('986', 'Wallis-et-Futuna', ['986', 'Wallis et Futuna']);
        $zones['0987'] = LegislativeDistrictZone::createDepartmentZone('987', 'Polynésie française', ['987']);
        $zones['0988'] = LegislativeDistrictZone::createDepartmentZone('988', 'Nouvelle-Calédonie', ['988', 'Nouvelle Calédonie']);
        $zones['0989'] = LegislativeDistrictZone::createDepartmentZone('989', 'Clipperton', ['989']);

        // Circonscriptions des français à l'étranger
        $zones['1001'] = LegislativeDistrictZone::createRegionZone('1001', 'USA et Canada', ['US', 'CA', 'USA', 'CAN', 'États-Unis', 'Etats Unis', 'Canada']);
        $zones['1002'] = LegislativeDistrictZone::createRegionZone('1002', 'Amériques et Caraïbes', [
            'Antigua-et-Barbuda',
            'Argentine',
            'Bahamas',
            'Barbade',
            'Belize',
            'Bolivie',
            'Brésil',
            'Chili',
            'Colombie',
            'Costa Rica',
            'Cuba',
            'République dominicaine',
            'Dominique',
            'Équateur',
            'Grenade',
            'Guatemala',
            'Guyana',
            'Haïti',
            'Honduras',
            'Jamaïque',
            'Mexique',
            'Nicaragua',
            'Panama',
            'Paraguay',
            'Pérou',
            'Saint-Christophe-et-Niévès',
            'Sainte-Lucie',
            'Saint-Vincent-et-les Grenadines',
            'Salvador',
            'Suriname',
            'Trinité-et-Tobago',
            'Uruguay',
            'Venezuela',
        ]);

        $zones['1003'] = LegislativeDistrictZone::createRegionZone('1003', 'Europe du Nord et Pays Baltes', [
            'Danemark',
            'Estonie',
            'Finlande',
            'Irlande',
            'Islande',
            'Lettonie',
            'Lituanie',
            'Norvège',
            'Royaume-Uni',
            'Suède',
        ]);

        $zones['1004'] = LegislativeDistrictZone::createRegionZone('1004', 'Bénélux', [
            'Belgique',
            'Luxembourg',
            'Pays-Bas',
        ]);

        $zones['1005'] = LegislativeDistrictZone::createRegionZone('1005', 'Péninsule Ibérique et Monaco', [
            'Andorre',
            'Espagne',
            'Monaco',
            'Portugal',
        ]);

        $zones['1006'] = LegislativeDistrictZone::createRegionZone('1006', 'Suisse et Liechtenstein', [
            'Suisse',
            'Liechtenstein',
        ]);

        $zones['1007'] = LegislativeDistrictZone::createRegionZone('1007', 'Europe Centrale', [
            'Allemagne',
            'Albanie',
            'Autriche',
            'Bosnie-Herzégovine',
            'Bulgarie',
            'Croatie',
            'Hongrie',
            'Kosovo',
            'Macédoine',
            'Monténégro',
            'Pologne',
            'Roumanie',
            'Serbie',
            'Slovaquie',
            'Slovénie',
            'République tchèque',
        ]);

        $zones['1008'] = LegislativeDistrictZone::createRegionZone('1008', 'Pourtour méditerranéen', [
            'Chypre',
            'Grèce',
            'Israël',
            'Italie',
            'Malte',
            'Saint-Martin',
            'Saint-Siège',
            'Turquie',
        ]);

        $zones['1009'] = LegislativeDistrictZone::createRegionZone('1009', 'Afrique du Nord et Centrale', [
            'Algérie',
            'Burkina Faso',
            'Cap-Vert',
            'Côte d\'Ivoire',
            'Gambie',
            'Guinée',
            'Guinée-Bissau',
            'Liberia',
            'Libye',
            'Mali',
            'Maroc',
            'Mauritanie',
            'Niger',
            'Sénégal',
            'Sierra Leone',
            'Tunisie',
        ]);

        $zones['1010'] = LegislativeDistrictZone::createRegionZone('1010', 'Afrique du Sud et Moyen Orient', [
            'Afrique du Sud',
            'Émirats Arabes Unis',
            'Oman',
            'Qatar',
            // ...
            'Zimbabwe',
        ]);

        $zones['1011'] = LegislativeDistrictZone::createRegionZone('1011', 'Europe Orientale, Asie et Océanie', [
            // Europe Orientale
            'Arménie',
            'Azerbaïdjan',
            'Biélorussie',
            'Géorgie',
            'Moldavie',
            'Russie',
            'Ukraine',

            // Asie
            'Afghanistan',
            'Bangladesh',
            'Indonésie',
            'Chine',
            'Japon',
            // ...

            // Océanie
            'Australie',
            'Fidji',
            'Nouvelle-Zélande',
            // ...
            'Vanuatu',
        ]);

        return $zones;
    }
}
