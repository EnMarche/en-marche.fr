<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Question;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadNoteAnswerData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $mandatory = true;

        $questionProblem = Question::create(
            'Constat : quel problème souhaitez vous résoudre ?',
            'Expliquez, en maximum 1700 caractères (espaces compris) le problème que vous identifiez et espérez pouvoir remédier.',
            $mandatory
        );
        $this->addReference('question-problem', $questionProblem);

        $questionAnswer = Question::create(
            'Solution : quelle réponse votre idée apporte-t-elle ? ',
            'Expliquez, en maximum 1700 caractères (espaces compris), comment votre proposition répond concrètement au problème.',
            $mandatory
        );
        $this->addReference('question-answer', $questionAnswer);

        $questionCompare = Question::create(
            'Comparaison : cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si et comment cette proposition a été étudiée ou mise en oeuvre en France ou à l’étranger.'
        );
        $this->addReference('question-compare', $questionCompare);

        $questionNegativeEffect = Question::create(
            'Impact : Cette proposition peut elle avoir des effets négatifs pour certains publics ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si cette proposition peut porter préjudice à certains publics (individus, entreprises, associations, ou pays) et comment il est possible d’en limiter les effets.'
        );
        $this->addReference('question-negative-effect', $questionNegativeEffect);

        $questionLawImpact = Question::create(
            'Droit : votre idée suppose t-elle de changer le droit ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu’il faudrait changer.'
        );
        $this->addReference('question-law-impact', $questionLawImpact);

        $questionBudgetImpact = Question::create(
            'Budget : votre idée a-t-elle un impact financier ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée entraîne directement des recettes ou des dépenses pour l’État ou les collectivités locales. Si oui, idéalement, donnez des éléments de chiffrage.'
        );
        $this->addReference('question-budget-impact', $questionBudgetImpact);

        $questionEcologyImpact = Question::create(
            'Environnement : votre idée a t-elle un impact écologique ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée a des effets positifs ou négatifs sur l’environnement. Idéalement, précisez des éléments de réponse pour maximiser ou minimiser (selon les cas) ces effets.'
        );
        $this->addReference('question-ecology-impact', $questionEcologyImpact);

        $questionGenderEquality = Question::create(
            'Égalité femmes-hommes : votre idée a t-elle un impact sur l’égalité entre les femmes et les hommes ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée a des effets positifs ou négatifs sur l’égalité entre les femmes et les hommes. Idéalement, donnez des éléments pour maximiser ou minimiser (selon les cas) ces effets.'
        );
        $this->addReference('question-gender-equality', $questionGenderEquality);

        $manager->persist($questionProblem);
        $manager->persist($questionAnswer);
        $manager->persist($questionCompare);
        $manager->persist($questionNegativeEffect);
        $manager->persist($questionLawImpact);
        $manager->persist($questionBudgetImpact);
        $manager->persist($questionEcologyImpact);
        $manager->persist($questionGenderEquality);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadNoteGuidelineData::class,
        ];
    }
}
