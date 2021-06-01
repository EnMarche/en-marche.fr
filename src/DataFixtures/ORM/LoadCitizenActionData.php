<?php

namespace App\DataFixtures\ORM;

use App\Entity\PostAddress;
use App\Event\EventFactory;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCitizenActionData extends Fixture implements DependentFixtureInterface
{
    public const CITIZEN_ACTION_1_UUID = '3f46976e-e76a-476e-86d7-575c6d3bc15f';
    public const CITIZEN_ACTION_2_UUID = '36ab5f85-5feb-4ff7-8218-d2da63045b74';
    public const CITIZEN_ACTION_3_UUID = '0389f98c-3e6c-4c92-ba80-19ab4a73e34b';
    public const CITIZEN_ACTION_4_UUID = '39f25bd2-f866-4c0d-84da-2387898b8db1';
    public const CITIZEN_ACTION_5_UUID = '92c8c6c9-928e-4686-8431-2e09254feb77';
    public const CITIZEN_ACTION_6_UUID = '0e533344-47cd-4dc4-909d-59f8cda21006';
    public const CITIZEN_ACTION_7_UUID = 'b5236488-a534-459a-a1a3-6df85b7c5ad9';
    public const CITIZEN_ACTION_8_UUID = 'a02ca3c7-1b07-46cd-81b2-d309258e53f9';
    public const CITIZEN_ACTION_9_UUID = '8d8a8ce7-ba60-4750-a300-bf0356e0ae0f';

    private $eventFactory;
    private $registrationFactory;

    public function __construct(EventFactory $eventFactory, EventRegistrationFactory $registrationFactory)
    {
        $this->eventFactory = $eventFactory;
        $this->registrationFactory = $registrationFactory;
    }

    public function load(ObjectManager $manager)
    {
        $actionCitoyenne1 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_1_UUID,
            'citizen_project' => $this->getReference('citizen-project-3'),
            'organizer' => $this->getReference('adherent-1'),
            'name' => 'Projet citoyen de Zürich',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Un projet citoyen pour les Zurichois(es)',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '30 Zeppelinstrasse', null, 47.3950062, 8.53838),
            'begin_at' => (new Chronos('+3 days'))->setTime(9, 30, 00, 000),
            'finish_at' => (new Chronos('+3 days'))->setTime(19, 00, 00, 000),
            'capacity' => 1,
            'time_zone' => 'Europe/Zurich',
        ]);
        $actionCitoyenne1->setPublished(true);
        $actionCitoyenne1->incrementParticipantsCount();

        $actionCitoyenne2 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_2_UUID,
            'citizen_project' => $this->getReference('citizen-project-2'),
            'organizer' => $this->getReference('adherent-2'),
            'name' => 'Projet citoyen',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Projet citoyen à Mouxy',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', null, 45.7218703, 5.929463),
            'begin_at' => (new Chronos('+9 days'))->setTime(9, 00, 00, 000),
            'finish_at' => (new Chronos('+9 days'))->setTime(19, 00, 00, 000),
            'capacity' => 30,
        ]);
        $actionCitoyenne2->setPublished(false);
        $actionCitoyenne2->incrementParticipantsCount();

        $actionCitoyenne3 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_3_UUID,
            'citizen_project' => $this->getReference('citizen-project-1'),
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Projet citoyen #3',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Un troisième projet citoyen',
            'address' => PostAddress::createFrenchAddress('16 rue de la Paix', '75008-75108', null, 48.869331, 2.331595),
            'begin_at' => (new Chronos('tomorrow'))->setTime(9, 30, 00, 000),
            'finish_at' => (new Chronos('tomorrow'))->setTime(16, 00, 00, 000),
            'capacity' => 20,
        ]);
        $actionCitoyenne3->setPublished(true);
        $actionCitoyenne3->incrementParticipantsCount();

        $actionCitoyenne4 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_4_UUID,
            'citizen_project' => $this->getReference('citizen-project-1'),
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Projet citoyen Paris-18',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Séance dans le 18è arrondissement',
            'address' => PostAddress::createFrenchAddress('26 rue de la Paix', '75008-75108', null, 48.869878, 2.332197),
            'begin_at' => (new Chronos('+11 days'))->setTime(10, 00, 00, 000),
            'finish_at' => (new Chronos('+11 days'))->setTime(15, 00, 00, 000),
            'capacity' => 20,
        ]);
        $actionCitoyenne4->setPublished(true);
        $actionCitoyenne4->incrementParticipantsCount(2);

        $actionCitoyenne5 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_5_UUID,
            'citizen_project' => $this->getReference('citizen-project-3'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen Kilchberg',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', null, 47.3164934, 8.553012),
            'begin_at' => (new Chronos('+11 days'))->setTime(9, 00, 00, 000),
            'finish_at' => (new Chronos('+11 days'))->setTime(12, 00, 00, 000),
            'time_zone' => 'Europe/Zurich',
        ]);
        $actionCitoyenne5->setPublished(true);
        $actionCitoyenne5->incrementParticipantsCount();

        $actionCitoyenne6 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_6_UUID,
            'citizen_project' => $this->getReference('citizen-project-4'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen annulée',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'On a annulé ce projet citoyen.',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', null, 47.3164934, 8.553012),
            'begin_at' => (new Chronos('+20 days'))->setTime(9, 00, 00, 000),
            'finish_at' => (new Chronos('+20 days'))->setTime(18, 00, 00, 000),
            'capacity' => 5,
            'time_zone' => 'Europe/Zurich',
        ]);
        $actionCitoyenne6->setPublished(true);
        $actionCitoyenne6->cancel();
        $actionCitoyenne6->incrementParticipantsCount();

        $actionCitoyenne7 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_7_UUID,
            'citizen_project' => $this->getReference('citizen-project-9'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen Kilchberg #2',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre notre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', null, 47.3164934, 8.553012),
            'begin_at' => (new Chronos('+15 days'))->setTime(9, 00, 00, 000),
            'finish_at' => (new Chronos('+15 days'))->setTime(12, 00, 00, 000),
            'capacity' => 10,
            'time_zone' => 'Europe/Zurich',
        ]);
        $actionCitoyenne7->setPublished(true);
        $actionCitoyenne7->incrementParticipantsCount(10);

        $actionCitoyenne8 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_8_UUID,
            'citizen_project' => $this->getReference('citizen-project-9'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen Kilchberg non publiée',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', null, 47.3164934, 8.553012),
            'begin_at' => (new Chronos('+15 days'))->setTime(9, 00, 00, 000),
            'finish_at' => (new Chronos('+15 days'))->setTime(12, 00, 00, 000),
            'capacity' => 10,
            'time_zone' => 'Europe/Zurich',
        ]);

        $actionCitoyenne9 = $this->eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_9_UUID,
            'citizen_project' => $this->getReference('citizen-project-2'),
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Projet citoyen du Vieux-Port',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre notre Vieux-Port propre',
            'address' => PostAddress::createForeignAddress('FR', '13001', 'Marseille', '25 Quai des Belges', null, 43.2943855, 5.3737235),
            'begin_at' => (new Chronos('-15 days'))->setTime(9, 00, 00, 000),
            'finish_at' => (new Chronos('-15 days'))->setTime(12, 00, 00, 000),
            'capacity' => 10,
        ]);
        $actionCitoyenne9->setPublished(true);
        $actionCitoyenne9->incrementParticipantsCount(2);

        $manager->persist($actionCitoyenne1);
        $manager->persist($actionCitoyenne2);
        $manager->persist($actionCitoyenne3);
        $manager->persist($actionCitoyenne4);
        $manager->persist($actionCitoyenne5);
        $manager->persist($actionCitoyenne6);
        $manager->persist($actionCitoyenne7);
        $manager->persist($actionCitoyenne8);
        $manager->persist($actionCitoyenne9);

        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne1, $this->getReference('adherent-1'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne2, $this->getReference('adherent-2'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne3, $this->getReference('adherent-3'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne4, $this->getReference('adherent-3'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne4, $this->getReference('adherent-5'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne4, $this->getReference('adherent-4'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne5, $this->getReference('adherent-13'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne6, $this->getReference('adherent-13'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne7, $this->getReference('adherent-13'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne8, $this->getReference('adherent-13'))));
        $manager->persist($this->registrationFactory->createFromCommand(new EventRegistrationCommand($actionCitoyenne9, $this->getReference('adherent-13'))));
        // Registrations of not connected users
        $eventRegistration1 = new EventRegistrationCommand($actionCitoyenne4);
        $eventRegistration1->setFirstName('Marie');
        $eventRegistration1->setLastName('CLAIRE');
        $eventRegistration1->setEmailAddress('marie.claire@test.com');
        $eventRegistration2 = new EventRegistrationCommand($actionCitoyenne4);
        $eventRegistration2->setFirstName('Pierre');
        $eventRegistration2->setLastName('FRANCE');
        $eventRegistration2->setEmailAddress('pierre.france@test.com');
        $manager->persist($this->registrationFactory->createFromCommand($eventRegistration1));
        $manager->persist($this->registrationFactory->createFromCommand($eventRegistration2));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
            LoadCitizenActionCategoryData::class,
        ];
    }
}
