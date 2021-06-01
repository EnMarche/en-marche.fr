<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\SuggestedQuestion;
use App\Jecoute\SurveyQuestionTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadJecouteSuggestedQuestionData extends Fixture implements DependentFixtureInterface
{
    public const SUGGESTED_QUESTIONS = [
        'suggested-question-1' => [
            'content' => "Ceci est-il un champ libre d'une question suggérée ?",
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
            'published' => true,
        ],
        'suggested-question-2' => [
            'content' => 'Est-ce une question suggérée à choix multiple ?',
            'type' => SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE,
            'choices' => [
                'Réponse A',
                'Réponse B',
                'Réponse C',
            ],
            'published' => true,
        ],
        'suggested-question-3' => [
            'content' => 'Est-ce une question suggérée à choix unique ?',
            'type' => SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            'choices' => [
                'Réponse 1',
                'Réponse 2',
                'Réponse 3',
            ],
            'published' => true,
        ],
    ];

    public function load(ObjectManager $manager)
    {
        //AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_suggested_question');

        foreach (self::SUGGESTED_QUESTIONS as $code => $data) {
            $question = new SuggestedQuestion($data['content'], $data['type'], $data['published']);

            if (\array_key_exists('choices', $data)) {
                foreach ($data['choices'] as $choice) {
                    $question->addChoice(new Choice($choice));
                }
            }

            $manager->persist($question);
            $this->addReference($code, $question);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadJecouteQuestionData::class,
        ];
    }
}
