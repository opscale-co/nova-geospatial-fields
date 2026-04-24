<?php

declare(strict_types=1);

use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Fields\Geospatial\Address;

it('registers the address vue component', function (): void {
    expect(Address::make('address')->component)->toBe('nova-geospatial-address');
});

it('preserves the formatted address in GeoJSON properties', function (): void {
    $field = Address::make('address');
    $model = new class
    {
        public ?string $address = null;
    };

    $payload = [
        'type' => 'Point',
        'coordinates' => [-3.7038, 40.4168],
        'properties' => ['formatted' => 'Puerta del Sol, Madrid'],
    ];

    $request = NovaRequest::create('/', 'POST', ['address' => json_encode($payload)]);

    invokeFill($field, $request, 'address', $model, 'address');

    $decoded = json_decode($model->address, true);
    expect($decoded['properties']['formatted'])->toBe('Puerta del Sol, Madrid');
});

it('exposes the selected geocoder driver in field meta', function (): void {
    $field = Address::make('address')->geocoder('photon');

    expect($field->meta())
        ->toHaveKey('geocoder')
        ->and($field->meta()['geocoder']['driver'])->toBe('photon');
});
