<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\ReferentSpaceAccessInformationRepository")
 */
class ReferentSpaceAccessInformation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTimeImmutable|null
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $previousDate;

    /**
     * @var \DateTimeImmutable|null
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $lastDate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(name="adherent_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $adherent;

    public function __construct(Adherent $adherent, \DateTimeImmutable $lastDate, \DateTimeImmutable $previousDate)
    {
        $this->adherent = $adherent;
        $this->lastDate = $lastDate;
        $this->previousDate = $previousDate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastDate(): \DateTimeImmutable
    {
        return $this->lastDate;
    }

    public function setLastDate(\DateTimeImmutable $lastDate): void
    {
        $this->lastDate = $lastDate;
    }

    public function getPreviousDate(): \DateTimeImmutable
    {
        return $this->previousDate;
    }

    public function setPreviousDate(\DateTimeImmutable $previousDate): void
    {
        $this->previousDate = $previousDate;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    /**
     * Update date of the last access to referent space and the preview date for referent space.
     *
     * @param string|int $timestamp a valid date representation as a string or integer
     */
    public function update($timestamp = 'now'): void
    {
        $newLastAccessDate = new \DateTimeImmutable($timestamp);
        if ($this->previousDate->format('Y-m-d') != $newLastAccessDate->format('Y-m-d')) {
            $this->previousDate = $this->lastDate;
        }

        $this->lastDate = $newLastAccessDate;
    }
}
