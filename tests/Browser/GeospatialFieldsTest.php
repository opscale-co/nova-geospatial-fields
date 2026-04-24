<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Geospatial\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Browser tests exercise the real Vue + Leaflet pipeline against the
 * workbench Nova app. The seeded `Place #1` ("Madrid HQ") has a value
 * for every geospatial field, so Detail views should render a map;
 * `Place #2` ("Empty place") is left null to prove the empty-state.
 */
final class GeospatialFieldsTest extends DuskTestCase
{
    #[Test]
    final public function create_view_renders_every_field_toolbar_and_map(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/new')
                ->waitForText('Create Place', 15)
                ->waitFor('[data-testid="geo-location-form"]', 10)
                ->waitFor('[data-testid="geo-address-form"]', 10)
                ->waitFor('[data-testid="geo-geofence-form"]', 10)
                ->waitFor('[data-testid="geo-area-form"]', 10)
                ->waitFor('[data-testid="geo-route-form"]', 10)
                ->assertPresent('[data-testid="geo-location-map"]')
                ->assertPresent('[data-testid="geo-address-map"]')
                ->assertPresent('[data-testid="geo-geofence-map"]')
                ->assertPresent('[data-testid="geo-area-map"]')
                ->assertPresent('[data-testid="geo-route-map"]')
                ->assertPresent('[data-testid="geo-address-search"]')
                ->assertPresent('[data-testid="geo-geofence-draw"]')
                ->assertPresent('[data-testid="geo-area-radius"]')
                ->assertPresent('[data-testid="geo-route-snap"]');
        });
    }

    #[Test]
    final public function create_view_actually_initialises_leaflet(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/new')
                ->waitForText('Create Place', 15)
                ->waitFor('[data-testid="geo-location-form"] .leaflet-container', 10)
                ->assertPresent('[data-testid="geo-location-form"] .leaflet-tile-pane')
                ->screenshot('form-leaflet-initialised');
        });
    }

    #[Test]
    final public function detail_view_renders_the_location_pin(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1')
                ->waitFor('[data-testid="geo-location-detail"]', 15)
                ->waitFor('[data-testid="geo-location-map"] .leaflet-container', 10)
                ->assertPresent('[data-testid="geo-location-map"] .leaflet-marker-pane img');
        });
    }

    #[Test]
    final public function detail_view_renders_the_address_formatted_label(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1')
                ->waitFor('[data-testid="geo-address-detail"]', 15)
                ->assertSeeIn('[data-testid="geo-address-label"]', 'Puerta del Sol');
        });
    }

    #[Test]
    final public function detail_view_renders_the_geofence_polygon(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1')
                ->waitFor('[data-testid="geo-geofence-detail"]', 15)
                ->waitFor('[data-testid="geo-geofence-map"] svg path', 10)
                ->assertPresent('[data-testid="geo-geofence-map"] .leaflet-overlay-pane svg');
        });
    }

    #[Test]
    final public function detail_view_renders_the_area_circle_and_summary(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1')
                ->waitFor('[data-testid="geo-area-detail"]', 15)
                ->waitFor('[data-testid="geo-area-map"] svg path', 10)
                ->assertSeeIn('[data-testid="geo-area-summary"]', '750 m');
        });
    }

    #[Test]
    final public function detail_view_renders_the_route_polyline_and_distance(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1')
                ->waitFor('[data-testid="geo-route-detail"]', 15)
                ->waitFor('[data-testid="geo-route-map"] svg path', 10)
                ->assertSeeIn('[data-testid="geo-route-summary"]', 'km');
        });
    }

    #[Test]
    final public function detail_view_shows_empty_state_when_fields_are_null(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/2')
                ->waitFor('[data-testid="geo-location-detail"]', 15)
                ->assertSeeIn('[data-testid="geo-location-empty"]', 'No location set')
                ->assertSeeIn('[data-testid="geo-address-empty"]', 'No address set')
                ->assertSeeIn('[data-testid="geo-geofence-empty"]', 'No geofence set')
                ->assertSeeIn('[data-testid="geo-area-empty"]', 'No area set')
                ->assertSeeIn('[data-testid="geo-route-empty"]', 'No route set');
        });
    }

    #[Test]
    final public function update_view_shows_every_form_map_preloaded_with_data(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1/edit')
                ->waitForText('Update Place', 15)
                ->waitFor('[data-testid="geo-location-map"] .leaflet-container', 10)
                ->waitFor('[data-testid="geo-area-map"] .leaflet-container', 10)
                ->assertSeeIn('[data-testid="geo-location-summary"]', '40.41680');
        });
    }

    #[Test]
    final public function index_view_shows_compact_summaries(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places')
                ->waitForText('Places', 15)
                ->assertPresent('[data-testid="geo-location-index"]')
                ->assertPresent('[data-testid="geo-area-index"]');
        });
    }
}
