<?php

namespace App\DataFixtures\ORM;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataAnswer;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadJecouteDataAnswerData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var SurveyQuestion $survey1Question1 */
        $survey1Question1 = $this->getReference('survey-1-question-1');

        /** @var SurveyQuestion $survey1Question2 */
        $survey1Question2 = $this->getReference('survey-1-question-2');

        /** @var SurveyQuestion $survey1Question3 */
        $survey1Question3 = $this->getReference('survey-1-question-3');

        /** @var SurveyQuestion $nationalSurvey1Question1 */
        $nationalSurvey1Question1 = $this->getReference('national-survey-1-question-1');

        /** @var SurveyQuestion $nationalSurvey1Question2 */
        $nationalSurvey1Question2 = $this->getReference('national-survey-1-question-2');

        /** @var SurveyQuestion $nationalSurvey2Question1 */
        $nationalSurvey2Question1 = $this->getReference('national-survey-2-question-1');

        /** @var DataSurvey $dataSurvey1 */
        $dataSurvey1 = $this->getReference('data-survey-1');

        /** @var DataSurvey $dataSurvey2 */
        $dataSurvey2 = $this->getReference('data-survey-2');

        /** @var DataSurvey $dataSurvey3 */
        $dataSurvey3 = $this->getReference('data-survey-3');

        /** @var DataSurvey $nationalSurvey1 */
        $nationalSurvey1 = $this->getReference('data-national-survey-1');

        /** @var DataSurvey $nationalSurvey2 */
        $nationalSurvey2 = $this->getReference('data-national-survey-2');

        /** @var DataSurvey $nationalSurvey3 */
        $nationalSurvey3 = $this->getReference('data-national-survey-3');

        // Data Survey 1

        $dataSurvey1Answer1 = new DataAnswer();
        $dataSurvey1Answer1->setSurveyQuestion($survey1Question1);
        $dataSurvey1Answer1->setDataSurvey($dataSurvey1);
        $dataSurvey1Answer1->setTextField('Bonsoir, ceci est une réponse A');

        /** @var Choice $surveyQuestion2Choice1 */
        $surveyQuestion2Choice1 = $this->getReference('question-2-choice-1');

        $dataSurvey1Answer2 = new DataAnswer();
        $dataSurvey1Answer2->setSurveyQuestion($survey1Question2);
        $dataSurvey1Answer2->setDataSurvey($dataSurvey1);
        $dataSurvey1Answer2->addSelectedChoice($surveyQuestion2Choice1);

        /** @var Choice $surveyQuestion3Choice1 */
        $surveyQuestion3Choice1 = $this->getReference('question-3-choice-1');

        $dataSurvey1Answer3 = new DataAnswer();
        $dataSurvey1Answer3->setSurveyQuestion($survey1Question3);
        $dataSurvey1Answer3->setDataSurvey($dataSurvey1);
        $dataSurvey1Answer3->addSelectedChoice($surveyQuestion3Choice1);

        $manager->persist($dataSurvey1Answer1);
        $manager->persist($dataSurvey1Answer2);
        $manager->persist($dataSurvey1Answer3);

        // Data Survey 2

        $dataSurvey2Answer1 = new DataAnswer();
        $dataSurvey2Answer1->setSurveyQuestion($survey1Question1);
        $dataSurvey2Answer1->setDataSurvey($dataSurvey2);
        $dataSurvey2Answer1->setTextField('Bonsoir, ceci est une réponse B');

        /** @var Choice $surveyQuestion2Choice1 */
        $surveyQuestion2Choice1 = $this->getReference('question-2-choice-1');

        $dataSurvey2Answer2 = new DataAnswer();
        $dataSurvey2Answer2->setSurveyQuestion($survey1Question2);
        $dataSurvey2Answer2->setDataSurvey($dataSurvey2);
        $dataSurvey2Answer2->addSelectedChoice($surveyQuestion2Choice1);

        /** @var Choice $surveyQuestion3Choice2 */
        $surveyQuestion3Choice2 = $this->getReference('question-3-choice-2');

        $dataSurvey2Answer3 = new DataAnswer();
        $dataSurvey2Answer3->setSurveyQuestion($survey1Question3);
        $dataSurvey2Answer3->setDataSurvey($dataSurvey2);
        $dataSurvey2Answer3->addSelectedChoice($surveyQuestion3Choice2);

        $manager->persist($dataSurvey1Answer1);
        $manager->persist($dataSurvey1Answer2);
        $manager->persist($dataSurvey1Answer3);

        $manager->persist($dataSurvey2Answer1);
        $manager->persist($dataSurvey2Answer2);
        $manager->persist($dataSurvey2Answer3);

        // Data Survey 3

        $dataSurvey3Answer1 = new DataAnswer();
        $dataSurvey3Answer1->setSurveyQuestion($survey1Question1);
        $dataSurvey3Answer1->setDataSurvey($dataSurvey3);
        $dataSurvey3Answer1->setTextField('Bonsoir, ceci est une réponse C');

        /** @var Choice $surveyQuestion2Choice2 */
        $surveyQuestion2Choice2 = $this->getReference('question-2-choice-2');

        $dataSurvey3Answer2 = new DataAnswer();
        $dataSurvey3Answer2->setSurveyQuestion($survey1Question2);
        $dataSurvey3Answer2->setDataSurvey($dataSurvey3);
        $dataSurvey3Answer2->addSelectedChoice($surveyQuestion2Choice2);

        $manager->persist($dataSurvey3Answer1);
        $manager->persist($dataSurvey3Answer2);

        // Data Survey National

        $dataNationalSurvey1Answer1 = new DataAnswer();
        $dataNationalSurvey1Answer1->setSurveyQuestion($nationalSurvey1Question1);
        $dataNationalSurvey1Answer1->setDataSurvey($nationalSurvey1);
        $dataNationalSurvey1Answer1->setTextField('Réponse nationale !');

        $manager->persist($dataNationalSurvey1Answer1);

        $dataNationalSurvey1Answer2 = new DataAnswer();
        $dataNationalSurvey1Answer2->setSurveyQuestion($nationalSurvey1Question2);
        $dataNationalSurvey1Answer2->setDataSurvey($nationalSurvey1);
        $dataNationalSurvey1Answer2->addSelectedChoice($this->getReference('national-question-2-choice-2'));
        $dataNationalSurvey1Answer2->addSelectedChoice($this->getReference('national-question-2-choice-3'));

        $manager->persist($dataNationalSurvey1Answer2);

        $dataNationalSurvey2Answer1 = new DataAnswer();
        $dataNationalSurvey2Answer1->setSurveyQuestion($nationalSurvey1Question1);
        $dataNationalSurvey2Answer1->setDataSurvey($nationalSurvey2);
        $dataNationalSurvey2Answer1->setTextField('La réponse nationale de la 2eme personne');

        $manager->persist($dataNationalSurvey2Answer1);

        $dataNationalSurvey2Answer2 = new DataAnswer();
        $dataNationalSurvey2Answer2->setSurveyQuestion($nationalSurvey1Question2);
        $dataNationalSurvey2Answer2->setDataSurvey($nationalSurvey2);
        $dataNationalSurvey2Answer2->addSelectedChoice($this->getReference('national-question-2-choice-1'));
        $dataNationalSurvey2Answer2->addSelectedChoice($this->getReference('national-question-2-choice-4'));

        $manager->persist($dataNationalSurvey2Answer2);

        $dataNationalSurvey3Answer1 = new DataAnswer();
        $dataNationalSurvey3Answer1->setSurveyQuestion($nationalSurvey2Question1);
        $dataNationalSurvey3Answer1->setDataSurvey($nationalSurvey3);
        $dataNationalSurvey3Answer1->addSelectedChoice($this->getReference('national-question-3-choice-1'));

        $manager->persist($dataNationalSurvey3Answer1);

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
