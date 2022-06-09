<?php

namespace App\Models\Partners\Balance;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FailedHistory.
 *
 * @property int $id
 * @property int $partner_id
 * @property int $type
 * @property int $delivery_id
 * @property int $package_id
 * @property int $status
 * @property string $created_by
 * @property string $updated_by
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class FailedHistory extends Model
{
    use SoftDeletes, CustomSerializeDate, HasFactory, CanSearch;

    public const TYPE_TRANSIT = 1;
    public const TYPE_DOORING = 2;

    public const STATUS_WAITING = 1;
    public const STATUS_COMPLETED = 2;

    public const STATUS = [
        self::STATUS_WAITING,
        self::STATUS_COMPLETED,
    ];
    public const TYPE = [
        self::TYPE_TRANSIT,
        self::TYPE_DOORING,
    ];

    protected $table = 'partner_failed_balance_histories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'type',
        'delivery_id',
        'package_id',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleted_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
