<?php

declare(strict_types=1);

use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Fields\Geospatial\Location;

it('registers the location vue component', function (): void {
    expect(Location::make('location')->component)->toBe('nova-geospatial-location');
});

it('accepts a raw GeoJSON Point string', function (): void {
    $field = Location::make('location');
    $model = new class
    {
        public ?string $location = null;
    };

    $request = NovaRequest::create('/', 'POST', [
        'location' => '{"type":"Point","coordinates":[-3.7038,40.4168]}',
    ]);

    invokeFill($field, $request, 'location', $model, 'location');

    $decoded = json_decode($model->location, true);
    expect($decoded['type'])->toBe('Point')
        ->and($decoded['coordinates'])->toBe([-3.7038, 40.4168]);
});

it('accepts an array payload', function (): void {
    $field = Location::make('location');
    $model = new class
    {
        public ?string $location = null;
    };

    $request = NovaRequest::create('/', 'POST', [
        'location' => ['type' => 'Point', 'coordinates' => [1, 2]],
    ]);

    invokeFill($field, $request, 'location', $model, 'location');

    expect(json_decode($model->location, true))
        ->toBe(['type' => 'Point', 'coordinates' => [1, 2]]);
});

it('writes null when the submitted value is null', function (): void {
    $field = Location::make('location');
    $model = new class
    {
        public ?string $location = 'previous';
    };

    $request = NovaRequest::create('/', 'POST', ['location' => null]);

    invokeFill($field, $request, 'location', $model, 'location');

    expect($model->location)->toBeNull();
});

it('leaves the attribute untouched when the request does not include it', function (): void {
    $field = Location::make('location');
    $model = new class
    {
        public ?string $location = 'keep-me';
    };

    $request = NovaRequest::create('/', 'POST', []);

    invokeFill($field, $request, 'location', $model, 'location');

    expect($model->location)->toBe('keep-me');
});

function invokeFill($field, NovaRequest $request, string $requestAttr, object $model, string $attr): void
{
    $method = new ReflectionMethod($field, 'fillAttributeFromRequest');
    $method->invoke($field, $request, $requestAttr, $model, $attr);
}
