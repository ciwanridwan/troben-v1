<?php

namespace App\Models\Deliveries;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DeliveryPackagePivot extends Pivot
{
    public $timestamps = true;

    protected $table = 'delivery_package';

    protected $hidden = [
        'delivery_id',
        'package_id',
    ];

    protected $casts = [
        'is_onboard' => 'bool',
    ];
}
