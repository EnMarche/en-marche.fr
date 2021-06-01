<?php

namespace App\DataFixtures\ORM;

use App\Entity\ThematicCommunity\AdherentMembership;
use App\Entity\ThematicCommunity\ContactMembership;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadThematicCommunityMembershipData extends Fixture implements DependentFixtureInterface
{
    private const MEMBERSHIP_UUID_1 = 'b168d99c-f3ff-4701-948f-be180c8d2af6';
    private const MEMBERSHIP_UUID_2 = '2420524f-f52e-46af-a945-327c48788d37';
    private const MEMBERSHIP_UUID_3 = 'be8b1edb-b958-4054-bcc9-903ff39062dd';
    private const MEMBERSHIP_UUID_4 = '883479fb-d685-486e-9777-cc1634f06f34';

    public function load(ObjectManager $manager)
    {
        $cm1 = new ContactMembership(Uuid::fromString(self::MEMBERSHIP_UUID_1));
        $cm1->setContact($this->getReference('tc-contact-1'));
        $cm1->setCommunity($this->getReference('tc-sante'));
        $cm1->setMotivations([ThematicCommunityMembership::MOTIVATION_THINKING, ThematicCommunityMembership::MOTIVATION_ON_SPOT]);
        $cm1->setStatus(ThematicCommunityMembership::STATUS_VERIFIED);
        $manager->persist($cm1);

        $cm2 = new ContactMembership(Uuid::fromString(self::MEMBERSHIP_UUID_2));
        $cm2->setContact($this->getReference('tc-contact-1'));
        $cm2->setCommunity($this->getReference('tc-education'));
        $cm2->setMotivations([ThematicCommunityMembership::MOTIVATION_ON_SPOT]);
        $cm2->setStatus(ThematicCommunityMembership::STATUS_VERIFIED);
        $manager->persist($cm2);

        $am = new AdherentMembership(Uuid::fromString(self::MEMBERSHIP_UUID_3));
        $am->setAdherent($this->getReference('adherent-8'));
        $am->setCommunity($this->getReference('tc-education'));
        $am->setMotivations([ThematicCommunityMembership::MOTIVATION_ON_SPOT, ThematicCommunityMembership::MOTIVATION_INFORMATION]);
        $am->setStatus(ThematicCommunityMembership::STATUS_VERIFIED);
        $am->setAssociation(true);
        $am->setAssociationName('Association de ouf');
        $manager->persist($am);

        $amElected = new AdherentMembership(Uuid::fromString(self::MEMBERSHIP_UUID_4));
        $amElected->setAdherent($this->getReference('adherent-5'));
        $amElected->setCommunity($this->getReference('tc-sante'));
        $amElected->setMotivations([ThematicCommunityMembership::MOTIVATION_THINKING]);
        $amElected->setStatus(ThematicCommunityMembership::STATUS_VERIFIED);
        $manager->persist($amElected);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadThematicCommunityData::class,
            LoadThematicCommunityContactData::class,
            LoadAdherentData::class,
        ];
    }
}
