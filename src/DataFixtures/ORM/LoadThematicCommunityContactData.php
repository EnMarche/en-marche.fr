<?php

namespace App\DataFixtures\ORM;

use App\Entity\ActivityAreaEnum;
use App\Entity\JobEnum;
use App\Entity\ThematicCommunity\Contact;
use App\Jecoute\GenderEnum;
use App\Utils\PhoneNumberUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadThematicCommunityContactData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $c1 = new Contact();
        $c1->setFirstName('John');
        $c1->setLastName('Peter');
        $c1->setGender(GenderEnum::MALE);
        $c1->setBirthDate(new \DateTime('14-05-1990'));
        $c1->setEmail('john.peter@contact.com');
        $c1->setActivityArea(ActivityAreaEnum::ACTIVITIES[14]);
        $c1->setJob('CTO de la France');
        $c1->setJobArea(JobEnum::JOBS[0]);
        $c1->setPhone(PhoneNumberUtils::create('+33612345678'));

        $this->addReference('tc-contact-1', $c1);
        $manager->persist($c1);

        $manager->flush();
    }
}
