<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageMeta extends Model
{
    public const KEY_PARTNER_SATELLITE = 'partner_satellite';
    public const KEY_PICKUP_DISTANCE = 'pickup_distance';

    protected $fillable = [
        'package_id',
        'key',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
