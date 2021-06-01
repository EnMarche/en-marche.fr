<?php

namespace App\Entity\MemberSummary;

use App\Entity\EntitySpanTrait;
use App\Entity\Summary;
use App\Summary\Contract;
use App\Summary\SummaryItemPositionableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberSummary\JobExperienceRepository")
 * @ORM\Table(name="member_summary_job_experiences")
 */
class JobExperience implements SummaryItemPositionableInterface
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
    private $company = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=200)
     */
    private $position = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=200)
     */
    private $location = '';

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     * @Assert\Length(min=2, max=200)
     */
    private $website;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     * @Assert\Length(min=2, max=200)
     */
    private $companyFacebookPage;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(min=2, max=200)
     */
    private $companyTwitterNickname;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(strict=true, callback={"\App\Summary\Contract", "all"})
     */
    private $contract = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(strict=true, callback={"\App\Summary\JobDuration", "all"})
     */
    private $duration = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=2, max=300)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $displayOrder = 1;

    /**
     * @var Summary|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Summary", inversedBy="experiences")
     */
    private $summary;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getCompanyFacebookPage(): ?string
    {
        return $this->companyFacebookPage;
    }

    public function setCompanyFacebookPage(?string $companyFacebookPage): void
    {
        $this->companyFacebookPage = $companyFacebookPage;
    }

    public function getCompanyTwitterNickname(): ?string
    {
        return $this->companyTwitterNickname;
    }

    public function setCompanyTwitterNickname(?string $companyTwitterNickname): void
    {
        $this->companyTwitterNickname = $companyTwitterNickname;
    }

    public function getContract(): string
    {
        return $this->contract;
    }

    public function setContract(string $contract): void
    {
        $this->contract = $contract;
    }

    public function getContractLabel(): string
    {
        return Contract::getLabel($this->contract);
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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

    public function getTitle(): string
    {
        return $this->getContractLabel().' '.$this->position;
    }
}
