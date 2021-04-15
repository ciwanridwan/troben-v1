<?php

namespace App\Models\Deliveries;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Deliverable extends MorphPivot
{
    const STATUS_LOAD_BY_ORIGIN_WAREHOUSE = 'load_by_origin_warehouse';
    const STATUS_LOAD_BY_DRIVER = 'load_by_driver';
    const STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE = 'unload_by_destination_warehouse';

    protected $table = 'deliverables';

    public $timestamps = true;

    protected $hidden = [
        'delivery_id',
        'deliverable_id',
        'deliverable_type',
    ];

    protected $casts = [
        'is_onboard' => 'boolean',
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_LOAD_BY_ORIGIN_WAREHOUSE,
            self::STATUS_LOAD_BY_DRIVER,
            self::STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE,
        ];
    }
}
