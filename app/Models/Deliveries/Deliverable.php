<?php

namespace App\Models\Deliveries;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations;

class Deliverable extends MorphPivot
{
    public const STATUS_PREPARED_BY_ORIGIN_WAREHOUSE = 'prepared_by_origin_warehouse';
    public const STATUS_LOAD_BY_DRIVER = 'load_by_driver';
    public const STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE = 'unload_by_destination_warehouse';

    protected $table = 'deliverables';

    protected $hidden = [
        'delivery_id',
        'deliverable_id',
        'deliverable_type',
    ];

    protected $casts = [
        'is_onboard' => 'boolean',
    ];

    public function delivery(): Relations\BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id');
    }

    public function deliverable(): Relations\MorphTo
    {
        return $this->morphTo();
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
            self::STATUS_LOAD_BY_DRIVER,
            self::STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE,
        ];
    }

    public static function isShouldOnBoard(string $status): bool
    {
        return $status === self::STATUS_LOAD_BY_DRIVER;
    }
}
