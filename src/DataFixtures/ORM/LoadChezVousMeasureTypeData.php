<?php

namespace App\DataFixtures\ORM;

use App\Entity\ChezVous\MeasureType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadChezVousMeasureTypeData extends Fixture
{
    private const TYPES = [
        [
            'code' => 'mission_bern',
            'label' => 'Projet de rénovation du patrimoine financé par la mission Bern',
            'sourceLink' => 'https://www.missionbern.fr/projets/',
            'sourceLabel' => 'missionbern.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=culture',
            'eligibilityLink' => null,
        ],
        [
            'code' => 'dedoublement_classes',
            'label' => 'Dédoublement des classes de CP et CE1',
            'sourceLink' => 'https://data.education.gouv.fr/explore/dataset/fr-en-ecoles-ep/information/',
            'sourceLabel' => 'data.education.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=education',
            'eligibilityLink' => null,
        ],
        [
            'code' => 'maison_service_accueil_public',
            'label' => 'Généralisation des maisons de service et d\'accueil au public',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/fiches-didentite-des-maisons-de-services-au-public/',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=action-publique',
            'eligibilityLink' => null,
        ],
        [
            'code' => 'suppression_taxe_habitation',
            'label' => 'Suppression de la taxe d’habitation',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/impots-locaux/',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,logement',
            'eligibilityLink' => 'https://www.impots.gouv.fr/portail/simulateur-de-la-reforme-de-la-taxe-dhabitation-pour-2019',
        ],
        [
            'code' => 'pass_culture',
            'label' => 'Mise en place du Pass Culture',
            'sourceLink' => 'https://www.service-public.fr/particuliers/actualites/A13201',
            'sourceLabel' => 'service-public.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=culture',
            'eligibilityLink' => 'https://pass.culture.fr/',
        ],
        [
            'code' => 'creation_entreprises',
            'label' => 'Création nettes d\'entreprises',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/base-sirene-des-entreprises-et-de-leurs-etablissements-siren-siret/',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,travail,entreprises',
            'eligibilityLink' => null,
        ],
        [
            'code' => 'baisse_nombre_chomeurs',
            'label' => 'Baisse du nombre de chômeurs',
            'sourceLink' => 'https://statistiques.pole-emploi.org/stmt/trsl?fa=M&lb=0',
            'sourceLabel' => 'pole-emploi.org',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,travail,entreprises,industrie,apprentissage,dialogue-social',
            'eligibilityLink' => null,
        ],
        [
            'code' => 'emplois_francs',
            'label' => 'Emplois francs',
            'sourceLink' => 'https://travail-emploi.gouv.fr/emploi/emplois-francs/',
            'sourceLabel' => 'travail-emploi.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,travail,entreprises,industrie,apprentissage,dialogue-social,territoires',
            'eligibilityLink' => 'https://travail-emploi.gouv.fr/emploi/emplois-francs/',
        ],
        [
            'code' => 'couverture_fibre',
            'label' => 'Couverture en fibre de tout le territoire',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/le-marche-du-haut-et-tres-haut-debit-fixe-deploiements/#resource-b8d4f328-0dd4-40a7-9b68-b2a2266abddb',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=numerique',
            'eligibilityLink' => null,
        ],
        [
            'code' => 'prime_conversion_automobile',
            'label' => 'Prime à la conversion automobile',
            'sourceLink' => 'https://www.primealaconversion.gouv.fr/dboneco/accueil/',
            'sourceLabel' => 'primealaconversion.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=ecologie',
            'eligibilityLink' => 'https://www.primealaconversion.gouv.fr/dboneco/accueil/',
        ],
        [
            'code' => 'cheque_energie',
            'label' => 'Chèque énergie',
            'sourceLink' => 'https://chequeenergie.gouv.fr/',
            'sourceLabel' => 'chequeenergie.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=ecologie',
            'eligibilityLink' => 'https://chequeenergie.gouv.fr/',
        ],
        [
            'code' => 'conversion_surface_agricole_bio',
            'label' => 'Conversion de la surface agricole en bio',
            'sourceLink' => 'https://www.agencebio.org/vos-outils/les-chiffres-cles/',
            'sourceLabel' => 'agencebio.org',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=ecologie',
            'eligibilityLink' => null,
        ],
        [
            'code' => 'quartier_reconquete_republicaine',
            'label' => 'Création d\'un quartier de reconquête républicaine',
            'sourceLink' => 'https://www.interieur.gouv.fr/Espace-presse/Dossiers-de-presse/Un-an-de-la-police-de-securite-du-quotidien',
            'sourceLabel' => 'interieur.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=securite',
            'eligibilityLink' => null,
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::TYPES as $type) {
            $measureType = new MeasureType($type['code'], $type['label']);
            $measureType->setSourceLabel($type['sourceLabel']);
            $measureType->setSourceLink($type['sourceLink']);
            $measureType->setOldolfLink($type['oldolfLink']);
            $measureType->setEligibilityLink($type['eligibilityLink']);
            $measureType->setCitizenProjectsLink('https://en-marche.fr/projets-citoyens');
            $measureType->setIdeasWorkshopLink('https://en-marche.fr/atelier-des-idees/proposer');

            $manager->persist($measureType);
            $this->setReference(sprintf('chez-vous-measure-type-%s', $type['code']), $measureType);
        }

        $manager->flush();
    }
}
