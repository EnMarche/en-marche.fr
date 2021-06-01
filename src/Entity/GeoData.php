<?php

namespace App\Entity;

use CrEOF\Spatial\PHP\Types\Geometry\GeometryInterface;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="geo_data",
 *     indexes={
 *         @ORM\Index(name="geo_data_geo_shape_idx", columns={"geo_shape"}, flags={"spatial"})
 *     }
 * )
 */
class GeoData
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var GeometryInterface
     *
     * @ORM\Column(type="geometry")
     */
    private $geoShape;

    /**
     * @throws \InvalidArgumentException if $geoShape is invalid
     */
    public function __construct(GeometryInterface $geoShape)
    {
        if (!$geoShape instanceof Polygon && !$geoShape instanceof MultiPolygon) {
            throw new \InvalidArgumentException(sprintf('$geoShape must be an instance of %s or %s, %s given', Polygon::class, MultiPolygon::class, \get_class($geoShape)));
        }

        $this->geoShape = $geoShape;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGeoShape(): GeometryInterface
    {
        return $this->geoShape;
    }

    public function setGeoShape(GeometryInterface $geoShape): void
    {
        $this->geoShape = $geoShape;
    }
}
