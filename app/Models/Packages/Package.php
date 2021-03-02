<?php

namespace App\Models\Packages;

use App\Models\Payments\Payment;
use App\Models\Customers\Customer;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Package model.
 *
 * @property int $int
 * @property int $customer_id
 * @property string $barcode
 * @property string $service_code
 * @property string $receiver_phone
 * @property string $receiver_name
 * @property string $receiver_address
 * @property string $received_by
 * @property float $total_amount
 * @property string $status
 * @property bool $is_separate_item
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Customers\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payments\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Packages\Item[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Packages\Price[] $prices
 */
class Package extends Model
{
    use HasPhoneNumber, SoftDeletes;

    public const STATUS_CREATED = 'created';
    public const STATUS_PENDING = 'pending';
    public const STATUS_WAITING_FOR_PICKUP = 'waiting_for_pickup';
    public const STATUS_WAITING_FOR_APPROVAL = 'waiting_for_approval';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_WITH_COURIER = 'with_courier';
    public const STATUS_DELIVERED = 'delivered';

    public const PAYMENT_STATUS_DRAFT = 'draft';
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';

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
        'customer_id',
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
        'payment_status',
        'is_separate_item',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
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
     * @return string[]
     */
    public static function getAvailablePaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_DRAFT, // payment not yet created
            self::PAYMENT_STATUS_PENDING, // payment is created, waiting for payment
            self::PAYMENT_STATUS_PAID, // payment is done.
            self::PAYMENT_STATUS_FAILED, // payment is failed
        ];
    }

    /**
     * Define `morphMany` relationship with Payment model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable', 'payable_type', 'payable_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Customer model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
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
