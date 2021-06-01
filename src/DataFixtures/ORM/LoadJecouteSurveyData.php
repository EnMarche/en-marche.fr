<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Question;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadJecouteSurveyData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_survey');
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_survey_question');

        /** @var Adherent $referent1 */
        $referent1 = $this->getReference('adherent-8');

        /** @var Adherent $referent2 */
        $referent2 = $this->getReference('adherent-19');

        /** @var Adherent $headedRegionalCandidate */
        $headedRegionalCandidate = $this->getReference('adherent-3');

        /** @var Administrator $administrator1 */
        $administrator1 = $this->getReference('administrator-1');

        /** @var Administrator $administrator2 */
        $administrator2 = $this->getReference('administrator-2');

        /**
         * Local Surveys
         */
        $localSurvey1 = new LocalSurvey($referent1, 'Questionnaire numéro 1', true);
        $localSurvey2 = new LocalSurvey($referent2, 'Un deuxième questionnaire', true);
        $localSurvey3 = new LocalSurvey($headedRegionalCandidate, 'Un questionnaire de la Région', true);
        $localSurvey4 = new LocalSurvey($referent1, 'Un questionnaire avec modification bloquée', true, true);

        /** @var Question $question1 */
        $question1 = $this->getReference('question-1');

        /** @var Question $question2 */
        $question2 = $this->getReference('question-2');

        /** @var Question $question3 */
        $question3 = $this->getReference('question-3');

        /** @var Question $suggestedQuestion1 */
        $suggestedQuestion1 = $this->getReference('suggested-question-1');

        $surveyQuestion1 = new SurveyQuestion($localSurvey1, $question1);
        $surveyQuestion2 = new SurveyQuestion($localSurvey1, $question2);
        $surveyQuestion3 = new SurveyQuestion($localSurvey1, $question3);
        $surveyQuestion4 = new SurveyQuestion(
            $localSurvey1,
            $question4 = new Question($suggestedQuestion1->getContent(), $suggestedQuestion1->getType())
        );
        $question4->setChoices($suggestedQuestion1->getChoices());
        $surveyQuestion4->setFromSuggestedQuestion($suggestedQuestion1->getId());

        $localSurvey1->addQuestion($surveyQuestion1);
        $localSurvey1->addQuestion($surveyQuestion2);
        $localSurvey1->addQuestion($surveyQuestion3);
        $localSurvey1->addQuestion($surveyQuestion4);

        $localSurvey1->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_77'));

        /** @var Question $question4 */
        $question4 = $this->getReference('question-4');

        $localSurvey2Question1 = new SurveyQuestion($localSurvey2, $question4);

        $localSurvey2->addQuestion($localSurvey2Question1);

        $localSurvey2->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'));

        $localSurvey3->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_region_11'));
        $localSurvey4->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'));

        $manager->persist($localSurvey1);
        $manager->persist($localSurvey2);
        $manager->persist($localSurvey3);
        $manager->persist($localSurvey4);

        $this->addReference('survey-1', $localSurvey1);

        $this->addReference('survey-1-question-1', $surveyQuestion1);
        $this->addReference('survey-1-question-2', $surveyQuestion2);
        $this->addReference('survey-1-question-3', $surveyQuestion3);
        $this->addReference('survey-1-question-4', $surveyQuestion4);

        /**
         * National Surveys
         */
        $nationalSurvey1 = new NationalSurvey($administrator1, 'Questionnaire national numéro 1', true);

        /** @var Question $nationalQuestion1 */
        $nationalQuestion1 = $this->getReference('national-question-1');

        /** @var Question $nationalQuestion2 */
        $nationalQuestion2 = $this->getReference('national-question-2');

        $nationalSurveyQuestion1 = new SurveyQuestion($nationalSurvey1, $nationalQuestion1);
        $nationalSurveyQuestion2 = new SurveyQuestion($nationalSurvey1, $nationalQuestion2);

        $nationalSurvey1->addQuestion($nationalSurveyQuestion1);
        $nationalSurvey1->addQuestion($nationalSurveyQuestion2);

        $manager->persist($nationalSurvey1);

        $this->addReference('national-survey-1', $nationalSurvey1);

        $this->addReference('national-survey-1-question-1', $nationalSurveyQuestion1);
        $this->addReference('national-survey-1-question-2', $nationalSurveyQuestion2);

        $nationalSurvey2 = new NationalSurvey($administrator2, 'Le deuxième questionnaire national', true);

        /** @var Question $nationalQuestion3 */
        $nationalQuestion3 = $this->getReference('national-question-3');

        $nationalSurveyQuestion3 = new SurveyQuestion($nationalSurvey2, $nationalQuestion3);

        $nationalSurvey2->addQuestion($nationalSurveyQuestion3);

        $manager->persist($nationalSurvey2);

        $this->addReference('national-survey-2', $nationalSurvey2);

        $this->addReference('national-survey-2-question-1', $nationalSurveyQuestion3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadReferentData::class,
            LoadAdherentData::class,
            LoadAdminData::class,
            LoadJecouteQuestionData::class,
            LoadJecouteSuggestedQuestionData::class,
        ];
    }
}
