<?php

namespace App\Entity\Jecoute;

use App\Jecoute\SurveyQuestionTypeEnum;
use App\Validator\SurveyQuestionTypeChoice;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_question")
 * @ORM\Entity
 *
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({
 *     "question": "Question",
 *     "suggested_question": "SuggestedQuestion"
 * })
 *
 * @SurveyQuestionTypeChoice
 */
class Question
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @SymfonySerializer\Groups("survey_list")
     */
    private $content;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Jecoute\SurveyQuestionTypeEnum", "all"})
     *
     * @SymfonySerializer\Groups("survey_list")
     */
    private $type;

    /**
     * @var Choice[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Choice", mappedBy="question", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid
     *
     * @SymfonySerializer\Groups("survey_list")
     */
    private $choices;

    public function __construct(string $content = null, string $type = SurveyQuestionTypeEnum::SIMPLE_FIELD)
    {
        $this->content = $content;
        $this->type = $type;
        $this->choices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function resetId(): void
    {
        $this->id = null;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getTypeLabel(): string
    {
        return array_flip(SurveyQuestionTypeEnum::all())[$this->type];
    }

    public function getChoicesAsJson(): string
    {
        return json_encode(array_map(function (Choice $choice) {
            return $choice->getContent();
        }, $this->choices->toArray()));
    }

    public function addChoice(Choice $choice): void
    {
        if (!$this->choices->contains($choice)) {
            $choice->setQuestion($this);
            $this->choices->add($choice);
        }
    }

    public function removeChoice(Choice $choice): void
    {
        $this->choices->removeElement($choice);
    }

    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function setChoices(Collection $choices): void
    {
        $this->choices = $choices;
    }

    public function isChoiceType(): bool
    {
        return \in_array($this->type, [
            SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE,
        ], true);
    }

    public function __clone()
    {
        $this->resetId();

        $choices = new ArrayCollection();
        foreach ($this->getChoices() as $choice) {
            /** @var Choice $choice */
            $clonedChoice = clone $choice;
            $clonedChoice->setQuestion($this);

            $choices->add($clonedChoice);
        }

        $this->setChoices($choices);
    }
}
