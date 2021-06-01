<?php

namespace App\Entity\MemberSummary;

use App\Entity\EntitySpanTrait;
use App\Entity\Summary;
use App\Summary\SummaryItemPositionableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberSummary\TrainingRepository")
 * @ORM\Table(name="member_summary_trainings")
 */
class Training implements SummaryItemPositionableInterface
{
    use EntitySpanTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=200)
     */
    private $organization = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=200)
     */
    private $diploma = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=200)
     */
    private $studyField = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=2, max=300)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=2, max=200)
     */
    private $extraCurricular;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $displayOrder = 1;

    /**
     * @var Summary|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Summary", inversedBy="trainings")
     */
    private $summary;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): string
    {
        return $this->organization;
    }

    public function setOrganization(string $organization): void
    {
        $this->organization = $organization;
    }

    public function getDiploma(): string
    {
        return $this->diploma;
    }

    public function setDiploma(string $diploma): void
    {
        $this->diploma = $diploma;
    }

    public function getStudyField(): string
    {
        return $this->studyField;
    }

    public function setStudyField(string $studyField): void
    {
        $this->studyField = $studyField;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getExtraCurricular(): ?string
    {
        return $this->extraCurricular;
    }

    public function setExtraCurricular(?string $extraCurricular): void
    {
        $this->extraCurricular = $extraCurricular;
    }

    public function isNew(): bool
    {
        return null === $this->id;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): void
    {
        $this->displayOrder = $displayOrder;
    }

    public function getSummary(): ?Summary
    {
        return $this->summary;
    }

    public function setSummary(?Summary $summary)
    {
        $this->summary = $summary;
    }

    public function getTitle()
    {
        return $this->diploma.' - '.$this->studyField;
    }
}
