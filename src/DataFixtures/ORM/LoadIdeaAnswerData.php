<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\Answer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadIdeaAnswerData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_answer');

        $ideaPeace = $this->getReference('idea-peace');
        $ideaHelpEcology = $this->getReference('idea-help-ecology');
        $ideaReduceWaste = $this->getReference('idea-reduce-waste');
        $ideaReduceNoise = $this->getReference('idea-noise');
        $questionProblem = $this->getReference('question-problem');
        $questionAnswer = $this->getReference('question-answer');
        $questionCompare = $this->getReference('question-compare');
        $questionNegativeEffect = $this->getReference('question-negative-effect');
        $questionLawImpact = $this->getReference('question-law-impact');
        $questionBudgetImpact = $this->getReference('question-budget-impact');
        $questionEcologyImpact = $this->getReference('question-ecology-impact');
        $questionGenderEquality = $this->getReference('question-gender-equality');

        $answerQuestionProblem = new Answer(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet, mi condimentum venenatis vestibulum, arcu neque feugiat massa, at pharetra velit sapien et elit. Sed vitae hendrerit nulla. Vivamus consectetur magna at tincidunt maximus. Aenean dictum metus vel tellus posuere venenatis.'
        );
        $answerQuestionProblem->setQuestion($questionProblem);
        $ideaPeace->addAnswer($answerQuestionProblem);
        $this->addReference('answer-q-problem', $answerQuestionProblem);

        $answerQuestionAnswer = new Answer(
            '<p>Nulla metus enim, congue eu facilisis ac, consectetur ut ipsum.</p>'
        );
        $answerQuestionAnswer->setQuestion($questionAnswer);
        $ideaPeace->addAnswer($answerQuestionAnswer);
        $this->addReference('answer-q-answer', $answerQuestionAnswer);

        $answerQuestionCompare = new Answer(
            '<p>Mauris gravida semper tincidunt.</p>'
        );
        $answerQuestionCompare->setQuestion($questionCompare);
        $ideaPeace->addAnswer($answerQuestionCompare);
        $this->addReference('answer-q-compare', $answerQuestionCompare);

        $answerQuestionNegativeEffect = new Answer(
            'Donec ac neque congue, condimentum ipsum ac, eleifend ex.'
        );
        $answerQuestionNegativeEffect->setQuestion($questionNegativeEffect);
        $ideaPeace->addAnswer($answerQuestionNegativeEffect);
        $this->addReference('answer-q-negative-effect', $answerQuestionNegativeEffect);

        $answerQuestionLawImpact = new Answer(
            'Suspendisse interdum quis tortor quis sodales. Suspendisse vel mollis orci.'
        );
        $answerQuestionLawImpact->setQuestion($questionLawImpact);
        $ideaPeace->addAnswer($answerQuestionLawImpact);
        $this->addReference('answer-q-law-impact', $answerQuestionLawImpact);

        $answerQuestionBudgetImpact = new Answer(
            'Proin et quam a tortor pretium fringilla non et magna.'
        );
        $answerQuestionBudgetImpact->setQuestion($questionBudgetImpact);
        $ideaPeace->addAnswer($answerQuestionBudgetImpact);
        $this->addReference('answer-q-budget-impact', $answerQuestionBudgetImpact);

        $answerQuestionEcologyImpact = new Answer(
            'Orci varius natoque penatibus et magnis dis parturient montes'
        );
        $answerQuestionEcologyImpact->setQuestion($questionEcologyImpact);
        $ideaPeace->addAnswer($answerQuestionEcologyImpact);
        $this->addReference('answer-q-ecology-impact', $answerQuestionEcologyImpact);

        $answerQuestionGenderEquality = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex.'
        );
        $answerQuestionGenderEquality->setQuestion($questionGenderEquality);
        $ideaPeace->addAnswer($answerQuestionGenderEquality);
        $this->addReference('answer-q-gender-equality', $answerQuestionGenderEquality);

        $answerQuestionProblemIdeaHelpEcology = new Answer(
            'Curabitur at eros et ex faucibus fringilla sed vel velit.'
        );
        $answerQuestionProblemIdeaHelpEcology->setQuestion($questionProblem);
        $ideaHelpEcology->addAnswer($answerQuestionProblemIdeaHelpEcology);
        $this->addReference('answer-q-problem-idea-he', $answerQuestionProblemIdeaHelpEcology);

        $answerQuestionProblemIdeaReduceWaste = new Answer(
            'Vestibulum ante ipsum primis.'
        );
        $answerQuestionProblemIdeaReduceWaste->setQuestion($questionProblem);
        $ideaReduceWaste->addAnswer($answerQuestionProblemIdeaReduceWaste);
        $this->addReference('answer-q-problem-idea-reduce-waste', $answerQuestionProblemIdeaReduceWaste);

        $answerQuestionProblemIdeaReduceNoise = new Answer(
            'Hodor.'
        );
        $answerQuestionProblemIdeaReduceNoise->setQuestion($questionProblem);
        $ideaReduceNoise->addAnswer($answerQuestionProblemIdeaReduceNoise);
        $this->addReference('answer-q-problem-idea-reduce-noise', $answerQuestionProblemIdeaReduceNoise);

        $manager->persist($answerQuestionProblem);
        $manager->persist($answerQuestionAnswer);
        $manager->persist($answerQuestionCompare);
        $manager->persist($answerQuestionNegativeEffect);
        $manager->persist($answerQuestionLawImpact);
        $manager->persist($answerQuestionBudgetImpact);
        $manager->persist($answerQuestionEcologyImpact);
        $manager->persist($answerQuestionGenderEquality);
        $manager->persist($answerQuestionProblemIdeaHelpEcology);
        $manager->persist($answerQuestionProblemIdeaReduceWaste);
        $manager->persist($answerQuestionProblemIdeaReduceNoise);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaData::class,
            LoadIdeaQuestionData::class,
            LoadUserDocumentData::class,
        ];
    }
}
