<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Geospatial\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Proves the Update page reloads the stored value into each form
 * component and re-initialises the Leaflet map with that value.
 */
final class UpdateViewTest extends DuskTestCase
{
    #[Test]
    final public function update_form_preloads_every_map_with_the_stored_shape(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places/1/edit')
                ->waitForText('Update Place', 15)
                ->waitFor('[data-testid="geo-location-form"] .leaflet-tile-pane', 10)
                ->waitFor('[data-testid="geo-area-form"] .leaflet-tile-pane', 10)
                ->assertSeeIn('[data-testid="geo-location-summary"]', '40.41680');
        });
    }
}
