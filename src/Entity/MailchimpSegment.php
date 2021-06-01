<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MailchimpSegmentRepository")
 */
class MailchimpSegment
{
    public const LIST_MAIN = 'main';
    public const LIST_ELECTED_REPRESENTATIVE = 'elected_representative';

    public const LISTS = [
        self::LIST_MAIN,
        self::LIST_ELECTED_REPRESENTATIVE,
    ];

    /**
     * @var int
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
     */
    private $list;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $externalId;

    public function __construct(string $list, string $label, string $externalId = null)
    {
        $this->list = $list;
        $this->label = $label;
        $this->externalId = $externalId;
    }

    public static function createElectedRepresentativeSegment(string $label, string $externalId = null)
    {
        return new self(self::LIST_ELECTED_REPRESENTATIVE, $label, $externalId);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getList(): string
    {
        return $this->list;
    }

    public function setList(string $list): void
    {
        $this->list = $list;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function __toString()
    {
        return sprintf('[%s] %s', $this->list, $this->label);
    }
}
