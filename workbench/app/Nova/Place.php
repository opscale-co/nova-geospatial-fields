<?php

declare(strict_types=1);

namespace Workbench\App\Nova;

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
    public static $model = \Workbench\App\Models\Place::class;

    public static $title = 'name';

    public static $search = ['name'];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required', 'max:255'),

            Location::make('Location')
                ->defaultCenter(40.4168, -3.7038),

            Address::make('Address')
                ->geocoder('nominatim')
                ->defaultCenter(40.4168, -3.7038),

            Geofence::make('Geofence')
                ->defaultCenter(40.4168, -3.7038)
                ->minVertices(3),

            Area::make('Area')
                ->defaultCenter(40.4168, -3.7038)
                ->defaultRadius(500)
                ->radiusRange(50, 50000),

            Route::make('Route')
                ->defaultCenter(40.4168, -3.7038)
                ->profile('driving'),
        ];
    }
}
