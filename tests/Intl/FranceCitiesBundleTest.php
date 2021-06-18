<?php

namespace Tests\App\Intl;

use App\Entity\ReferentTag;
use App\Intl\FranceCitiesBundle;
use PHPUnit\Framework\TestCase;

class FranceCitiesBundleTest extends TestCase
{
    /**
     * @dataProvider provideSearchCitiesForTags
     */
    public function testSearchCitiesForTags(array $tags, string $search, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, FranceCitiesBundle::searchCitiesForTags($tags, $search));
    }

    public function provideSearchCitiesForTags(): iterable
    {
        yield [
            [
                $this->createReferentTag('92', true, false),
            ],
            'Bois Colom',
            [
                [
                    'name' => 'Bois-Colombes',
                    'postal_code' => '92270',
                    'insee_code' => '92009',
                ],
            ],
        ];
        yield [
            [
                $this->createReferentTag('77', true, false),
            ],
            'Bois Colom',
            [],
        ];

        yield [
            [
                $this->createReferentTag('92', true, false),
            ],
            'Melun',
            [],
        ];
        yield [
            [
                $this->createReferentTag('77', true, false),
            ],
            'Melun',
            [
                [
                    'name' => 'Melun',
                    'postal_code' => '77000',
                    'insee_code' => '77288',
                ],
            ],
        ];
    }

    private function createReferentTag(string $code, bool $isDepartmentTag, bool $isBoroughTag): ReferentTag
    {
        $tag = $this->createMock(ReferentTag::class);
        $tag->expects($this->any())
            ->method('getCode')
            ->willReturn($code)
        ;
        $tag->expects($this->any())
            ->method('isDepartmentTag')
            ->willReturn($isDepartmentTag)
        ;
        $tag->expects($this->any())
            ->method('isBoroughTag')
            ->willReturn($isBoroughTag)
        ;

        return $tag;
    }

    /**
     * @dataProvider providePostalCodes
     */
    public function testGetPostalCodeCities($postalCode, $expectedCities)
    {
        $this->assertEquals($expectedCities, FranceCitiesBundle::getPostalCodeCities($postalCode));
    }

    public function providePostalCodes(): array
    {
        return [
            [
                '75001',
                [
                    75101 => 'Paris 1er',
                ],
            ],
            [
                '35420',
                [
                    35018 => 'La Bazouge-du-Désert',
                    35111 => 'Le Ferré',
                    35162 => 'Louvigné-du-Désert',
                    35174 => 'Mellé',
                    35190 => 'Monthault',
                    35230 => 'Poilley',
                    35271 => 'Saint-Georges-de-Reintembault',
                    35357 => 'Villamée',
                ],
            ],

            // Réunion
            [
                '97441',
                [
                    97420 => 'Sainte-Suzanne',
                ],
            ],

            // Guadeloupe
            [
                '97122',
                [
                    97103 => 'Baie-Mahault',
                ],
            ],

            // Saint-Pierre-et-Miquelon
            [
                '97500',
                [
                    97501 => 'Miquelon-Langlade',
                    97502 => 'Saint-Pierre',
                ],
            ],
        ];
    }
}
