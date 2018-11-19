<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="note_guideline")
 * @ORM\Entity
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Guideline
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Question", mappedBy="guideline")
     */
    private $questions;

    /**
     * @Assert\GreaterThanOrEqual(0)
     *
     * @Gedmo\SortablePosition
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $position = 0;

    /**
     * @ORM\Column
     */
    private $name;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public static function create(string $name, bool $enable = true): Guideline
    {
        $guideline = new self();

        $guideline->name = $name;
        $guideline->enabled = $enable;

        return $guideline;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addQuestion(Question $question): void
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
        }
    }

    public function removeQuestion(Question $question): void
    {
        $this->questions->removeElement($question);
    }

    public function getQuestions(): ArrayCollection
    {
        return $this->questions;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
