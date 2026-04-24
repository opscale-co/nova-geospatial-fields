<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $table = 'places';

    protected $guarded = [];

    protected $casts = [
        'location' => 'array',
        'address' => 'array',
        'geofence' => 'array',
        'area' => 'array',
        'route' => 'array',
    ];
}
