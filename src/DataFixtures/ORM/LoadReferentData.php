<?php

namespace App\DataFixtures\ORM;

use App\Entity\Referent;
use App\Entity\ReferentArea;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadReferentData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($areas = $this->createReferentArea() as $area) {
            $manager->persist($area);
        }

        $candidate001 = $this->createReferent(
            [$areas['USA'], $areas['21']],
            "Côte d'Or",
            Genders::MALE,
            'Nicolas',
            'Bordes',
            null,
            'nicolas-bordes',
            Referent::ENABLED
        );
        $candidate001->setEmailAddress('nicolas.bordes@en-marche-dev.fr');
        $candidate001->setFacebookPageUrl('https://www.facebook.com/nyko24');
        $candidate001->setTwitterPageUrl('https://twitter.com/nyko24');

        $candidate002 = $this->createReferent(
            [$areas['75002'], $areas['75001']],
            'Paris 2e et Paris 1er',
            Genders::MALE,
            'Alban',
            'Martin',
            null,
            'alban-martin',
            Referent::DISABLED
        );
        $candidate002->setEmailAddress('alban.martin@en-marche-dev.fr');
        $candidate002->setFacebookPageUrl('https://www.facebook.com/fakeaccount');
        $candidate002->setTwitterPageUrl('https://twitter.com/fakeaccount');

        $candidate003 = $this->createReferent(
            [$areas['92']],
            'Haut-de-seine',
            Genders::MALE,
            'Jean',
            'Dupont',
            null,
            'jean',
            Referent::ENABLED
        );
        $candidate003->setEmailAddress('referent@en-marche-dev.fr');
        $candidate003->setFacebookPageUrl('https://www.facebook.com/fakeaccount');
        $candidate003->setTwitterPageUrl('https://twitter.com/fakeaccount');

        $manager->persist($candidate001);
        $manager->persist($candidate002);
        $manager->persist($candidate003);

        $manager->flush();

        $this->setReference('referent1', $candidate001);
        $this->setReference('referent2', $candidate002);
        $this->setReference('referent3', $candidate003);
    }

    private function createReferent(
        array $areas,
        string $areaLabel,
        string $gender,
        string $firstName,
        string $lastName,
        string $geojson = null,
        string $slug = null,
        string $status = Referent::ENABLED
    ): Referent {
        $directory = __DIR__.'/../../DataFixtures/legislatives';
        $description = sprintf('%s/description.txt', $directory);
        if ($slug && file_exists($path = sprintf('%s/%s.txt', $directory, $slug))) {
            $description = $path;
        }

        $referent = new Referent();
        $referent->setGender($gender);
        $referent->setFirstName($firstName);
        $referent->setLastName($lastName);
        $referent->setGeojson($geojson);
        $referent->setDescription(file_get_contents($description));
        $referent->setStatus($status);
        $referent->setAreaLabel($areaLabel);
        $referent->setMedia();

        foreach ($areas as $area) {
            $referent->addArea($area);
        }

        if ($slug) {
            $referent->setSlug($slug);
        }

        return $referent;
    }

    /**
     * @return ReferentArea[]
     */
    private function createReferentArea(): array
    {
        // France Métropolitaine
        $zones['01'] = ReferentArea::createDepartmentZone('01', 'Ain', ['01']);
        $zones['02'] = ReferentArea::createDepartmentZone('02', 'Aisne', ['02']);
        $zones['19'] = ReferentArea::createDepartmentZone('19', 'Corrèze', ['19']);
        $zones['2A'] = ReferentArea::createDepartmentZone('2A', 'Corse Sud', ['20', '2A', '2B', 'Corse']);
        $zones['2B'] = ReferentArea::createDepartmentZone('2B', 'Haute Corse', ['20', '2A', '2B', 'Corse']);
        $zones['21'] = ReferentArea::createDepartmentZone('21', "Côte d'Or", ['21']);
        $zones['73'] = ReferentArea::createDepartmentZone('73', 'Savoie', ['73']);
        $zones['74'] = ReferentArea::createDepartmentZone('74', 'Haute-Savoie', ['74', 'Haute Savoie']);
        $zones['75'] = ReferentArea::createDepartmentZone('75', 'Paris', ['75']);
        $zones['92'] = ReferentArea::createDepartmentZone('92', 'Hauts-de-Seine', ['92', 'Hauts de Seine']);

        // Outre-Mer
        $zones['971'] = ReferentArea::createDepartmentZone('971', 'Guadeloupe', ['971']);
        $zones['972'] = ReferentArea::createDepartmentZone('972', 'Martinique', ['972']);
        $zones['973'] = ReferentArea::createDepartmentZone('973', 'Guyane', ['973']);
        $zones['974'] = ReferentArea::createDepartmentZone('974', 'La Réunion', ['974']);
        $zones['975'] = ReferentArea::createDepartmentZone('975', 'Saint-Pierre-et-Miquelon', ['975', 'Saint Pierre et Miquelon']);
        $zones['976'] = ReferentArea::createDepartmentZone('976', 'Mayotte', ['976']);
        $zones['977'] = ReferentArea::createDepartmentZone('977', 'Saint-Barthélemy', ['977', 'Saint Barthelemy']);
        $zones['978'] = ReferentArea::createDepartmentZone('978', 'Saint-Martin', ['978', 'Saint Martin']);
        $zones['986'] = ReferentArea::createDepartmentZone('986', 'Wallis-et-Futuna', ['986', 'Wallis et Futuna']);
        $zones['987'] = ReferentArea::createDepartmentZone('987', 'Polynésie française', ['987']);
        $zones['988'] = ReferentArea::createDepartmentZone('988', 'Nouvelle-Calédonie', ['988', 'Nouvelle Calédonie']);
        $zones['989'] = ReferentArea::createDepartmentZone('989', 'Clipperton', ['989']);

        // Circonscriptions des français à l'étranger
        $zones['USA'] = ReferentArea::createRegionZone('USA', 'USA et Canada', ['US', 'CA', 'USA', 'CAN', 'États-Unis', 'Etats Unis', 'Canada']);
        $zones['AMC'] = ReferentArea::createRegionZone('AMC', 'Amériques et Caraïbes', [
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

        $zones['EUN'] = ReferentArea::createRegionZone('EUN', 'Europe du Nord et Pays Baltes', [
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

        $zones['BEN'] = ReferentArea::createRegionZone('BEN', 'Bénélux', [
            'Belgique',
            'Luxembourg',
            'Pays-Bas',
        ]);

        $zones['CH'] = ReferentArea::createRegionZone('CH', 'Suisse et Liechtenstein', [
            'Suisse',
            'Liechtenstein',
        ]);

        $zones['EUC'] = ReferentArea::createRegionZone('EUC', 'Europe Centrale', [
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

        // Arrondissement
        $zones['75002'] = ReferentArea::createDistrict('75002', 'Paris 2e', ['Paris 2e']);
        $zones['75001'] = ReferentArea::createDistrict('75001', 'Paris 1e', ['Paris 1e']);
        $zones['75003'] = ReferentArea::createDistrict('75003', 'Paris 3e', ['Paris 3e']);

        return $zones;
    }
}
