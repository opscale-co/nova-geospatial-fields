<?php

declare(strict_types=1);

use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Fields\Geospatial\Area;

it('registers the area vue component', function (): void {
    expect(Area::make('area')->component)->toBe('nova-geospatial-area');
});

it('stores a center point and a radius in properties', function (): void {
    $field = Area::make('area');
    $model = new class
    {
        public ?string $area = null;
    };

    $payload = [
        'type' => 'Point',
        'coordinates' => [-3.7038, 40.4168],
        'properties' => ['radius' => 750],
    ];

    $request = NovaRequest::create('/', 'POST', ['area' => json_encode($payload)]);

    invokeFill($field, $request, 'area', $model, 'area');

    $decoded = json_decode($model->area, true);
    expect($decoded['properties']['radius'])->toBe(750);
});

it('publishes defaultRadius to field meta', function (): void {
    $field = Area::make('area')->defaultRadius(1000);

    expect($field->meta()['defaultRadius'])->toBe(1000);
});

it('publishes a min/max radius range to field meta', function (): void {
    $field = Area::make('area')->radiusRange(50, 5000);

    expect($field->meta()['minRadius'])->toBe(50)
        ->and($field->meta()['maxRadius'])->toBe(5000);
});
