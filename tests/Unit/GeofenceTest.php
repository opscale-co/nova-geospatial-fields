<?php

declare(strict_types=1);

use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Fields\Geospatial\Geofence;

it('registers the geofence vue component', function (): void {
    expect(Geofence::make('geofence')->component)->toBe('nova-geospatial-geofence');
});

it('stores a closed polygon payload', function (): void {
    $field = Geofence::make('geofence');
    $model = new class
    {
        public ?string $geofence = null;
    };

    $ring = [[0.0, 0.0], [1.0, 0.0], [1.0, 1.0], [0.0, 1.0], [0.0, 0.0]];
    $payload = ['type' => 'Polygon', 'coordinates' => [$ring]];

    $request = NovaRequest::create('/', 'POST', ['geofence' => json_encode($payload)]);

    invokeFill($field, $request, 'geofence', $model, 'geofence');

    $decoded = json_decode($model->geofence, true);
    expect($decoded['type'])->toBe('Polygon')
        ->and($decoded['coordinates'][0])->toHaveCount(5)
        ->and($decoded['coordinates'][0][0])->toBe($decoded['coordinates'][0][4]);
});

it('exposes the configured minimum vertex count in field meta', function (): void {
    $field = Geofence::make('geofence')->minVertices(5);

    expect($field->meta()['minVertices'])->toBe(5);
});

it('clamps min vertices to 3', function (): void {
    $field = Geofence::make('geofence')->minVertices(2);

    expect($field->meta()['minVertices'])->toBe(3);
});
