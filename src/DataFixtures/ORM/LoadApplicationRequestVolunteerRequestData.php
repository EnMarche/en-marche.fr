<?php

namespace App\DataFixtures\ORM;

use App\Entity\ApplicationRequest\VolunteerRequest;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use libphonenumber\PhoneNumberUtil;
use Ramsey\Uuid\Uuid;

class LoadApplicationRequestVolunteerRequestData extends Fixture implements DependentFixtureInterface
{
    private const UUID_1 = '214ef0d4-8923-48a6-99e1-e50eeefaeaf4';
    private const UUID_2 = 'a6ac8cc7-776f-4f4f-854e-f6b0a1bd7c62';
    private const UUID_3 = '5ca5fc5c-b6f4-4edf-bb8e-111aa9222696';
    private const UUID_4 = '06d61c85-929a-4152-b46c-b94b6883b8d6';

    public function load(ObjectManager $manager)
    {
        $municipal1 = $this->getReference('municipal-chief-1');
        $municipal2 = $this->getReference('municipal-chief-2');
        $municipal3 = $this->getReference('municipal-chief-3');

        $volunteerRequest1 = new VolunteerRequest(Uuid::fromString(self::UUID_1));
        $volunteerRequest1->setGender(Genders::MALE);
        $volunteerRequest1->setFirstName('Tony');
        $volunteerRequest1->setLastName('Stark');
        $volunteerRequest1->setEmailAddress('tony.stark@stark-industries.com');

        $volunteerRequest1->setFavoriteCities([
            $municipal1->getMunicipalChiefManagedArea()->getInseeCode(),
            $municipal2->getMunicipalChiefManagedArea()->getInseeCode(),
        ]);

        $volunteerRequest1->setTakenForCity($municipal2->getMunicipalChiefManagedArea()->getInseeCode());

        $volunteerRequest2 = new VolunteerRequest(Uuid::fromString(self::UUID_2));
        $volunteerRequest2->setGender(Genders::FEMALE);
        $volunteerRequest2->setFirstName('Damien');
        $volunteerRequest2->setLastName('Schmidt');
        $volunteerRequest2->setEmailAddress('damien.schmidt@example.ch');

        $volunteerRequest2->setFavoriteCities([
            $municipal1->getMunicipalChiefManagedArea()->getInseeCode(),
            $municipal2->getMunicipalChiefManagedArea()->getInseeCode(),
            $municipal3->getMunicipalChiefManagedArea()->getInseeCode(),
        ]);

        $volunteerRequest2->setAdherent($this->getReference('adherent-14'));

        $volunteerRequest3 = new VolunteerRequest(Uuid::fromString(self::UUID_3));
        $volunteerRequest3->setGender(Genders::MALE);
        $volunteerRequest3->setFirstName('Tony');
        $volunteerRequest3->setLastName('Stark');
        $volunteerRequest3->setEmailAddress('tony.stark@stark-industries.com');

        $volunteerRequest3->setFavoriteCities([
            $municipal1->getMunicipalChiefManagedArea()->getInseeCode(),
            $municipal3->getMunicipalChiefManagedArea()->getInseeCode(),
        ]);

        $volunteerRequest4 = new VolunteerRequest(Uuid::fromString(self::UUID_4));
        $volunteerRequest4->setGender(Genders::OTHER);
        $volunteerRequest4->setFirstName('Damien');
        $volunteerRequest4->setLastName('Schmidt');
        $volunteerRequest4->setEmailAddress('damien.schmidt@example.ch');

        $volunteerRequest4->setFavoriteCities([
            $municipal1->getMunicipalChiefManagedArea()->getInseeCode(),
        ]);

        $volunteerRequest4->setAdherent($this->getReference('adherent-14'));

        $phone = PhoneNumberUtil::getInstance()->parse('06-06-06-06-06', 'FR');

        foreach ([$volunteerRequest1, $volunteerRequest2, $volunteerRequest3, $volunteerRequest4] as $i => $volunteerRequest) {
            $volunteerRequest->setPostalCode('90265');
            $volunteerRequest->setCityName('Malibu');
            $volunteerRequest->setCountry('US');
            $volunteerRequest->setAddress('10880 Malibu Point');
            $volunteerRequest->setProfession('Scientist & Engineer');
            $volunteerRequest->setPhone($phone);

            $volunteerRequest->addFavoriteTheme($this->getReference('application-theme-06'));
            $volunteerRequest->addFavoriteTheme($this->getReference('application-theme-08'));
            $volunteerRequest->addTag($this->getReference('application-request-tag-'.$i));
            $volunteerRequest->addTag($this->getReference('application-request-tag-'.($i + 1) % 4));
            $volunteerRequest->setCustomFavoriteTheme('Thanos destruction');

            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-01'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-02'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-04'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-08'));

            $volunteerRequest->setCustomTechnicalSkills("I'am a man with a cool robot suit.");
            $volunteerRequest->setIsPreviousCampaignMember(false);
            $volunteerRequest->setShareAssociativeCommitment(false);

            $volunteerRequest->addReferentTag($this->getReference('referent_tag_59'));

            $manager->persist($volunteerRequest);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadApplicationRequestThemeData::class,
            LoadApplicationRequestTechnicalSkillData::class,
            LoadReferentTagData::class,
        ];
    }
}
