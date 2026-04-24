<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial;

/**
 * Coverage area defined by a center point and a radius in meters —
 * stored as a GeoJSON `Point` with the radius in `properties.radius`:
 *
 *   {"type":"Point","coordinates":[lng, lat],"properties":{"radius": 500}}
 *
 * The circle is drawn on the map with Leaflet's `L.circle` (radius is
 * in meters, not pixels, so the rendered size tracks the map zoom).
 */
final class Area extends GeospatialField
{
    public $component = 'nova-geospatial-area';

    /**
     * Default radius in meters, used when the stored value is empty.
     */
    public function defaultRadius(int $meters): self
    {
        $this->withMeta(['defaultRadius' => max(1, $meters)]);

        return $this;
    }

    /**
     * Clamp the radius the user can pick on the form.
     */
    public function radiusRange(int $min, int $max): self
    {
        $this->withMeta([
            'minRadius' => max(1, $min),
            'maxRadius' => max($min + 1, $max),
        ]);

        return $this;
    }
}
