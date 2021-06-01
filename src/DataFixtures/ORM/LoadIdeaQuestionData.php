<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadIdeaQuestionData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_question');

        $guidelineMainFeature = $this->getReference('guideline-main-feature');
        $guidelineImplementation = $this->getReference('guideline-implementation');

        $isMandatory = true;

        $questionProblem = new Question(
            'Constat',
            'quel problème souhaitez-vous résoudre ?',
            'Expliquer précisément le problème que vous avez identifié et auquel vous souhaitez répondre. N\'hésitez pas à étayer votre constat par des chiffres ou des exemples.',
            1,
            $isMandatory
        );
        $this->addReference('question-problem', $questionProblem);
        $guidelineMainFeature->addQuestion($questionProblem);

        $questionAnswer = new Question(
            'Solution',
            'quelle réponse votre idée apporte-t-elle ? ',
            'Expliquez comment votre proposition répond au problème en étant le plus concret possible. Chaque proposition ne doit comporter qu\'une seule solution.',
            2,
            $isMandatory
        );
        $this->addReference('question-answer', $questionAnswer);
        $guidelineMainFeature->addQuestion($questionAnswer);

        $questionCompare = new Question(
            'Comparaison',
            'cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?',
            'Précisez si cette proposition a été mise en œuvre en France ou à l\'étranger, s\'il s\'agissait d\'une expérimentation et quels en ont été les résultats.',
            3
        );
        $this->addReference('question-compare', $questionCompare);
        $guidelineMainFeature->addQuestion($questionCompare);

        $questionNegativeEffect = new Question(
            'Impact',
            'Cette proposition peut elle avoir des effets négatifs pour certains publics ?',
            'Expliquez si cette proposition peut porter préjudice à certains acteurs (individus, professions, territoires, institutions, entreprises, associations, etc) et comment il est possible d\'en limiter les effets.',
            4
        );
        $this->addReference('question-negative-effect', $questionNegativeEffect);
        $guidelineMainFeature->addQuestion($questionNegativeEffect);

        $questionLawImpact = new Question(
            'Droit',
            'votre idée suppose-t-elle de changer le droit ?',
            'Expliquez si votre proposition nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu\'il faudrait changer.',
            5
        );
        $this->addReference('question-law-impact', $questionLawImpact);
        $guidelineImplementation->addQuestion($questionLawImpact);

        $questionBudgetImpact = new Question(
            'Budget',
            'votre idée a-t-elle un impact financier ?',
            'Expliquez si votre proposition entraîne directement des recettes ou des dépenses pour l’État ou les collectivités locales. Si oui, donnez si possible des éléments de chiffrage.',
            6
        );
        $this->addReference('question-budget-impact', $questionBudgetImpact);
        $guidelineImplementation->addQuestion($questionBudgetImpact);

        $questionEcologyImpact = new Question(
            'Environnement',
            'votre idée a-t-elle un impact écologique ?',
            'Expliquez si votre idée a des effets positifs ou négatifs sur l\'environnement. Idéalement, précisez comment maximiser ou minimiser ces effets.',
            7
        );
        $this->addReference('question-ecology-impact', $questionEcologyImpact);
        $guidelineImplementation->addQuestion($questionEcologyImpact);

        $questionGenderEquality = new Question(
            'Égalité femmes-hommes',
            'votre idée a-t-elle un impact sur l’égalité entre les femmes et les hommes ?',
            'L\'égalité femmes-hommes est la grande cause du quiquennat. Expliquez si votre proposition a des effets positifs ou négatifs sur ce sujet',
            8
        );
        $this->addReference('question-gender-equality', $questionGenderEquality);
        $guidelineImplementation->addQuestion($questionGenderEquality);

        $questionDisabled = new Question(
            'Masquée',
            'Masquée',
            'Elle n\'est pas affichée.',
            9,
            true,
            false
        );
        $guidelineImplementation->addQuestion($questionDisabled);

        $manager->persist($questionProblem);
        $manager->persist($questionAnswer);
        $manager->persist($questionCompare);
        $manager->persist($questionNegativeEffect);
        $manager->persist($questionLawImpact);
        $manager->persist($questionBudgetImpact);
        $manager->persist($questionEcologyImpact);
        $manager->persist($questionGenderEquality);
        $manager->persist($questionDisabled);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaGuidelineData::class,
        ];
    }
}
