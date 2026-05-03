<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Geospatial\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Proves every field shows its empty-state placeholder when the
 * backing attribute is `null` (seeded as `Place #2` / "Empty place").
 */
final class EmptyStateTest extends DuskTestCase
{
    #[Test]
    final public function each_field_shows_its_empty_state_when_value_is_null(): void
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
}
