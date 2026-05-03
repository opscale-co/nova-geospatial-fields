<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Geospatial\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Proves every field renders its toolbar and initialises a live Leaflet
 * map on the Create page of the Place resource.
 */
final class CreateViewTest extends DuskTestCase
{
    #[Test]
    final public function every_field_renders_its_toolbar_and_map_container(): void
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
    final public function leaflet_actually_boots_inside_the_create_form(): void
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
}
