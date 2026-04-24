<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial;

/**
 * Multi-waypoint path — stored as a GeoJSON `LineString` with the
 * ordered waypoints preserved in `properties.waypoints` so the form
 * can reconstruct the path without re-querying OSRM:
 *
 *   {
 *     "type":"LineString",
 *     "coordinates":[[lng,lat], ...],
 *     "properties":{
 *       "waypoints":[[lng,lat], ...],
 *       "distance": 12345.6,
 *       "duration": 987.6
 *     }
 *   }
 *
 * The form fetches an OSRM-compatible routing endpoint to snap
 * waypoints to the road network; the detail view renders the
 * already-computed coordinates as a polyline.
 */
final class Route extends GeospatialField
{
    public $component = 'nova-geospatial-route';

    /**
     * Switch between routing profiles supported by the OSRM endpoint.
     * Common values: 'driving', 'cycling', 'walking'.
     */
    public function profile(string $profile): self
    {
        $this->withMeta(['routingProfile' => $profile]);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $config = config('nova-geospatial-fields.routing');

        $this->withMeta([
            'osrmUrl' => $this->meta['osrmUrl'] ?? ($config['osrm_url'] ?? ''),
            'routingProfile' => $this->meta['routingProfile'] ?? 'driving',
        ]);

        return parent::jsonSerialize();
    }
}
