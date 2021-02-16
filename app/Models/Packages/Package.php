<?php

namespace App\Models\Packages;

use App\Models\Orders\Order;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Package model.
 *
 * @property int $int
 * @property int $order_id
 * @property string $barcode
 * @property string $service_code
 * @property string $receiver_phone
 * @property string $receiver_name
 * @property string $receiver_address
 * @property string $received_by
 * @property int $weight
 * @property int $height
 * @property int $length
 * @property int $width
 * @property float $total_amount
 * @property string $status
 * @property bool $is_separate_item
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Order $order
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Packages\Item[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Packages\Price[] $prices
 */
class Package extends Model
{
    use HasPhoneNumber, SoftDeletes;

    const STATUS_CREATED = 'created';
    const STATUS_PENDING = 'pending';
    const STATUS_WAITING_FOR_PICKUP = 'waiting_for_pickup';
    const STATUS_WAITING_FOR_APPROVAL = 'waiting_for_approval';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_WITH_COURIER = 'with_courier';
    const STATUS_DELIVERED = 'delivered';

    /**
     * Phone number column.
     *
     * @var string
     */
    protected $phoneNumberColumn = 'receiver_phone';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'packages';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'service_code',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'received_by',
        'weight',
        'height',
        'length',
        'width',
        'total_amount',
        'status',
        'is_separate_item',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'weight' => 'int',
        'height' => 'int',
        'length' => 'int',
        'width' => 'int',
        'total_amount' => 'float',
        'is_separate_item' => 'bool',
    ];

    /**
     * Get all available statuses.
     *
     * @return string[]
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_PENDING,
            self::STATUS_WAITING_FOR_PICKUP,
            self::STATUS_WAITING_FOR_APPROVAL,
            self::STATUS_ACCEPTED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_WITH_COURIER,
            self::STATUS_DELIVERED,
        ];
    }

    /**
     * Define `belongsTo` relationship with Order model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Define `hasMany` relationship with Item model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'package_id', 'id');
    }

    /**
     * Define `hasMany` relationship with Price model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'package_id', 'id');
    }
}
