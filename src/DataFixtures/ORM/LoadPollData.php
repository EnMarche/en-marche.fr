<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Geo\Zone;
use App\Entity\Poll\Choice;
use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\NationalPoll;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPollData extends Fixture implements DependentFixtureInterface
{
    public const NATIONAL_POLL_01_UUID = '8adca369-938c-450b-92e9-9c2b1f206fa3';
    public const LOCAL_POLL_01_UUID = '655d7534-9592-4aed-83e6-cad8fbb3668f';
    public const LOCAL_POLL_02_UUID = 'c45f204d-cf49-4bf7-9a51-bd1fc89a7260';
    public const LOCAL_POLL_03_UUID = 'f91b332e-efef-4bf6-89ad-b9675e42a3f5';
    public const LOCAL_POLL_04_UUID = 'aa7456ef-3bac-47ee-9951-23fad500fd92';
    public const POLL_01_CHOICE_01_UUID = 'dd429c8f-a07f-47ad-a424-b28058c4bf7d';
    public const POLL_01_CHOICE_02_UUID = '26aba15c-b49a-4cb7-99ef-585e12bcff50';
    public const POLL_01_CHOICE_03_UUID = 'c140e1fb-749c-4b13-97f6-327999004247';

    public function load(ObjectManager $manager)
    {
        $michelle = $this->getReference('adherent-1');
        $carl = $this->getReference('adherent-2');
        $jacques = $this->getReference('adherent-3');
        $gisele = $this->getReference('adherent-5');

        // National
        $nationalPoll1 = $this->createNationalPoll(
            $this->getReference('administrator-2'),
            self::NATIONAL_POLL_01_UUID,
            'Plutôt thé ou café ?',
            new \DateTime('+1 day')
        );

        $nationalPoll1Choice1 = $this->createChoice('Thé', self::POLL_01_CHOICE_01_UUID);
        $nationalPoll1->addChoice($nationalPoll1Choice1);

        $nationalPoll1Choice2 = $this->createChoice('Café', self::POLL_01_CHOICE_02_UUID);
        $nationalPoll1->addChoice($nationalPoll1Choice2);

        $nationalPoll1Choice3 = $this->createChoice("Ni l'un ni l'autre", self::POLL_01_CHOICE_03_UUID);
        $nationalPoll1->addChoice($nationalPoll1Choice3);

        $manager->persist($nationalPoll1);
        $manager->persist($this->createVote($nationalPoll1Choice2));
        $manager->persist($this->createVote($nationalPoll1Choice2, $michelle));
        $manager->persist($this->createVote($nationalPoll1Choice2, $jacques));
        $manager->persist($this->createVote($nationalPoll1Choice3));

        // Local
        $localPoll1 = $this->createLocalPoll(
            $this->getReference('adherent-3'),
            self::LOCAL_POLL_01_UUID,
            'Tu dis "oui" ?',
            new \DateTime('+7 day'),
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11'),
            true
        );
        $this->addChoices($localPoll1, [
            Choice::YES => [$michelle, $carl, $jacques],
            Choice::NO => [$gisele],
        ]);
        $localPoll2 = $this->createLocalPoll(
            $this->getReference('adherent-3'),
            self::LOCAL_POLL_02_UUID,
            'Tu dis "non" ?',
            new \DateTime('+5 day'),
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11')
        );
        $this->addChoices($localPoll2, [
            Choice::YES => [$jacques],
            Choice::NO => [$michelle, $gisele],
        ]);
        $localPoll3 = $this->createLocalPoll(
            $this->getReference('adherent-5'),
            self::LOCAL_POLL_03_UUID,
            'Tu dis quoi ?',
            new \DateTime('+3 day'),
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11')
        );
        $this->addChoices($localPoll3, [
            Choice::YES => [],
            Choice::NO => [],
        ]);
        $localPoll4 = $this->createLocalPoll(
            $this->getReference('adherent-3'),
            self::LOCAL_POLL_04_UUID,
            'Non publié ?',
            new \DateTime('+1 day'),
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11')
        );
        $this->addChoices($localPoll4, [
            Choice::YES => [],
            Choice::NO => [],
        ]);

        $manager->persist($localPoll1);
        $manager->persist($localPoll2);
        $manager->persist($localPoll3);
        $manager->persist($localPoll4);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
            LoadGeoZoneData::class,
        ];
    }

    private function createLocalPoll(
        Adherent $author,
        string $uuid,
        string $question,
        \DateTimeInterface $finishAt,
        Zone $zone,
        bool $published = false
    ): Poll {
        return new LocalPoll($author, Uuid::fromString($uuid), $question, $finishAt, $zone, $published);
    }

    private function createNationalPoll(
        Administrator $administrator,
        string $uuid,
        string $question,
        \DateTimeInterface $finishAt
    ): Poll {
        return new NationalPoll($administrator, Uuid::fromString($uuid), $question, $finishAt);
    }

    private function createChoice(string $value, string $uuid = null): Choice
    {
        return new Choice($value, $uuid ? Uuid::fromString($uuid) : null);
    }

    private function createVote(Choice $choice, Adherent $adherent = null): Vote
    {
        return new Vote($choice, $adherent);
    }

    private function addChoices(Poll $poll, array $choices): void
    {
        foreach ($choices as $value => $adherents) {
            foreach ($poll->getChoices() as $existingChoice) {
                if ($value === $existingChoice->getValue()) {
                    $choice = $existingChoice;
                }
            }

            if (!isset($choice)) {
                $choice = new Choice($value);
                $poll->addChoice($choice);
            }

            foreach ($adherents as $adherent) {
                $vote = $this->createVote($choice, $adherent);
                $choice->addVote($vote);
            }
        }
    }
}
