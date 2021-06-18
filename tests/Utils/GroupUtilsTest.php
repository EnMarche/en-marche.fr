<?php

namespace Tests\App\Utils;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Utils\GroupUtils;
use Ramsey\Uuid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class GroupUtilsTest extends AbstractKernelTestCase
{
    public function testGetUuidsFromJson()
    {
        $this->assertSame([], GroupUtils::getUuidsFromJson(''));
        $this->assertSame([], GroupUtils::getUuidsFromJson('not valid JSON syntax'));
        $this->assertSame([], GroupUtils::getUuidsFromJson('[]'));
        $this->assertSame([], GroupUtils::getUuidsFromJson('{}'));
        $this->assertSame([], GroupUtils::getUuidsFromJson('"dfbb56f5-e7df-4fdc-9777-e8237cef4872"'));
        $this->assertSame([], GroupUtils::getUuidsFromJson('["e7df-4fdc-9777-e8237cef4872"]'));

        $stringUuids = [
            'dfbb56f5-e7df-4fdc-9777-e8237cef4872',
            '1501e14a-56c9-4876-b5aa-11fa046bc838',
            '53d4ab7f-dcec-4083-9486-feac8d791325',
        ];

        $objectUuids = array_map(function ($item) {
            return Uuid::fromString($item);
        }, $stringUuids);

        $this->assertEquals($objectUuids, GroupUtils::getUuidsFromJson(json_encode($stringUuids)));
    }

    public function testRemoveUnknownAdherents()
    {
        $adherents = $this->getAdherents();

        $this->assertSame([], GroupUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            []
        ));
        $this->assertSame([], GroupUtils::removeUnknownAdherents(
            [Uuid::fromString(LoadAdherentData::ADHERENT_1_UUID)],
            []
        ));
        $this->assertSame([], GroupUtils::removeUnknownAdherents(
            [],
            $adherents
        ));
        $this->assertSame([$adherents[0]], GroupUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            $adherents
        ));
        $this->assertSame($adherents, GroupUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID, LoadAdherentData::ADHERENT_2_UUID],
            $adherents
        ));
        $this->assertSame($adherents, GroupUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID, LoadAdherentData::ADHERENT_2_UUID, LoadAdherentData::ADHERENT_3_UUID],
            $adherents
        ));
    }

    public function testRemoveUnknownAdherentsBadCallCollection()
    {
        $this->expectException(\BadMethodCallException::class);

        GroupUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            ['this is not a collection of adherents']
        );
    }

    public function testRemoveUnknownAdherentsBadCallIterable()
    {
        $this->expectException(\BadMethodCallException::class);

        GroupUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            'this is not an iterable value'
        );
    }

    public function testGetUuidsFromAdherents()
    {
        $adherents = $this->getAdherents();
        $uuids = GroupUtils::getUuidsFromAdherents($adherents);

        $this->assertCount(\count($adherents), $uuids);

        foreach ($uuids as $uuid) {
            $this->assertIsString($uuid);
            $this->assertTrue(Uuid::isValid($uuid));
        }
    }

    public function testGetUuidsFromAdherentsBadCallCollection()
    {
        $this->expectException(\BadMethodCallException::class);

        GroupUtils::getUuidsFromAdherents(['this is not a collection of adherents']);
    }

    public function testGetUuidsFromAdherentsBadCallIterable()
    {
        $this->expectException(\BadMethodCallException::class);

        GroupUtils::getUuidsFromAdherents('this is not an iterable value');
    }
}
