<?php

namespace App\Entity\Jecoute;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_survey_question")
 * @ORM\Entity
 */
class SurveyQuestion implements AuthoredInterface
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Survey", inversedBy="questions", cascade={"persist"})
     * @Gedmo\SortableGroup
     */
    private $survey;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question", cascade={"persist"})
     *
     * @Assert\Valid
     */
    private $question;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Jecoute\DataAnswer", mappedBy="surveyQuestion")
     */
    private $dataAnswers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $fromSuggestedQuestion;

    public function __construct(Survey $survey = null, Question $question = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->survey = $survey;
        $this->question = $question;
        $this->dataAnswers = new ArrayCollection();
    }

    public function resetId(): void
    {
        $this->id = null;
    }

    public function refreshUuid(): void
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getAuthor(): ?Adherent
    {
        return $this->getSurvey()->getAuthor();
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): void
    {
        $this->survey = $survey;
    }

    public function resetSurvey(): void
    {
        $this->survey = null;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    public function setFromSuggestedQuestion(?int $questionId): void
    {
        $this->fromSuggestedQuestion = $questionId;
    }

    public function getFromSuggestedQuestion(): ?int
    {
        return $this->fromSuggestedQuestion;
    }

    /**
     * @return DataAnswer[]|Collection
     */
    public function getDataAnswers(): Collection
    {
        return $this->dataAnswers;
    }

    public function getDataAnswersFor(SurveyQuestion $surveyQuestion, DataSurvey $dataSurvey)
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('surveyQuestion', $surveyQuestion))
            ->andWhere(Criteria::expr()->eq('dataSurvey', $dataSurvey))
        ;

        return $this->dataAnswers->matching($criteria)->first();
    }

    public function addDataAnswer(DataAnswer $dataAnswer): void
    {
        if (!$this->dataAnswers->contains($dataAnswer)) {
            $this->dataAnswers->add($dataAnswer);
        }
    }

    public function removeDataAnswer(DataAnswer $dataAnswer): void
    {
        $this->dataAnswers->removeElement($dataAnswer);
    }

    public function __clone()
    {
        $this->resetId();
        $this->refreshUuid();
    }
}
