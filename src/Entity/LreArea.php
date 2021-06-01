<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class LreArea
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ReferentTag|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ReferentTag")
     * @ORM\JoinColumn(nullable=true)
     */
    private $referentTag;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $allTags = false;

    public function __construct(ReferentTag $tag = null)
    {
        $this->referentTag = $tag;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReferentTag(): ?ReferentTag
    {
        return $this->referentTag;
    }

    public function setReferentTag(?ReferentTag $referentTag): void
    {
        $this->referentTag = $referentTag;
    }

    public function isAllTags(): bool
    {
        return $this->allTags;
    }

    public function setAllTags(bool $allTags): void
    {
        $this->allTags = $allTags;
    }

    /**
     * @Assert\IsTrue(message="lre_area.all_tags_and_one_selected")
     */
    public function isValid()
    {
        return (null !== $this->referentTag) ^ $this->allTags;
    }
}
