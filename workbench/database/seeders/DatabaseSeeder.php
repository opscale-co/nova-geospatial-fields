<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\Place;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@laravel.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ],
        );

        Place::firstOrCreate(
            ['name' => 'Madrid HQ'],
            [
                'location' => [
                    'type' => 'Point',
                    'coordinates' => [-3.7038, 40.4168],
                ],
                'address' => [
                    'type' => 'Point',
                    'coordinates' => [-3.7038, 40.4168],
                    'properties' => ['formatted' => 'Puerta del Sol, Madrid, Spain'],
                ],
                'geofence' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-3.7070, 40.4150],
                        [-3.7000, 40.4150],
                        [-3.7000, 40.4200],
                        [-3.7070, 40.4200],
                        [-3.7070, 40.4150],
                    ]],
                ],
                'area' => [
                    'type' => 'Point',
                    'coordinates' => [-3.7038, 40.4168],
                    'properties' => ['radius' => 750],
                ],
                'route' => [
                    'type' => 'LineString',
                    'coordinates' => [
                        [-3.7038, 40.4168],
                        [-3.7000, 40.4180],
                        [-3.6950, 40.4200],
                    ],
                    'properties' => [
                        'waypoints' => [
                            [-3.7038, 40.4168],
                            [-3.6950, 40.4200],
                        ],
                        'distance' => 1820.5,
                        'duration' => 300.0,
                    ],
                ],
            ],
        );

        Place::firstOrCreate(['name' => 'Empty place']);
    }
}
