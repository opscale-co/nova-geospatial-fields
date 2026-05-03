<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Tests;

use Laravel\Dusk\Browser;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Override;

abstract class DuskTestCase extends BaseTestCase
{
    use WithWorkbench;

    protected static $baseServePort = 8099;

    /**
     * Close any Dusk browser instance after each test so the next test
     * starts from a clean slate. Without this, Chrome accumulates DOM
     * and Leaflet state across our test classes, which occasionally
     * wedges the session when the next test starts.
     */
    #[Override]
    protected function tearDown(): void
    {
        static::closeAll();

        parent::tearDown();
    }

    final protected function loginToNova(Browser $browser): Browser
    {
        $browser->visit('/nova');

        if ($browser->element('input[name="email"]')) {
            $browser->type('email', 'admin@laravel.com')
                ->type('password', 'password')
                ->press('Log In')
                ->waitUntil('!window.location.pathname.includes("/login")', 10);
        }

        return $browser;
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('app.key', 'base64:/k4sM0Ba8ApH16NSjhKzYO8yAR1lOPncRPFr853v6+4=');
    }
}
