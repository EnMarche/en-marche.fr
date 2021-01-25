<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ConsularManagedArea
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
     * @var ConsularDistrict
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConsularDistrict")
     */
    private $consularDistrict;

    public function getId(): int
    {
        return $this->id;
    }

    public function getConsularDistricts(): ConsularDistrict
    {
        return $this->consularDistrict;
    }

    public function setConsularDistrict(ConsularDistrict $consularDistrict): void
    {
        $this->consularDistrict = $consularDistrict;
    }
}
