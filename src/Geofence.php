<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial;

/**
 * Closed polygon describing a zone or perimeter — stored as a GeoJSON
 * `Polygon`:
 *
 *   {"type":"Polygon","coordinates":[[[lng, lat], ...]]}
 *
 * Form view exposes Leaflet.draw's polygon tool; the first and last
 * coordinate are equal per the GeoJSON spec.
 */
final class Geofence extends GeospatialField
{
    public $component = 'nova-geospatial-geofence';

    /**
     * Minimum number of vertices the Vue form will accept before submit.
     */
    public function minVertices(int $count): self
    {
        $this->withMeta(['minVertices' => max(3, $count)]);

        return $this;
    }
}
