<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class MunicipalManagerSupervisorRole
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $referent;

    public function __construct(Adherent $referent)
    {
        $this->referent = $referent;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReferent(): Adherent
    {
        return $this->referent;
    }
}
