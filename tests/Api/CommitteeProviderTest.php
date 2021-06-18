<?php

namespace Tests\App\Api;

use App\Api\CommitteeProvider;
use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group api
 */
class CommitteeProviderTest extends TestCase
{
    public function testGetApprovedCommittees()
    {
        $committees[] = $committee1 = $this->createCommitteeMock(
            '2bb0472c-4189-409f-9d06-b617843230ea',
            'comite-paris',
            'Comité de Paris',
            48.8705073,
            2.3032432
        );

        $committees[] = $committee2 = $this->createCommitteeMock(
            'ae65d178-3dc6-4c14-843c-36df38c82834',
            'comite-berlin',
            'Comité de Berlin',
            52.5330939,
            13.4662418
        );

        // This one is not geocoded and will not included in the final results
        $committees[] = $this->createCommitteeMock(
            'b3d93750-d983-46bb-8f36-1a7ce39e74b5',
            'comite-rouen',
            'Comité de Rouen'
        );

        $repository = $this->createMock(CommitteeRepository::class);
        $repository->expects($this->once())->method('findApprovedCommittees')->willReturn($committees);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->at(0))->method('generate')->willReturn('/comites/'.$committee1->getSlug());
        $urlGenerator->expects($this->at(1))->method('generate')->willReturn('/comites/'.$committee2->getSlug());

        $provider = new CommitteeProvider($repository, $urlGenerator);

        $this->assertCount(2, $data = $provider->getApprovedCommittees());
        $this->assertSame(
            [
                [
                    'uuid' => '2bb0472c-4189-409f-9d06-b617843230ea',
                    'slug' => 'comite-paris',
                    'name' => 'Comité de Paris',
                    'url' => '/comites/comite-paris',
                    'position' => [
                        'lat' => 48.8705073,
                        'lng' => 2.3032432,
                    ],
                ],
                [
                    'uuid' => 'ae65d178-3dc6-4c14-843c-36df38c82834',
                    'slug' => 'comite-berlin',
                    'name' => 'Comité de Berlin',
                    'url' => '/comites/comite-berlin',
                    'position' => [
                        'lat' => 52.5330939,
                        'lng' => 13.4662418,
                    ],
                ],
            ],
            $data
        );
    }

    private function createCommitteeMock(
        string $uuid,
        string $slug,
        string $name,
        float $latitude = null,
        float $longitude = null
    ) {
        $committee = $this->createMock(Committee::class);
        $committee->expects($this->any())->method('isGeocoded')->willReturn($latitude && $longitude);
        $committee->expects($this->any())->method('getUuid')->willReturn(Uuid::fromString($uuid));
        $committee->expects($this->any())->method('getSlug')->willReturn($slug);
        $committee->expects($this->any())->method('getName')->willReturn($name);
        $committee->expects($this->any())->method('getLatitude')->willReturn($latitude);
        $committee->expects($this->any())->method('getLongitude')->willReturn($longitude);

        return $committee;
    }
}
