<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SenatorArea
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
     */
    private $departmentTag;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDepartmentTag(): ?ReferentTag
    {
        return $this->departmentTag;
    }

    public function setDepartmentTag(?ReferentTag $departmentTag): void
    {
        $this->departmentTag = $departmentTag;
    }
}
