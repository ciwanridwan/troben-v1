<?php

namespace App\Models\Partners\Performances;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Partner Delivery Performance Model.
 *
 * @property int $partner_id
 * @property int $delivery_id
 * @property int $package_id
 * @property int $level
 * @property int $deadline
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PerformanceModel extends Pivot
{
    use HasFactory;

    public const STATUS_ON_PROCESS = 1;
    public const STATUS_REACHED = 5;
    public const STATUS_FAILED = 10;
}
