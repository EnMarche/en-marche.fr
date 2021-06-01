<?php

namespace App\Entity\ChezVous;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChezVous\RegionRepository")
 * @ORM\Table(name="chez_vous_regions")
 *
 * @UniqueEntity("code")
 */
class Region
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="10")
     */
    private $code;

    /**
     * @var Department[]|Collection
     *
     * @ORM\OneToMany(targetEntity=Department::class, mappedBy="region", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $departments;

    public function __construct(string $name = null, string $code = null)
    {
        $this->name = $name;
        $this->code = $code;
        $this->departments = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): void
    {
        if (!$this->departments->contains($department)) {
            $department->setRegion($this);
            $this->departments->add($department);
        }
    }

    public function removeDepartment(Department $department): void
    {
        $this->departments->removeElement($department);
    }
}
