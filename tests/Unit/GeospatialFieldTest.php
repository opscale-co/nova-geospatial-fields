<?php

declare(strict_types=1);

use Opscale\Fields\Geospatial\Location;

it('resolves a JSON string stored on the model into an array for the Vue component', function (): void {
    $field = Location::make('location');
    $model = new class
    {
        public ?string $location = '{"type":"Point","coordinates":[1.0,2.0]}';
    };

    $field->resolve($model, 'location');

    expect($field->value)->toBe(['type' => 'Point', 'coordinates' => [1.0, 2.0]]);
});

it('passes through arrays already cast by Eloquent', function (): void {
    $field = Location::make('location');
    $model = new class
    {
        public array $location = ['type' => 'Point', 'coordinates' => [3.0, 4.0]];
    };

    $field->resolve($model, 'location');

    expect($field->value)->toBe(['type' => 'Point', 'coordinates' => [3.0, 4.0]]);
});

it('merges package map defaults into field meta on serialize', function (): void {
    $field = Location::make('location');

    $meta = $field->jsonSerialize();

    expect($meta)->toHaveKey('tileLayer')
        ->and($meta)->toHaveKey('defaultCenter')
        ->and($meta)->toHaveKey('defaultZoom');
});

it('lets explicit setters override package defaults', function (): void {
    $field = Location::make('location')
        ->defaultCenter(10.0, 20.0)
        ->defaultZoom(18);

    $meta = $field->jsonSerialize();

    expect($meta['defaultCenter'])->toBe(['lat' => 10.0, 'lng' => 20.0])
        ->and($meta['defaultZoom'])->toBe(18);
});
