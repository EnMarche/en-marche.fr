<?php

namespace App\Entity\Reporting;

use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AdministratorExportHistory
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
     * @var Administrator
     *
     * @ORM\ManyToOne(targetEntity=Administrator::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $administrator;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $routeName;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $parameters;

    public function __construct(Administrator $administrator, string $routeName, array $parameters)
    {
        $this->administrator = $administrator;
        $this->routeName = $routeName;
        $this->parameters = $parameters;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdministrator(): Administrator
    {
        return $this->administrator;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
