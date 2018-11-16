<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\Question;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadJecouteSurveyData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_survey');
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_survey_question');

        /** @var Adherent $referent1 */
        $referent1 = $this->getReference('adherent-8');
        /** @var Adherent $referent2 */
        $referent2 = $this->getReference('adherent-19');

        $survey1 = new Survey($referent1, 'Questionnaire numéro 1', 'Paris 1er', true);
        $survey2 = new Survey($referent2, 'Un deuxième questionnaire', 'Paris 8ème', true);

        /** @var Question $question1 */
        $question1 = $this->getReference('question-1');

        /** @var Question $question2 */
        $question2 = $this->getReference('question-2');

        /** @var Question $question3 */
        $question3 = $this->getReference('question-3');

        /** @var Question $question4 */
        $question4 = $this->getReference(('suggested-question-1'));

        $surveyQuestion1 = new SurveyQuestion($survey1, $question1);
        $surveyQuestion2 = new SurveyQuestion($survey1, $question2);
        $surveyQuestion3 = new SurveyQuestion($survey1, $question3);
        $surveyQuestion4 = new SurveyQuestion($survey1, $question4);
        $surveyQuestion4->setFromSuggestedQuestion(true);

        $survey1->addQuestion($surveyQuestion1);
        $survey1->addQuestion($surveyQuestion2);
        $survey1->addQuestion($surveyQuestion3);
        $survey1->addQuestion($surveyQuestion4);

        $survey2Question1 = new SurveyQuestion($survey2, $question1);

        $survey2->addQuestion($survey2Question1);

        $manager->persist($survey1);
        $manager->persist($survey2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadJecouteQuestionData::class,
            LoadJecouteSuggestedQuestionData::class,
        ];
    }
}
