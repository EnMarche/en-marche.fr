<?php

namespace App\DataFixtures\ORM;

use App\Entity\Timeline\Manifesto;
use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Profile;
use App\Entity\Timeline\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadTimelineData extends Fixture
{
    public const PROFILES = [
        'TP001' => [
            'title' => [
                'fr' => 'Chef d\'entreprise',
                'en' => 'Entrepreneur',
            ],
            'slug' => [
                'fr' => 'chef-d-entreprise',
                'en' => 'entrepreneur',
            ],
            'description' => [
                'fr' => 'Les mesures pour chefs d\'entreprise.',
                'en' => 'Measures for entrepreneurs.',
            ],
        ],
        'TP002' => [
            'title' => [
                'fr' => '12-25 ans',
                'en' => '12-25 years',
            ],
            'slug' => [
                'fr' => '12-25-ans',
                'en' => '12-25-years',
            ],
            'description' => [
                'fr' => 'Les profils de 12 à 25ans.',
                'en' => 'Profiles from 12 to 25 years old.',
            ],
        ],
        'TP003' => [
            'title' => [
                'fr' => '25-35 ans',
                'en' => '25-35 years',
            ],
            'slug' => [
                'fr' => '25-35-ans',
                'en' => '25-35-years',
            ],
            'description' => [
                'fr' => 'Les profils de 25 à 35ans.',
                'en' => 'Profiles from 25 to 35 years old.',
            ],
        ],
        'TP004' => [
            'title' => [
                'fr' => '35-45 ans',
                'en' => '35-45 years',
            ],
            'slug' => [
                'fr' => '35-45-ans',
                'en' => '35-45-years',
            ],
            'description' => [
                'fr' => 'Les profils de 35 à 45ans.',
                'en' => 'Profiles from 35 to 45 years old.',
            ],
        ],
        'TP005' => [
            'title' => [
                'fr' => '45 ans et plus',
                'en' => '45 years and over',
            ],
            'slug' => [
                'fr' => '45-ans-et-plus',
                'en' => '45-years-and-over',
            ],
            'description' => [
                'fr' => 'Les profils de 45ans et plus.',
                'en' => 'Profiles of 45 years and over.',
            ],
        ],
    ];

    public const THEMES = [
        'TT001' => [
            'title' => [
                'fr' => 'Action publique et fonction publique',
                'en' => 'Public action and public service',
            ],
            'slug' => [
                'fr' => 'action-et-fonction-publique',
                'en' => 'public-action-and-public-service',
            ],
            'description' => [
                'fr' => 'Action publique et fonction publique',
                'en' => 'Public action and public service',
            ],
            'featured' => true,
        ],
        'TT002' => [
            'title' => [
                'fr' => 'Alternance / Apprentissage',
                'en' => 'Alternation / Apprenticeship',
            ],
            'slug' => [
                'fr' => 'alternance-apprentissage',
                'en' => 'alternation-apprenticeship',
            ],
            'description' => [
                'fr' => 'Alternance / Apprentissage',
                'en' => 'Alternation / Apprenticeship',
            ],
        ],
        'TT003' => [
            'title' => [
                'fr' => 'Agriculture',
                'en' => 'Agriculture',
            ],
            'slug' => [
                'fr' => 'agriculture',
                'en' => 'agriculture',
            ],
            'description' => [
                'fr' => 'Agriculture',
                'en' => 'Agriculture',
            ],
            'featured' => true,
        ],
        'TT004' => [
            'title' => [
                'fr' => 'Culture',
                'en' => 'Culture',
            ],
            'slug' => [
                'fr' => 'culture',
                'en' => 'culture',
            ],
            'description' => [
                'fr' => 'Culture',
                'en' => 'Culture',
            ],
        ],
        'TT005' => [
            'title' => [
                'fr' => 'Défense',
                'en' => 'Defense',
            ],
            'slug' => [
                'fr' => 'defense',
                'en' => 'defense',
            ],
            'description' => [
                'fr' => 'Défense',
                'en' => 'Defense',
            ],
        ],
    ];

    public const MANIFESTOS = [
        'TMA001' => [
            'title' => [
                'fr' => 'Présidentielle 2017',
                'en' => 'Presidential 2017',
            ],
            'slug' => [
                'fr' => 'presidentielles-2017',
                'en' => 'presidential-2017',
            ],
            'description' => [
                'fr' => 'Le programme présidentiel 2017.',
                'en' => 'The presidential manifesto 2017.',
            ],
        ],
        'TMA002' => [
            'title' => [
                'fr' => 'Européennes 2019',
                'en' => 'Europeans 2019',
            ],
            'slug' => [
                'fr' => 'europeennes-2019',
                'en' => 'europeans-2019',
            ],
            'description' => [
                'fr' => 'Le programme européennes 2019.',
                'en' => 'The europeans manifesto 2019',
            ],
        ],
        'TMA003' => [
            'title' => [
                'fr' => 'Hors programme',
                'en' => 'Out of manifesto',
            ],
            'slug' => [
                'fr' => 'hors-programme',
                'en' => 'out-of-manifesto',
            ],
            'description' => [
                'fr' => 'Hors programme.',
                'en' => 'Out of manifesto.',
            ],
        ],
    ];

    public const MEASURES = [
        'TM001' => [
            'title' => [
                'fr' => 'Élargir les horaires d’ouverture des services publics',
                'en' => 'Expand the opening hours of public services',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003'],
            'manifesto' => 'TMA001',
        ],
        'TM002' => [
            'title' => [
                'fr' => 'Créer 10 000 postes de policiers et gendarmes en plus',
                'en' => 'Create 10,000 more police positions',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002'],
            'manifesto' => 'TMA001',
        ],
        'TM003' => [
            'title' => [
                'fr' => 'Créer 12 000 postes pour les classes de CP et de CE1 dans les zones prioritaires',
                'en' => 'Create 12,000 positions for CP and CE1 classes in priority areas',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003', 'TP004'],
            'manifesto' => 'TMA001',
        ],
        'TM004' => [
            'title' => [
                'fr' => 'Réduire de 120 000 le nombre d\'emplois publics',
                'en' => 'Reduce by 120,000 the number of public jobs',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
            'manifesto' => 'TMA001',
        ],
        'TM005' => [
            'title' => [
                'fr' => 'Rendre les ministres comptables du respect de la dépense publique',
                'en' => 'Make ministers accountable for respecting public spending',
            ],
            'status' => Measure::STATUS_DONE,
            'themes' => ['TT001', 'TT002', 'TT003'],
            'profiles' => ['TP005'],
            'manifesto' => 'TMA001',
        ],
        'TM006' => [
            'title' => [
                'fr' => 'Mettre fin à l’évolution uniforme des rémunérations des fonctions publiques',
                'en' => 'Put an end to the uniform evolution of public service pay',
            ],
            'status' => Measure::STATUS_DONE,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
            'manifesto' => 'TMA001',
        ],
        'TM007' => [
            'title' => [
                'fr' => 'Confier aux services des métropoles les compétences de leurs conseils départementaux',
                'en' => 'Entrust the departments of the cities with the skills of their departmental councils',
            ],
            'status' => Measure::STATUS_DONE,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
            'manifesto' => 'TMA001',
        ],
        'TM008' => [
            'title' => [
                'fr' => 'Basculer les cotisations salariales vers la CSG',
                'en' => 'Switch employee contributions to CSG',
            ],
            'status' => Measure::STATUS_DONE,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
            'manifesto' => 'TMA001',
        ],
        'TM009' => [
            'title' => [
                'fr' => 'Créer une aide unique selon la taille de l’entreprise et le niveau de qualification de l’apprenti',
                'en' => 'Create a unique help depending on the size of the company',
            ],
            'status' => Measure::STATUS_UPCOMING,
            'themes' => ['TT002'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
            'manifesto' => 'TMA001',
        ],
        'TM010' => [
            'title' => [
                'fr' => 'Créer une aide unique selon la taille de l’entreprise et le niveau de qualification de l’apprenti',
                'en' => 'Create a one-stop shop for businesses for learning and applying for help',
            ],
            'status' => Measure::STATUS_UPCOMING,
            'themes' => ['TT002'],
            'profiles' => ['TP001', 'TP002'],
            'manifesto' => 'TMA001',
        ],
        'TM011' => [
            'title' => [
                'fr' => 'Rassembler les deux contrats d\'alternance en un contrat unique',
                'en' => 'Bring the two work-study contracts into a single contract',
            ],
            'status' => Measure::STATUS_UPCOMING,
            'themes' => ['TT002'],
            'profiles' => ['TP001', 'TP002'],
            'manifesto' => 'TMA001',
        ],
        'TM012' => [
            'title' => [
                'fr' => 'Affecter la totalité de la taxe d’apprentissage au financement de l’apprentissage',
                'en' => 'Allocate the Learning Tax to Learning Funding',
            ],
            'status' => Measure::STATUS_UPCOMING,
            'themes' => ['TT002', 'TT001'],
            'profiles' => ['TP001'],
            'manifesto' => 'TMA001',
        ],
        'TM013' => [
            'title' => [
                'fr' => 'Unifier la grille de rémunération des alternants',
                'en' => 'Unify the remuneration grid for alternates',
            ],
            'status' => Measure::STATUS_UPCOMING,
            'themes' => ['TT002'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
            'manifesto' => 'TMA001',
        ],
        'TM014' => [
            'title' => [
                'fr' => 'Confier aux branches l\'augmentation des planchers de rémunération des alternants',
                'en' => 'To entrust to the branches the increase of the remuneration floors of the alternates',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT003'],
            'profiles' => ['TP001'],
            'manifesto' => 'TMA001',
        ],
        'TM015' => [
            'title' => [
                'fr' => 'Inscrire dans la loi les principes de la rémunération des apprentis et les montants plancher',
                'en' => 'Enclose in the law the principles of apprentice remuneration and the amounts',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT004'],
            'profiles' => ['TP002', 'TP003'],
            'manifesto' => 'TMA001',
        ],
        'TM016' => [
            'title' => [
                'fr' => 'Définir programmes et organisation des formations avec les branches professionnelles',
                'en' => 'Define programs and organization of training with professional branches',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP001'],
            'manifesto' => 'TMA002',
        ],
        'TM017' => [
            'title' => [
                'fr' => 'Développer un « sas » de préparation à l’alternance à la fin du collège',
                'en' => 'Develop an "airlock" for work-study preparation at the end of secondary school',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT003'],
            'profiles' => ['TP001', 'TP002'],
            'manifesto' => 'TMA003',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::PROFILES as $reference => $metadatas) {
            $profil = new Profile();

            $translation = $profil->translate('fr');
            $translation->setTitle($metadatas['title']['fr']);
            $translation->setSlug($metadatas['slug']['fr']);
            $translation->setDescription($metadatas['description']['fr']);

            $translation = $profil->translate('en');
            $translation->setTitle($metadatas['title']['en']);
            $translation->setSlug($metadatas['slug']['en']);
            $translation->setDescription($metadatas['description']['en']);

            $this->addReference($reference, $profil);

            $profil->mergeNewTranslations();

            $manager->persist($profil);
        }

        foreach (self::THEMES as $reference => $metadatas) {
            $theme = new Theme($metadatas['featured'] ?? false);

            $translation = $theme->translate('fr');
            $translation->setTitle($metadatas['title']['fr']);
            $translation->setSlug($metadatas['slug']['fr']);
            $translation->setDescription($metadatas['description']['fr']);

            $translation = $theme->translate('en');
            $translation->setTitle($metadatas['title']['en']);
            $translation->setSlug($metadatas['slug']['en']);
            $translation->setDescription($metadatas['description']['en']);

            $this->addReference($reference, $theme);

            $theme->mergeNewTranslations();

            $manager->persist($theme);
        }

        foreach (self::MANIFESTOS as $reference => $metadatas) {
            $manifesto = new Manifesto();

            $translation = $manifesto->translate('fr');
            $translation->setTitle($metadatas['title']['fr']);
            $translation->setSlug($metadatas['slug']['fr']);
            $translation->setDescription($metadatas['description']['fr']);

            $translation = $manifesto->translate('en');
            $translation->setTitle($metadatas['title']['en']);
            $translation->setSlug($metadatas['slug']['en']);
            $translation->setDescription($metadatas['description']['en']);

            $manifesto->mergeNewTranslations();

            $this->addReference($reference, $manifesto);

            $manager->persist($manifesto);
        }

        $manager->flush();

        foreach (self::MEASURES as $reference => $metadatas) {
            $measure = new Measure(
                $metadatas['status'],
                array_map(function (string $profileReference) {
                    return $this->getReference($profileReference);
                }, $metadatas['profiles']),
                array_map(function (string $themeReference) {
                    return $this->getReference($themeReference);
                }, $metadatas['themes']),
                $this->getReference($metadatas['manifesto']),
                null,
                true
            );

            $measure->translate('fr')->setTitle($metadatas['title']['fr']);
            $measure->translate('en')->setTitle($metadatas['title']['en']);

            $measure->mergeNewTranslations();

            $manager->persist($measure);
        }

        $manager->flush();
    }
}
