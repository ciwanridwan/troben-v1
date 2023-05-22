<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageMeta extends Model
{
    public const KEY_PARTNER_SATELLITE = 'partner_satellite';

    protected $fillable = [
        'package_id',
        'key',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
