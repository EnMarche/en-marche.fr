<?php

namespace Tests\App\Device;

use App\Device\DeviceManager;
use App\Entity\Device;
use App\Entity\Geo\Zone;
use App\Repository\DeviceRepository;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class DeviceManagerTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    /** @var DeviceRepository */
    private $deviceRepository;
    /** @var DeviceManager */
    private $deviceManager;

    /**
     * @dataProvider provideDeviceNewPostalCodes
     */
    public function testRefreshZoneFromPostalCode(string $newPostalCode, string $newZoneName, string $newZoneType): void
    {
        /** @var Device $device */
        $device = $this->deviceRepository->findOneByDeviceUuid('device_2');
        /** @var Zone $zoneBeforeUpdate */
        $zoneBeforeUpdate = $device->getZones()->first();

        self::assertSame('Antony', $zoneBeforeUpdate->getName());
        self::assertSame(Zone::CITY, $zoneBeforeUpdate->getType());

        $device->setPostalCode($newPostalCode);
        $this->deviceManager->refreshZoneFromPostalCode($device);

        /** @var Zone $zoneAfterUpdate */
        $zoneAfterUpdate = $device->getZones()->first();

        self::assertSame($newZoneName, $zoneAfterUpdate->getName());
        self::assertSame($newZoneType, $zoneAfterUpdate->getType());
    }

    public function provideDeviceNewPostalCodes(): iterable
    {
        yield ['94300', 'Vincennes', Zone::CITY];
        yield ['06000', 'Nice', Zone::CITY];
        yield ['59000', 'Lille', Zone::CITY];
        yield ['75016', 'Paris 16ème', Zone::BOROUGH];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceRepository = $this->get(DeviceRepository::class);
        $this->deviceManager = $this->get(DeviceManager::class);
    }

    protected function tearDown(): void
    {
        $this->deviceRepository = null;
        $this->deviceManager = null;

        parent::tearDown();
    }
}
