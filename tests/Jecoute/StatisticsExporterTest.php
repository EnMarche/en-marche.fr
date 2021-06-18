<?php

namespace Tests\App\Jecoute;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Question;
use App\Entity\Jecoute\SurveyQuestion;
use App\Jecoute\StatisticsExporter;
use App\Jecoute\SurveyQuestionTypeEnum;
use App\Repository\Jecoute\DataAnswerRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatisticsExporterTest extends TestCase
{
    /** @var MockObject|DataAnswerRepository */
    private $dataAnswerRepository;
    /** @var MockObject|StatisticsExporter */
    private $statisticsExporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataAnswerRepository = $this->getMockBuilder(DataAnswerRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->statisticsExporter = new StatisticsExporter($this->dataAnswerRepository);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->dataAnswerRepository = null;
        $this->statisticsExporter = null;
    }

    public function testExport(): void
    {
        /** @var Adherent $author */
        $author = $this->createMock(Adherent::class);
        $survey = new LocalSurvey($author, 'Questionnaire TestU');

        $question = new Question('question test u', SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE);
        $surveyQuestion = new SurveyQuestion($survey, $question);
        $survey->addQuestion($surveyQuestion);

        $findDataByQuestionReturn = [
            'survey' => [
                'name' => 'Questionnaire numéro 1',
            ],
            'questions' => [
                'uuid1' => [
                    [
                        'uuid' => '6ae09586-b8a8-4ea9-8b18-0158297b3656',
                        'question_content' => 'Ceci est-il un champ libre ?',
                        'type' => 'simple_field',
                        'total_simple_field' => 3,
                        'choice_content' => null,
                        'total' => 3,
                    ],
                ],
                'uuid2' => [
                    [
                        'question_content' => 'Est-ce une question à choix multiple ?',
                        'type' => 'multiple_choice',
                        'total_by_choice' => 2,
                        'choice_content' => 'Réponse A',
                        'total' => 3,
                    ],
                    [
                        'question_content' => 'Est-ce une question à choix multiple ?',
                        'type' => 'multiple_choice',
                        'total_by_choice' => 1,
                        'choice_content' => 'Réponse B',
                        'total' => 3,
                    ],
                ],
                'uuid3' => [
                    [
                        'question_content' => 'Est-ce une question à choix unique ?',
                        'type' => 'unique_choice',
                        'total_by_choice' => 1,
                        'choice_content' => 'Réponse A',
                        'total' => 2,
                    ],
                    [
                        'question_content' => 'Est-ce une question à choix unique ?',
                        'type' => 'unique_choice',
                        'total_by_choice' => 1,
                        'choice_content' => 'Réponse B',
                        'total' => 2,
                    ],
                ],
                'uuid4' => [
                    [
                        'uuid' => '6ae09586-b8a8-4ea9-8b18-0158297b3656',
                        'question_content' => 'Ceci est-il un champ libre d\'une question suggérée ?',
                        'type' => 'simple_field',
                        'total_simple_field' => 0,
                        'choice_content' => null,
                        'total' => 0,
                    ],
                ],
            ],
        ];

        $this->dataAnswerRepository
            ->expects($this->exactly(2))
            ->method('findAllBySurveyQuestion')
            ->willReturn([
                ['textField' => 'Test Unit', 'postedAt' => new \DateTime('now')],
                ['textField' => 'Test Unit 2', 'postedAt' => '2019-01-01 01:00:00'],
            ], [])
        ;

        $content = $this->statisticsExporter->export($findDataByQuestionReturn);

        $this->assertSame(1, substr_count($content, 'Questionnaire numéro 1 '));
        $this->assertSame(1, substr_count($content, 'Aucune donnée n\'est disponible pour le moment.'.\PHP_EOL));
        $this->assertSame(1, substr_count($content, 'Est-ce une question à choix multiple ?'.\PHP_EOL));
        $this->assertSame(1, substr_count($content, 'Réponse A;66.67%;2'.\PHP_EOL));
        $this->assertSame(1, substr_count($content, 'Réponse A;50%;1'.\PHP_EOL));
        $this->assertSame(1, substr_count($content, 'Test Unit 2;2019-01-01 01:00:00'.\PHP_EOL));
    }
}
