<?php

namespace App\Models\Partners\Performances;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Partner Package Performance Model.
 *
 * @property int $partner_id
 * @property int $package_id
 * @property int $level
 * @property int $deadline
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Package extends PerformanceModel
{
    use HasFactory;

    protected $table = 'partner_package_performances';

    protected $fillable = [
        'partner_id',
        'package_id',
        'level',
        'deadline',
        'status'
    ];
}
