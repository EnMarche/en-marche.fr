<?php

namespace App\DataFixtures\ORM;

use App\Entity\BoardMember\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadBoardMemberRoleData extends Fixture
{
    public const BOARD_MEMBER_ROLES = [
        'adherent' => 'Adhérent(e) membre de la société civile',
        'supervisor' => 'Animateur(trice) de comités locaux',
        'referent' => 'Référent(e) territorial(e)',
        'deputy' => 'Député(e) national(e)',
        'european_deputy' => 'Député(e) européen(ne)',
        'minister' => 'Membre du gouvernement',
        'senator' => 'Sénateur(trice)',
        'consular' => 'Conseiller(ère) Consulaire',
        'president_larem' => 'Président(e) du groupe LaREM d\'un exécutif local',
        'president' => 'Président(e) de région / département / assemblée territoriale',
        'mayor_less' => 'Maire d\'une commune de moins de 50 000 habitants',
        'mayor_more' => 'Maire d\'une commune de plus de 50 000 habitants',
        'president_less' => 'Président(e) d\'établissement de coopération intercommunale EPCI < 100 000 habitants',
        'president_more' => 'Président(e) d\'établissement de coopération intercommunale EPCI > 100 000 habitants',
        'personality' => 'Personnalité',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::BOARD_MEMBER_ROLES as $code => $name) {
            $role = new Role($code, $name);
            $manager->persist($role);
            $this->addReference(strtolower($code), $role);
        }

        $manager->flush();
    }
}
