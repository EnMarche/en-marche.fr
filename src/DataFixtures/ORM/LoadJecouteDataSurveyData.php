<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Jecoute\GenderEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadJecouteDataSurveyData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Survey $survey1 */
        $survey1 = $this->getReference('survey-1');

        /** @var NationalSurvey $nationalSurvey1 */
        $nationalSurvey1 = $this->getReference('national-survey-1');

        /** @var NationalSurvey $nationalSurvey2 */
        $nationalSurvey2 = $this->getReference('national-survey-2');

        /** @var Adherent $adherent7 */
        $adherent7 = $this->getReference('adherent-7');

        $dataSurvey1 = new DataSurvey($survey1, 'Juan', 'Nanardinho');
        $dataSurvey1->setAuthor($adherent7);
        $dataSurvey1->setGender(GenderEnum::MALE);

        $dataSurvey2 = new DataSurvey($survey1, 'Brigitte', 'Brioulini');
        $dataSurvey2->setAuthor($adherent7);
        $dataSurvey2->setGender(GenderEnum::FEMALE);

        $dataSurvey3 = new DataSurvey($survey1, 'Michel', 'Mimolette');
        $dataSurvey3->setAuthor($adherent7);
        $dataSurvey3->setGender(GenderEnum::MALE);

        $dataSurvey4 = new DataSurvey($nationalSurvey1, 'Roger', 'Camembert');
        $dataSurvey4->setAuthor($adherent7);
        $dataSurvey4->setGender(GenderEnum::MALE);

        $dataSurvey5 = new DataSurvey($nationalSurvey1, 'Sophie', 'Stiket');
        $dataSurvey5->setAuthor($adherent7);
        $dataSurvey5->setGender(GenderEnum::FEMALE);

        $dataSurvey6 = new DataSurvey($nationalSurvey2, 'Pierre', 'Feuilcizo');
        $dataSurvey6->setAuthor($adherent7);
        $dataSurvey6->setGender(GenderEnum::MALE);

        $manager->persist($dataSurvey1);
        $manager->persist($dataSurvey2);
        $manager->persist($dataSurvey3);
        $manager->persist($dataSurvey4);
        $manager->persist($dataSurvey5);
        $manager->persist($dataSurvey6);

        $this->addReference('data-survey-1', $dataSurvey1);
        $this->addReference('data-survey-2', $dataSurvey2);
        $this->addReference('data-survey-3', $dataSurvey3);
        $this->addReference('data-national-survey-1', $dataSurvey4);
        $this->addReference('data-national-survey-2', $dataSurvey5);
        $this->addReference('data-national-survey-3', $dataSurvey6);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadJecouteSurveyData::class,
            LoadAdherentData::class,
            LoadJecouteQuestionData::class,
            LoadJecouteSuggestedQuestionData::class,
        ];
    }
}
