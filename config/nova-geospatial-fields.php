<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Tile layer
    |--------------------------------------------------------------------------
    |
    | Leaflet tile layer URL template and attribution, used by every field.
    | Defaults to the public OpenStreetMap raster tiles.
    */
    'tile_layer' => [
        'url' => env(
            'NOVA_GEOSPATIAL_TILE_URL',
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        ),
        'attribution' => env(
            'NOVA_GEOSPATIAL_TILE_ATTRIBUTION',
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        ),
        'max_zoom' => 19,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default map view
    |--------------------------------------------------------------------------
    |
    | Used by fields when the stored value is empty and no explicit default
    | has been configured on the field itself.
    */
    'default_center' => [
        'lat' => (float) env('NOVA_GEOSPATIAL_DEFAULT_LAT', 40.4168),
        'lng' => (float) env('NOVA_GEOSPATIAL_DEFAULT_LNG', -3.7038),
    ],
    'default_zoom' => (int) env('NOVA_GEOSPATIAL_DEFAULT_ZOOM', 13),

    /*
    |--------------------------------------------------------------------------
    | Geocoder
    |--------------------------------------------------------------------------
    |
    | Driver used by the Address field to translate a free-text address into
    | coordinates. Both Nominatim and Photon are public, CORS-friendly
    | OpenStreetMap-based services.
    */
    'geocoder' => [
        'driver' => env('NOVA_GEOSPATIAL_GEOCODER', 'nominatim'),
        'nominatim' => [
            'url' => env('NOVA_GEOSPATIAL_NOMINATIM_URL', 'https://nominatim.openstreetmap.org'),
            'user_agent' => env('APP_NAME', 'Laravel').' / nova-geospatial-fields',
        ],
        'photon' => [
            'url' => env('NOVA_GEOSPATIAL_PHOTON_URL', 'https://photon.komoot.io'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | OSRM-compatible routing endpoint used by the Route field to snap a
    | multi-waypoint path to the road network.
    */
    'routing' => [
        'osrm_url' => env(
            'NOVA_GEOSPATIAL_OSRM_URL',
            'https://router.project-osrm.org/route/v1',
        ),
    ],
];
