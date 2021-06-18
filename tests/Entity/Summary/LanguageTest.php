<?php

namespace Tests\App\Entity\Summary;

use App\Entity\MemberSummary\Language;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    public function testSortByLevel()
    {
        $languages = [
            $language1 = $this->createLanguage(1, Language::LEVEL_LOW),
            $language2 = $this->createLanguage(2, Language::LEVEL_BASIC),
            $language3 = $this->createLanguage(3, Language::LEVEL_MEDIUM),
            $language4 = $this->createLanguage(4, Language::LEVEL_HIGH),
            $language5 = $this->createLanguage(5, Language::LEVEL_FLUENT),
            $language6 = $this->createLanguage(6, Language::LEVEL_LOW),
            $language7 = $this->createLanguage(7, Language::LEVEL_BASIC),
            $language8 = $this->createLanguage(8, Language::LEVEL_MEDIUM),
            $language9 = $this->createLanguage(9, Language::LEVEL_HIGH),
            $language10 = $this->createLanguage(10, Language::LEVEL_FLUENT),
        ];

        $expectedSort = [
            5 => $language5,
            10 => $language10,
            4 => $language4,
            9 => $language9,
            3 => $language3,
            8 => $language8,
            2 => $language2,
            7 => $language7,
            1 => $language1,
            6 => $language6,
        ];
        $sorted = [];

        foreach (Language::sortByLevel($languages) as $id => $language) {
            $sorted[$id] = $language;

            $this->assertSame($expectedSort[$id], $language);
        }

        $this->assertSame($expectedSort, $sorted);
    }

    private function createLanguage(int $id, string $level): Language
    {
        $language = new Language();

        $idRefl = new \ReflectionProperty(Language::class, 'id');
        $idRefl->setAccessible(true);
        $idRefl->setValue($language, $id);

        $language->setLevel($level);

        return $language;
    }
}
