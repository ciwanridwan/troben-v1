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

    protected $table = 'partner_delivery_performances';

    protected $fillable = [
        'partner_id',
        'delivery_id',
        'level',
        'deadline',
        'status'
    ];
}
