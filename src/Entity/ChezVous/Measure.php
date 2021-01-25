<?php

namespace App\Entity\ChezVous;

use App\Validator\ChezVous\MeasurePayload;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChezVous\MeasureRepository")
 * @ORM\Table(name="chez_vous_measures", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="chez_vous_measures_city_type_unique", columns={"city_id", "type_id"}),
 * })
 *
 * @MeasurePayload
 *
 * @UniqueEntity(fields={"city", "type"}, errorPath="type")
 */
class Measure
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
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $payload;

    /**
     * @var City|null
     *
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="measures")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $city;

    /**
     * @var MeasureType|null
     *
     * @ORM\ManyToOne(targetEntity=MeasureType::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     */
    private $type;

    public function __construct(City $city = null, MeasureType $type = null, array $payload = null)
    {
        $this->city = $city;
        $this->type = $type;
        $this->payload = $payload;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?MeasureType
    {
        return $this->type;
    }

    public function setType(?MeasureType $type): void
    {
        $this->type = $type;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): void
    {
        $this->city = $city;
    }

    public function getEntries(): iterable
    {
        if (!$this->payload) {
            return [];
        }

        $entries = [];

        foreach ($this->payload as $key => $value) {
            $entries[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $entries;
    }

    public function setEntries(iterable $entries): void
    {
        if (empty($entries)) {
            $this->payload = null;
        }

        $this->payload = [];

        foreach ($entries as $entry) {
            if (!\array_key_exists('key', $entry) || !\array_key_exists('value', $entry)) {
                continue;
            }

            $this->payload[$entry['key']] = $entry['value'];
        }
    }
}
