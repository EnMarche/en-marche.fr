<?php

namespace App\Entity;

use App\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="je_marche_reports")
 * @ORM\Entity(repositoryClass="App\Repository\JeMarcheReportRepository")
 */
class JeMarcheReport
{
    use EntityTimestampableTrait;

    const TYPE_KIOSQUE = 'kiosque';
    const TYPE_WALK = 'la-marche';
    const TYPE_DOOR_TO_DOOR = 'porte-a-porte';
    const TYPE_DINNER = 'diner';
    const TYPE_CONVERSATION = 'conversation';
    const TYPE_WORKSHOP = 'atelier';
    const TYPE_ACTION = 'action-qui-me-ressemble';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=30)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback="getTypes")
     */
    private $type = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress = '';

    /**
     * @var string
     *
     * @ORM\Column(length=11)
     *
     * @Assert\NotBlank(message="jemarche.postal_code.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=11,
     *     minMessage="jemarche.postal_code.invalid",
     *     maxMessage="jemarche.postal_code.invalid"
     * )
     */
    private $postalCode = '';

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\All({
     *     @Assert\Email(message="jemarche.email.invalid"),
     *     @Assert\Length(max=255, maxMessage="common.email.max_length")
     * })
     */
    private $convinced = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\All({
     *     @Assert\Email(message="jemarche.email.invalid"),
     *     @Assert\Length(max=255, maxMessage="common.email.max_length")
     * })
     */
    private $almostConvinced = [];

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", nullable=true, options={"unsigned": true})
     *
     * @Assert\GreaterThanOrEqual(value=0, message="jemarche.not_conviced.greater_than_or_equal_0")
     * @Assert\LessThanOrEqual(value=65535, message="jemarche.not_conviced.less_than_or_equal_65000")
     */
    private $notConvinced;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=2500, maxMessage="jemarche.reaction.max_2500")
     */
    private $reaction = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message")
     * @AssertRecaptcha
     */
    public $recaptcha = '';

    public static function createWithCaptcha(string $recaptcha): self
    {
        $report = new self();
        $report->recaptcha = $recaptcha;

        return $report;
    }

    /**
     * @Assert\Callback
     */
    public function validateOneFieldNotBlank(ExecutionContextInterface $context): void
    {
        if (!$this->notConvinced && !$this->almostConvinced && !$this->convinced) {
            $context->addViolation('Vous devez entrer au moins un contact que vous avez obtenu durant une action.');
        }
    }

    public function __toString()
    {
        return $this->type.' de '.$this->emailAddress;
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_KIOSQUE,
            self::TYPE_WALK,
            self::TYPE_DOOR_TO_DOOR,
            self::TYPE_DINNER,
            self::TYPE_CONVERSATION,
            self::TYPE_WORKSHOP,
            self::TYPE_ACTION,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getConvinced(): array
    {
        return $this->convinced;
    }

    public function getConvincedList(string $glue = '|'): string
    {
        return implode($glue, (array) $this->convinced);
    }

    public function getConvincedAsString(): string
    {
        return implode("\n", $this->convinced);
    }

    public function countConvinced(): int
    {
        return \count($this->convinced);
    }

    public function setConvinced(array $convinced): void
    {
        $this->convinced = $convinced;
    }

    public function getAlmostConvinced(): array
    {
        return $this->almostConvinced;
    }

    public function getAlmostConvincedList(string $glue = '|'): string
    {
        return implode($glue, (array) $this->almostConvinced);
    }

    public function getAlmostConvincedAsString(): string
    {
        return implode("\n", $this->almostConvinced);
    }

    public function countAlmostConvinced(): int
    {
        return \count($this->almostConvinced);
    }

    public function setAlmostConvinced(array $almostConvinced): void
    {
        $this->almostConvinced = $almostConvinced;
    }

    public function getNotConvinced(): ?int
    {
        return $this->notConvinced;
    }

    public function setNotConvinced(?int $notConvinced): void
    {
        $this->notConvinced = $notConvinced;
    }

    public function getReaction(): ?string
    {
        return $this->reaction;
    }

    public function setReaction(?string $reaction): void
    {
        $this->reaction = $reaction;
    }
}
