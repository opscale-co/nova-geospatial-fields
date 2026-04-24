<?php

declare(strict_types=1);

use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Fields\Geospatial\Route;

it('registers the route vue component', function (): void {
    expect(Route::make('route')->component)->toBe('nova-geospatial-route');
});

it('stores a LineString with waypoints in properties', function (): void {
    $field = Route::make('route');
    $model = new class
    {
        public ?string $route = null;
    };

    $payload = [
        'type' => 'LineString',
        'coordinates' => [[0.0, 0.0], [1.0, 1.0], [2.0, 2.0]],
        'properties' => [
            'waypoints' => [[0.0, 0.0], [2.0, 2.0]],
            'distance' => 1234.5,
            'duration' => 60.0,
        ],
    ];

    $request = NovaRequest::create('/', 'POST', ['route' => json_encode($payload)]);

    invokeFill($field, $request, 'route', $model, 'route');

    $decoded = json_decode($model->route, true);
    expect($decoded['type'])->toBe('LineString')
        ->and($decoded['coordinates'])->toHaveCount(3)
        ->and($decoded['properties']['waypoints'])->toHaveCount(2)
        ->and($decoded['properties']['distance'])->toBe(1234.5);
});

it('defaults the routing profile to driving', function (): void {
    $field = Route::make('route');

    $meta = $field->jsonSerialize();

    expect($meta)->toHaveKey('routingProfile')
        ->and($meta['routingProfile'])->toBe('driving');
});

it('allows overriding the routing profile', function (): void {
    $field = Route::make('route')->profile('cycling');

    expect($field->meta()['routingProfile'])->toBe('cycling');
});
