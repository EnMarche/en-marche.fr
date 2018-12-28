<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\EnabledInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"guideline_read"}
 *         },
 *         "order": {"position": "ASC"},
 *         "pagination_enabled": false,
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 * @ORM\Table(name="ideas_workshop_guideline")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Guideline implements EnabledInterface
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
    private $enabled;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Question", mappedBy="guideline")
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $questions;

    /**
     * @Assert\GreaterThanOrEqual(0)
     *
     * @Gedmo\SortablePosition
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $position;

    /**
     * @ORM\Column
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $name;

    public function __construct(string $name, bool $enabled = true, int $position = 0)
    {
        $this->name = $name;
        $this->position = $position;
        $this->enabled = $enabled;
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addQuestion(Question $question): void
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);

            $question->setGuideline($this);
        }
    }

    public function removeQuestion(Question $question): void
    {
        $this->questions->removeElement($question);
    }

    public function getQuestions(): Collection
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
