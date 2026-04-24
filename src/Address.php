<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial;

/**
 * Free-text address geocoded into a GeoJSON `Point` with the formatted
 * address preserved as a `properties.formatted` key:
 *
 *   {"type":"Point","coordinates":[lng, lat],"properties":{"formatted":"..."}}
 *
 * Geocoding happens client-side via either Nominatim (default) or Photon —
 * both are public, CORS-friendly OpenStreetMap-based services.
 */
final class Address extends GeospatialField
{
    public $component = 'nova-geospatial-address';

    /**
     * Pick the geocoding driver used by the Vue component.
     *
     * Supported drivers: 'nominatim' (default), 'photon'.
     */
    public function geocoder(string $driver): self
    {
        $this->withMeta(['geocoder' => ['driver' => $driver]]);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $config = config('nova-geospatial-fields.geocoder');

        $this->withMeta([
            'geocoder' => array_merge(
                ['driver' => $config['driver'] ?? 'nominatim'],
                $this->meta['geocoder'] ?? [],
            ),
            'geocoderEndpoints' => [
                'nominatim' => $config['nominatim']['url'] ?? 'https://nominatim.openstreetmap.org',
                'photon' => $config['photon']['url'] ?? 'https://photon.komoot.io',
            ],
        ]);

        return parent::jsonSerialize();
    }
}
