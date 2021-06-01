<?php

namespace App\Entity\Jecoute;

use App\Validator\DataSurveyAnswerTypeChoice;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_data_answer")
 * @ORM\Entity
 *
 * @DataSurveyAnswerTypeChoice
 */
class DataAnswer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SurveyQuestion", inversedBy="dataAnswers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $surveyQuestion;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $textField;

    /**
     * @var Choice[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Choice", fetch="EAGER", inversedBy="dataAnswers")
     * @ORM\JoinTable(
     *     name="jecoute_data_answer_selected_choices",
     *     joinColumns={
     *         @ORM\JoinColumn(name="data_answer_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="choice_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    private $selectedChoices;

    /**
     * @var DataSurvey
     *
     * @ORM\ManyToOne(targetEntity="DataSurvey", inversedBy="answers", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dataSurvey;

    public function __construct()
    {
        $this->selectedChoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurveyQuestion(): ?SurveyQuestion
    {
        return $this->surveyQuestion;
    }

    public function setSurveyQuestion(SurveyQuestion $surveyQuestion): void
    {
        $this->surveyQuestion = $surveyQuestion;
    }

    public function getTextField(): ?string
    {
        return $this->textField;
    }

    public function setTextField(string $textField): void
    {
        $this->textField = $textField;
    }

    public function getSelectedChoices(): Collection
    {
        return $this->selectedChoices;
    }

    public function addSelectedChoice(Choice $selectedChoice): void
    {
        if (!$this->selectedChoices->contains($selectedChoice)) {
            $this->selectedChoices->add($selectedChoice);
        }
    }

    public function removeSelectedChoice(Choice $selectedChoice): void
    {
        $this->selectedChoices->removeElement($selectedChoice);
    }

    public function getDataSurvey(): DataSurvey
    {
        return $this->dataSurvey;
    }

    public function setDataSurvey(DataSurvey $dataSurvey): void
    {
        $this->dataSurvey = $dataSurvey;
    }
}
