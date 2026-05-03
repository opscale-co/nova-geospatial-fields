<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial;

/**
 * Single pin on a map — stores a GeoJSON `Point`:
 *
 *   {"type":"Point","coordinates":[lng, lat]}
 *
 * Detail view shows the pin on a read-only map. Form view lets the user
 * drag a marker or click once to place it.
 */
final class Location extends GeospatialField
{
    public $component = 'nova-geospatial-location';

    /**
     * @return array<string, mixed>
     */
    protected function mapDefaults(): array
    {
        return array_merge(parent::mapDefaults(), ['defaultZoom' => 17]);
    }
}
