<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Geospatial\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Verifies every field renders its stored shape on the Detail page of
 * the seeded `Place #1` ("Madrid HQ"). Loads the page once and checks
 * all five fields in sequence — mounting five Leaflet maps across five
 * separate test methods turned out to compound Chrome state and trigger
 * flaky session resets.
 */
final class DetailViewTest extends DuskTestCase
{
    #[Test]
    final public function every_field_renders_its_geometry_and_summary(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1')
                ->waitFor('[data-testid="geo-location-detail"]', 15)
                ->waitFor('[data-testid="geo-address-detail"]', 10)
                ->waitFor('[data-testid="geo-geofence-detail"]', 10)
                ->waitFor('[data-testid="geo-area-detail"]', 10)
                ->waitFor('[data-testid="geo-route-detail"]', 10)

                // Location — pin on the map
                ->waitFor('[data-testid="geo-location-map"] .leaflet-tile-pane', 10)
                ->assertPresent('[data-testid="geo-location-map"] .leaflet-marker-pane img')

                // Address — formatted label + map
                ->waitFor('[data-testid="geo-address-map"] .leaflet-tile-pane', 10)
                ->assertSeeIn('[data-testid="geo-address-label"]', 'Puerta del Sol')

                // Geofence — svg polygon overlay
                ->waitFor('[data-testid="geo-geofence-map"] svg path', 10)
                ->assertPresent('[data-testid="geo-geofence-map"] .leaflet-overlay-pane svg')

                // Area — svg circle + radius summary
                ->waitFor('[data-testid="geo-area-map"] svg path', 10)
                ->assertSeeIn('[data-testid="geo-area-summary"]', '750 m')

                // Route — svg polyline + distance summary
                ->waitFor('[data-testid="geo-route-map"] svg path', 10)
                ->assertSeeIn('[data-testid="geo-route-summary"]', 'km')

                ->screenshot('detail-all-fields-rendered');
        });
    }
}
