<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MunicipalManagerRoleAssociation
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var City[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\City")
     * @ORM\JoinTable(name="municipal_manager_role_association_cities",
     *     joinColumns={@ORM\JoinColumn(name="municipal_manager_role_association_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="city_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $cities;

    public function __construct(array $cities)
    {
        $this->cities = new ArrayCollection($cities);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): void
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
        }
    }

    public function removeCity(City $city): void
    {
        $this->cities->removeElement($city);
    }

    public function getPostalCodes(): array
    {
        $codes = [];

        foreach ($this->cities as $city) {
            $codes = array_merge($codes, $city->getPostalCodes());
        }

        return array_values(array_unique($codes));
    }

    public function getInseeCodes(): array
    {
        return $this->cities->map(static function (City $city) {
            return $city->getInseeCode();
        })->toArray();
    }
}
