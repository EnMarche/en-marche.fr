<?php

namespace Tests\App\Geo;

use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Geo\ZoneMatcher;
use Tests\App\AbstractKernelTestCase;

class ZoneMatcherTest extends AbstractKernelTestCase
{
    /**
     * @var ZoneMatcher
     */
    private $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = $this->get(ZoneMatcher::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->matcher = null;
    }

    public function testInseeMatch(): void
    {
        $clichy = PostAddress::createFrenchAddress('98 bld Victor Hugo', 'XXXXX-92024');
        $paris8 = PostAddress::createFrenchAddress('26 rue de la Paix', 'XXXXX-75108');
        $lyon1 = PostAddress::createFrenchAddress('2 Rue de la République', 'XXXXX-69381');

        $zonesClichy = $this->matcher->match($clichy);
        $zonesParis8 = $this->matcher->match($paris8);
        $zonesLyon1 = $this->matcher->match($lyon1);

        self::assertCount(1, $zonesClichy);
        self::assertCount(1, $zonesParis8);
        self::assertCount(1, $zonesLyon1);

        self::assertSame('92024', $zonesClichy[0]->getCode());
        self::assertSame(Zone::CITY, $zonesClichy[0]->getType());

        self::assertSame('75108', $zonesParis8[0]->getCode());
        self::assertSame(Zone::BOROUGH, $zonesParis8[0]->getType());

        self::assertSame('69381', $zonesLyon1[0]->getCode());
        self::assertSame(Zone::BOROUGH, $zonesLyon1[0]->getType());
    }

    public function testPostalCodeMatch(): void
    {
        $clichy = PostAddress::createEmptyAddress();
        $clichy->setPostalCode('92110');

        $zones = $this->matcher->match($clichy);

        self::assertCount(1, $zones);

        self::assertSame('92024', $zones[0]->getCode());
        self::assertSame(Zone::CITY, $zones[0]->getType());
    }

    public function testCountryMatch(): void
    {
        $zurich = PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse');

        $zones = $this->matcher->match($zurich);

        self::assertCount(1, $zones);

        self::assertSame('CH', $zones[0]->getCode());
        self::assertSame(Zone::COUNTRY, $zones[0]->getType());
    }
}
