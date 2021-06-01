<?php

namespace App\DataFixtures\ORM;

use App\Admin\AdministratorFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadAdminData extends Fixture
{
    private $administratorFactory;

    public function __construct(AdministratorFactory $administratorFactory)
    {
        $this->administratorFactory = $administratorFactory;
    }

    public function load(ObjectManager $manager)
    {
        $adminRoles = [
            'ROLE_ADMIN_DASHBOARD',
            'ROLE_ADMIN_MEDIAS',
            'ROLE_ADMIN_CONTENT',
            'ROLE_ADMIN_HOME',
            'ROLE_ADMIN_PROPOSALS',
            'ROLE_ADMIN_ORDERS',
            'ROLE_ADMIN_FACEBOOK_PROFILES',
            'ROLE_ADMIN_REDIRECTIONS',
            'ROLE_ADMIN_NEWSLETTER',
            'ROLE_ADMIN_JE_MARCHE',
            'ROLE_ADMIN_UNREGISTRATIONS',
            'ROLE_ADMIN_TON_MACRON',
            'ROLE_ADMIN_MY_EUROPE',
            'ROLE_ADMIN_LEGISLATIVES',
            'ROLE_ADMIN_ADHERENTS',
            'ROLE_ADMIN_SUMMARY',
            'ROLE_ADMIN_SKILLS',
            'ROLE_ADMIN_COMMITTEES',
            'ROLE_ADMIN_COMMITTEES_MERGE',
            'ROLE_ADMIN_COMMITTEE_DESIGNATION',
            'ROLE_ADMIN_EVENTS',
            'ROLE_ADMIN_INSTITUTIONAL_EVENTS',
            'ROLE_ADMIN_CITIZEN_ACTIONS',
            'ROLE_ADMIN_CITIZEN_PROJECTS',
            'ROLE_ADMIN_TURNKEY_PROJECTS',
            'ROLE_ADMIN_REPORTS',
            'ROLE_ADMIN_PROCURATIONS',
            'ROLE_ADMIN_ELECTIONS',
            'ROLE_ADMIN_DONATIONS',
            'ROLE_ADMIN_EMAIL',
            'ROLE_ADMIN_ADHERENT_TAGS',
            'ROLE_ADMIN_REFERENT_TAGS',
            'ROLE_ADMIN_REFERENTS',
            'ROLE_ADMIN_REFERENTS_AREAS',
            'ROLE_ADMIN_TIMELINE',
            'ROLE_ADMIN_CLIENTS',
            'ROLE_ADMIN_ORGANIGRAMM',
            'ROLE_ADMIN_MOOC',
            'ROLE_ADMIN_EMAIL_SUBSCRIPTION_TYPES',
            'ROLE_ADMIN_LABEL',
            'ROLE_ADMIN_BIOGRAPHY',
            'ROLE_ADMIN_JECOUTE',
            'ROLE_ADMIN_IDEAS_WORKSHOP',
            'ROLE_ADMIN_ASSESSOR',
            'ROLE_ADMIN_APPLICATION_REQUEST',
            'ROLE_ADMIN_CHEZ_VOUS',
            'ROLE_ADMIN_FINANCE',
            'ROLE_ADMIN_PROGRAMMATIC_FOUNDATION',
            'ROLE_ADMIN_ELECTION_CITY_CARD',
            'ROLE_ADMIN_ELECTION_CITY_CARD_MANAGERS',
            'ROLE_ADMIN_ELECTED_REPRESENTATIVE',
            'ROLE_ADMIN_TERRITORIAL_COUNCIL',
            'ROLE_ADMIN_FILES',
        ];

        $writerRoles = [
            'ROLE_ADMIN_DASHBOARD',
            'ROLE_ADMIN_MEDIAS',
            'ROLE_ADMIN_CONTENT',
            'ROLE_ADMIN_HOME',
            'ROLE_ADMIN_PROPOSALS',
            'ROLE_ADMIN_ORDERS',
            'ROLE_ADMIN_FACEBOOK_PROFILES',
            'ROLE_ADMIN_REDIRECTIONS',
        ];

        $superAdmin2fa = $this->administratorFactory->createFromArray([
            'email' => 'titouan.galopin@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
            'secret' => 'D3GU3BR4LUDK5NWR',
        ]);

        $admin = $this->administratorFactory->createFromArray([
            'email' => 'jean.dupond@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
            'activated' => false,
        ]);
        $this->setReference('administrator-1', $admin);

        $writer = $this->administratorFactory->createFromArray([
            'email' => 'martin.pierre@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $writerRoles,
        ]);

        $manager->persist($this->administratorFactory->createFromArray([
            'email' => 'admin@en-marche-dev.fr',
            'password' => 'admin',
            'roles' => $adminRoles,
        ]));

        $manager->persist($superadmin = $this->administratorFactory->createFromArray([
            'email' => 'superadmin@en-marche-dev.fr',
            'password' => 'superadmin',
            'roles' => ['ROLE_SUPER_ADMIN'],
        ]));
        $this->setReference('administrator-2', $superadmin);

        $manager->persist($this->administratorFactory->createFromArray([
            'email' => 'writer@en-marche-dev.fr',
            'password' => 'writer',
            'roles' => $writerRoles,
        ]));

        $manager->persist($superAdmin2fa);
        $manager->persist($admin);
        $manager->persist($writer);
        $manager->flush();
    }
}
