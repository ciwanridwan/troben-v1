<?php

namespace App\Models\Packages;

use App\Models\Geo\Regency;
use App\Models\Geo\District;
use App\Models\Geo\SubDistrict;
use App\Models\Payments\Payment;
use App\Models\Customers\Customer;
use App\Concerns\Models\HasBarcode;
use App\Models\Deliveries\Delivery;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Deliveries\DeliveryPackagePivot;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Package model.
 *
 * @property int $id
 * @property int $customer_id
 * @property string $barcode
 * @property string $service_code
 * @property string $transporter_type
 * @property string $sender_name
 * @property string $sender_phone
 * @property string $sender_address
 * @property string $receiver_phone
 * @property string $receiver_name
 * @property string $receiver_address
 * @property string $status
 * @property bool $is_separate_item
 * @property float $total_amount
 * @property string $payment_status
 * @property int $origin_regency_id
 * @property int $origin_district_id
 * @property int $origin_sub_district_id
 * @property int $destination_regency_id
 * @property int $destination_district_id
 * @property int $destination_sub_district_id
 * @property string|null $received_by
 * @property \Carbon\Carbon|null $received_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Customers\Customer|null $customer
 * @property-read \App\Models\Geo\Regency|null $origin_regency
 * @property-read \App\Models\Geo\District|null $origin_district
 * @property-read \App\Models\Geo\SubDistrict|null $origin_sub_district
 * @property-read \App\Models\Geo\Regency|null $destination_regency
 * @property-read \App\Models\Geo\District|null $destination_district
 * @property-read \App\Models\Geo\SubDistrict|null $destination_sub_district
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payments\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Packages\Item[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Packages\Price[] $prices
 * @property-read \Illuminate\Database\Eloquent\Collection $deliveries
 * @property-read null|DeliveryPackagePivot pivot
 */
class Package extends Model
{
    use HasPhoneNumber, SoftDeletes, HashableId, HasBarcode, HasFactory;

    public const STATUS_CANCEL = 'cancel';
    public const STATUS_CREATED = 'created';
    public const STATUS_PENDING = 'pending';
    public const STATUS_WAITING_FOR_PICKUP = 'waiting_for_pickup';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_ESTIMATING = 'estimating';
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
        'customer_id',
        'service_code',
        'transporter_type',
        'sender_name',
        'sender_phone',
        'sender_address',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'is_separate_item',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_regency_id',
        'destination_district_id',
        'destination_sub_district_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'customer_id',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_regency_id',
        'destination_district_id',
        'destination_sub_district_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'total_amount' => 'float',
        'is_separate_item' => 'bool',
        'received_at' => 'datetime',
    ];

    /**
     * Get all available statuses.
     *
     * @return string[]
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_CANCEL,
            self::STATUS_CREATED,
            self::STATUS_PENDING,
            self::STATUS_WAITING_FOR_PICKUP,
            self::STATUS_PICKED_UP,
            self::STATUS_ESTIMATING,
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

    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany(Delivery::class)
            ->withPivot(['is_onboard', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->orderByPivot('created_at')
            ->using(DeliveryPackagePivot::class);
    }

    /**
     * Define `belongsTo` relationship with Regency model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function origin_regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'origin_regency_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function origin_district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'origin_district_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Sub District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function origin_sub_district(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'origin_sub_district_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Regency model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destination_regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'destination_regency_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destination_district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'destination_district_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Sub District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destination_sub_district(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'destination_sub_district_id', 'id');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopeFailed($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_FAILED);
    }
}
