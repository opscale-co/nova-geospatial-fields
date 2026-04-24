## Support us

At Opscale, we're passionate about contributing to the open-source community by providing solutions that help businesses scale efficiently. If you've found our tools helpful, here are a few ways you can show your support:

⭐ **Star this repository** to help others discover our work and be part of our growing community. Every star makes a difference!

💬 **Share your experience** by leaving a review on [Trustpilot](https://www.trustpilot.com/review/opscale.co) or sharing your thoughts on social media. Your feedback helps us improve and grow!

📧 **Send us feedback** on what we can improve at [feedback@opscale.co](mailto:feedback@opscale.co). We value your input to make our tools even better for everyone.

🙏 **Get involved** by actively contributing to our open-source repositories. Your participation benefits the entire community and helps push the boundaries of what's possible.

💼 **Hire us** if you need custom dashboards, admin panels, internal tools or MVPs tailored to your business. With our expertise, we can help you systematize operations or enhance your existing product. Contact us at hire@opscale.co to discuss your project needs.

Thanks for helping Opscale continue to scale! 🚀

## Description

Storing latitude/longitude pairs, street addresses, or delivery routes in a database is easy. Seeing them on a map inside Nova is not — until now. This package ships five purpose-built Nova fields backed by an interactive Leaflet map, so your admin panel shows the shape of your geodata, not just the numbers. Compatible with Nova 5.

| Field | Purpose | Storage |
|---|---|---|
| `Location` | Single pin — a lat/lng point picked from the map. | GeoJSON `Point` |
| `Address` | Text box that geocodes (via Nominatim or Photon) to a pin. | GeoJSON `Point` + formatted address |
| `Geofence` | Closed polygon — draw the perimeter of a zone. | GeoJSON `Polygon` |
| `Area` | Circle defined by a center point + radius (meters). | GeoJSON `Point` + radius |
| `Route` | Multi-waypoint polyline for paths or delivery routes. | GeoJSON `LineString` |

## Installation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opscale-co/nova-geospatial-fields.svg?style=flat-square)](https://packagist.org/packages/opscale-co/nova-geospatial-fields)

```bash
composer require opscale-co/nova-geospatial-fields
```

The package auto-registers its service provider.

Back each field with a `json` column (or `text`) — the payload is stored as GeoJSON so you can query it with your database's native geo support if present, or as plain JSON otherwise.

```php
Schema::create('places', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->json('location')->nullable();   // Location field
    $table->json('address')->nullable();    // Address field
    $table->json('geofence')->nullable();   // Geofence field
    $table->json('area')->nullable();       // Area field
    $table->json('route')->nullable();      // Route field
    $table->timestamps();
});
```

## Usage

```php
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\Fields\Geospatial\Address;
use Opscale\Fields\Geospatial\Area;
use Opscale\Fields\Geospatial\Geofence;
use Opscale\Fields\Geospatial\Location;
use Opscale\Fields\Geospatial\Route;

class Place extends Resource
{
    public static $model = \App\Models\Place::class;

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required', 'max:255'),

            Location::make('Location')
                ->defaultCenter(40.4168, -3.7038) // Madrid
                ->defaultZoom(13),

            Address::make('Address')
                ->geocoder('nominatim'), // or 'photon'

            Geofence::make('Geofence'),

            Area::make('Area')
                ->defaultRadius(500), // meters

            Route::make('Route'),
        ];
    }
}
```

### Field behavior

| View | Behavior |
|---|---|
| Create / Update | Interactive Leaflet map with drawing tools (marker / polygon / circle / polyline). Address shows a search input that geocodes on submit. |
| Detail | Read-only map showing the stored shape, centered and fit to the data. |
| Index | Compact text summary (e.g. `40.42, -3.70` for a point, `6 vertices` for a polygon). |
| API | `fillAttributeFromRequest` accepts raw GeoJSON strings as well as the internal JSON payload. |

### Configuration

```php
// config/nova-geospatial-fields.php (publishable)
return [
    'tile_layer' => [
        'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '© OpenStreetMap contributors',
    ],
    'geocoder' => [
        'driver' => env('NOVA_GEOSPATIAL_GEOCODER', 'nominatim'),
        'nominatim' => [
            'url' => 'https://nominatim.openstreetmap.org',
            'user_agent' => env('APP_NAME', 'Laravel').' / nova-geospatial-fields',
        ],
        'photon' => [
            'url' => 'https://photon.komoot.io',
        ],
    ],
    'routing' => [
        'osrm_url' => 'https://router.project-osrm.org/route/v1',
    ],
];
```

### Under the hood

| Layer | Dependency |
|---|---|
| Map | [Leaflet](https://leafletjs.com/) |
| Drawing | [Leaflet.draw](https://github.com/Leaflet/Leaflet.draw) |
| Routing | [Leaflet Routing Machine](https://www.liedman.net/leaflet-routing-machine/) + OSRM |
| Geocoding | [Nominatim](https://nominatim.org/) or [Photon](https://photon.komoot.io/) |
| Circle → GeoJSON | [@turf/circle](https://turfjs.org/docs/#circle) |

## Testing

```bash
npm run test           # Unit + feature
npm run test:web       # Dusk browser tests — verifies the map renders
```

## License

MIT
