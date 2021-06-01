<?php

namespace App\DataFixtures\ORM;

use App\Entity\MyTeam\DelegatedAccess;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDelegatedAccessData extends Fixture implements DependentFixtureInterface
{
    private const ACCESS_UUID_1 = '2e80d106-4bcb-4b28-97c9-3856fc235b27';
    private const ACCESS_UUID_2 = 'f4ce89da-1272-4a01-a47e-4ce5248ce018';
    private const ACCESS_UUID_3 = '96076afb-2243-4251-97fe-8201d50c3256';
    private const ACCESS_UUID_4 = '411faa64-202d-4ff2-91ce-c98b29af28ef';
    private const ACCESS_UUID_5 = 'd2315289-a3fd-419c-a3dd-3e1ff71b754d';
    private const ACCESS_UUID_6 = '7fdf8fb0-1628-4500-b0b2-34c40cc27a2c';
    public const ACCESS_UUID_7 = '08f40730-d807-4975-8773-69d8fae1da74';
    private const ACCESS_UUID_8 = '01ddb89b-25be-4ccb-a90f-8338c42e7e58';

    public function load(ObjectManager $manager)
    {
        // full access, no committees or cities restriction
        $delegatedAccess1 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_1));
        $delegatedAccess1->setDelegated($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess1->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess1->setRole('Collaborateur parlementaire');
        $delegatedAccess1->setType('deputy');
        $delegatedAccess1->setAccesses(DelegatedAccess::ACCESSES);

        $manager->persist($delegatedAccess1);

        // access to 2 tabs, no committees or cities restriction
        $delegatedAccess2 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_2));
        $delegatedAccess2->setDelegated($this->getReference('adherent-4')); // luciole1989@spambox.fr
        $delegatedAccess2->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess2->setRole('Collaborateur parlementaire');
        $delegatedAccess2->setType('deputy');
        $delegatedAccess2->setAccesses([DelegatedAccess::ACCESS_EVENTS, DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess2);

        // access to 2 tabs, with cities restriction
        $delegatedAccess3 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_3));
        $delegatedAccess3->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess3->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess3->setRole('Collaborateur parlementaire');
        $delegatedAccess3->setType('deputy');
        $delegatedAccess3->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_EVENTS]);
        $delegatedAccess3->setRestrictedCities(['59360', '59350', '59044', '59002']);

        $manager->persist($delegatedAccess3);

        // second access to same user, but of type senator, with different accesses
        $delegatedAccess4 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_4));
        $delegatedAccess4->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess4->setDelegator($this->getReference('senator-59')); // senateur@en-marche-dev.fr
        $delegatedAccess4->setRole('Collaborateur parlementaire');
        $delegatedAccess4->setType('senator');
        $delegatedAccess4->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess4);

        // second deputy access to same user, with different accesses
        $delegatedAccess5 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_5));
        $delegatedAccess5->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess5->setDelegator($this->getReference('deputy-75-2')); // deputy-75-2@en-marche-dev.fr
        $delegatedAccess5->setRole('Collaborateur parlementaire');
        $delegatedAccess5->setType('deputy');
        $delegatedAccess5->setAccesses([DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess5);

        // no accesses
        $delegatedAccess6 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_6));
        $delegatedAccess6->setDelegated($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess6->setDelegator($this->getReference('senator-59')); // senateur@en-marche-dev.fr
        $delegatedAccess6->setRole('Collaborateur parlementaire');
        $delegatedAccess6->setType('senator');

        $manager->persist($delegatedAccess6);

        // access from referent
        $delegatedAccess7 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_7));
        $delegatedAccess7->setDelegated($this->getReference('senator-59')); // senateur@en-marche-dev.fr
        $delegatedAccess7->setDelegator($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess7->setRole('Collaborateur parlementaire');
        $delegatedAccess7->setType('referent');
        $delegatedAccess7->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
            DelegatedAccess::ACCESS_MESSAGES,
        ]);

        $manager->persist($delegatedAccess7);

        // access from candidate
        $delegatedAccess8 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_8));
        $delegatedAccess8->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess8->setDelegator($this->getReference('adherent-3')); // jacques.picard@en-marche.fr
        $delegatedAccess8->setRole('Candidat délégué');
        $delegatedAccess8->setType('candidate');
        $delegatedAccess8->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
            DelegatedAccess::ACCESS_MESSAGES,
            DelegatedAccess::ACCESS_EVENTS,
            DelegatedAccess::ACCESS_FILES,
            DelegatedAccess::ACCESS_POLLS,
            DelegatedAccess::ACCESS_JECOUTE,
            DelegatedAccess::ACCESS_JECOUTE_REGION,
            DelegatedAccess::ACCESS_JECOUTE_NEWS,
        ]);
        $delegatedAccess8->setRestrictedCities(['77288', '59002']);
        $delegatedAccess8->setRestrictedCommittees([$this->getReference('committee-4')]);

        $manager->persist($delegatedAccess7);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
