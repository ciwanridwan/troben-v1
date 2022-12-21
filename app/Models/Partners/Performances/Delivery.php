<?php

namespace App\Models\Partners\Performances;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Partner Delivery Performance Model.
 *
 * @property int $partner_id
 * @property int $delivery_id
 * @property int $level
 * @property int $deadline
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Delivery extends PerformanceModel
{
    use HasFactory;

    public const TYPE_MB_WAREHOUSE_PACKING = 'mb_warehouse_packing';
    public const TYPE_MB_DRIVER_TO_TRANSIT = 'mb_driver_to_transit';
    public const TYPE_MTAK_OWNER_TO_DRIVER = 'mtak_owner_to_driver';
    public const TYPE_MTAK_DRIVER_TO_WAREHOUSE = 'mtak_driver_to_warehouse';
    public const TYPE_MPW_WAREHOUSE_GOOD_RECEIVE = 'mpw_warehouse_good_receive';
    public const TYPE_MPW_WAREHOUSE_REQUEST_TRANSPORTER = 'mpw_warehouse_request_transporter';
    public const TYPE_DRIVER_DOORING = 'driver_dooring';

    protected $table = 'partner_delivery_performances';

    protected $fillable = [
        'partner_id',
        'delivery_id',
        'level',
        'deadline',
        'status',
        'type'
    ];
}
