<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

final class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-geospatial-fields');

        $this->publishes([
            __DIR__.'/../config/nova-geospatial-fields.php' => config_path('nova-geospatial-fields.php'),
        ], 'nova-geospatial-fields-config');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/nova-geospatial-fields'),
        ], 'nova-geospatial-fields-lang');

        Nova::serving(function (ServingNova $servingNova): void {
            Nova::script('nova-geospatial-fields', __DIR__.'/../dist/js/field.js');
            Nova::style('nova-geospatial-fields', __DIR__.'/../dist/css/field.css');

            $locale = app()->getLocale();
            $translations = __DIR__.'/../lang/'.$locale.'.json';
            if (is_file($translations)) {
                Nova::translations($translations);
            }
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/nova-geospatial-fields.php',
            'nova-geospatial-fields',
        );
    }
}
