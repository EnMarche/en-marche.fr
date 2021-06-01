<?php

namespace App\DataFixtures\ORM;

use App\Entity\Biography\ExecutiveOfficeMember;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadExecutiveOfficeMemberData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $member1 = new ExecutiveOfficeMember(
            null,
            'Christophe',
            'Castaner',
            "Secrétaire d'État auprès du Premier ministre, chargé des Relations avec le Parlement.",
            "Christophe Castaner, né le 3 janvier 1966 à Ollioules, est un juriste et homme politique français. Député pour les Alpes-de-Haute-Provence, tête de liste du Parti socialiste aux élections régionales de 2015 en Provence-Alpes-Côte d'Azur, il rejoint le mouvement En marche d'Emmanuel Macron en 2016",
            true,
            'Délégué général du mouvement',
            true,
            false
        );

        $member1->setFacebookProfile('https://www.facebook.com/christophe.castaner');
        $member1->setTwitterProfile('https://twitter.com/ccastaner');
        $member1->setInstagramProfile('https://www.instagram.com/christophe_castaner');
        $member1->setLinkedInProfile('https://www.linkedin.com/in/christophe-castaner-7a96203a');

        $member2 = new ExecutiveOfficeMember(
            null,
            'Caroline',
            'Abadie',
            "Députée LaREM de l'Isère, chargée d'animer la réflexion sur les libertés publiques, la justice, la défense et la sécurité intérieure.",
            "Députée LaREM de l'Isère, chargée d'animer la réflexion sur les libertés publiques, la justice, la défense et la sécurité intérieure, ainsi que sur les nouveaux risques. Déléguée adjointe : Marie-Hélène REGNIER, référente LaREM dans l'Aude.",
            true,
            'Déléguée à la Garantie des libertés et de la sécurité',
            false,
            false
        );

        $member2->setFacebookProfile('https://facebook.com/EnMarche');
        $member2->setTwitterProfile('https://twitter.com/enmarchefr');
        $member2->setInstagramProfile('https://www.instagram.com/enmarchefr');
        $member2->setLinkedInProfile('https://fr.linkedin.com/company/en-marche');

        $member3 = new ExecutiveOfficeMember(
            null,
            'Béatrice',
            'Agamennone',
            "47 ans, mariée, 3 enfants, référente LaREM en Moselle depuis octobre 2016. Elle est ingénieur en chef des travaux publics de l'Etat, directrice adjointe du Centre d'études et d'expertises sur l'Environnement, les Mobilités et l'Aménagement, Est.",
            "47 ans, mariée, 3 enfants, référente LaREM en Moselle depuis octobre 2016. Elle est ingénieur en chef des travaux publics de l'Etat, directrice adjointe du Centre d'études et d'expertises sur l'Environnement, les Mobilités et l'Aménagement, Est. Elle est adjointe au Maire de Metz, vice-présidente de l'agence d'urbanisme d'Agglomérations de Moselle et conseillère communautaire de Metz Métropole.",
            true,
            'Référente',
            false,
            false
        );

        $member3->setFacebookProfile('https://facebook.com/EnMarche');
        $member3->setTwitterProfile('https://twitter.com/enmarchefr');
        $member3->setInstagramProfile('https://www.instagram.com/enmarchefr');
        $member3->setLinkedInProfile('https://fr.linkedin.com/company/en-marche');

        $member4 = new ExecutiveOfficeMember(
            null,
            'Laëtitia',
            'Avia',
            "32 ans, députée de Paris. Engagée de la 1ère heure auprès d'Emmanuel Macron, Laetitia a été experte du programme Justice puis référente territoriale d'En Marche.",
            "32 ans, députée de Paris. Engagée de la 1ère heure auprès d'Emmanuel Macron, Laetitia a été experte du programme Justice puis référente territoriale d'En Marche. Issue des quartiers populaires, engagée pour l'égalité des chances, elle est avocate au Barreau de Paris où elle a fondé son cabinet.",
            true,
            'Déléguée à la Communication',
            false,
            false
        );

        $member4->setFacebookProfile('https://facebook.com/EnMarche');
        $member4->setTwitterProfile('https://twitter.com/enmarchefr');
        $member4->setInstagramProfile('https://www.instagram.com/enmarchefr');
        $member4->setLinkedInProfile('https://fr.linkedin.com/company/en-marche');

        $member5 = new ExecutiveOfficeMember(
            null,
            'Pierre',
            'P.',
            'Délégué général adjoint à LaREM',
            'Délégué général adjoint à LaREM',
            true,
            'Délégué général adjoint',
            false,
            true
        );
        $member5->setFacebookProfile('https://facebook.com/EnMarche');
        $member5->setTwitterProfile('https://twitter.com/enmarchefr');
        $member5->setInstagramProfile('https://www.instagram.com/enmarchefr');
        $member5->setLinkedInProfile('https://fr.linkedin.com/company/en-marche');

        $manager->persist($member1);
        $manager->persist($member2);
        $manager->persist($member3);
        $manager->persist($member4);
        $manager->persist($member5);

        $manager->flush();
    }
}
