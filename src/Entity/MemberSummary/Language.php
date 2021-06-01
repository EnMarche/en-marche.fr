<?php

namespace App\Entity\MemberSummary;

use App\Entity\Summary;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberSummary\LanguageRepository")
 * @ORM\Table(name="member_summary_languages")
 */
class Language
{
    public const LEVEL_LOW = 'connaissance limitée';
    public const LEVEL_BASIC = 'maîtrise basique';
    public const LEVEL_MEDIUM = 'bonne maîtrise';
    public const LEVEL_HIGH = 'maîtrise parfaite';
    public const LEVEL_FLUENT = 'langue maternelle';

    public const LEVELS = [
        self::LEVEL_LOW,
        self::LEVEL_BASIC,
        self::LEVEL_MEDIUM,
        self::LEVEL_HIGH,
        self::LEVEL_FLUENT,
    ];

    public const LEVEL_CHOICES = [
        'member_summary.language.level.low' => self::LEVEL_LOW,
        'member_summary.language.level.basic' => self::LEVEL_BASIC,
        'member_summary.language.level.medium' => self::LEVEL_MEDIUM,
        'member_summary.language.level.high' => self::LEVEL_HIGH,
        'member_summary.language.level.fluent' => self::LEVEL_FLUENT,
    ];

    /**
     * @var int|null
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
     *
     * @Assert\NotBlank
     * @Assert\Language
     */
    private $code = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(strict=true, callback="getLevels")
     */
    private $level = '';

    /**
     * @var Summary|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Summary", inversedBy="languages")
     */
    private $summary;

    public function __toString(): string
    {
        return sprintf('%s - %s', ucfirst(Languages::getName($this->code)), ucfirst($this->level));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function getSummary(): ?Summary
    {
        return $this->summary;
    }

    public function setSummary(?Summary $summary)
    {
        $this->summary = $summary;
    }

    public static function getLevels(): array
    {
        return self::LEVELS;
    }

    public static function sortByLevel(iterable $languages): iterable
    {
        foreach (array_reverse(self::LEVELS) as $level) {
            foreach ($languages as $language) {
                if (!$language instanceof self) {
                    throw new \InvalidArgumentException(sprintf('Expected an instance of self "%s", got "%s."', __CLASS__, \is_object($language) ? \get_class($language) : \gettype($language)));
                }

                if ($level === $language->level) {
                    yield $language->id => $language;
                }
            }
        }
    }
}
