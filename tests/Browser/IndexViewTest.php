<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Geospatial\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Smoke test — the index page must render the seeded rows without a
 * JS error, proving none of our custom fields crash in the table cell.
 */
final class IndexViewTest extends DuskTestCase
{
    #[Test]
    final public function places_index_lists_seeded_rows(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/places')
                ->waitForText('Madrid HQ', 15)
                ->assertSee('Madrid HQ')
                ->assertSee('Empty place');
        });
    }
}
