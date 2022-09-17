<?php

namespace App\Models\Packages;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use App\Models\Code;
use App\Models\Offices\Office;
use App\Models\Partners\Balance\History;
use App\Models\Partners\ClaimedVoucher;
use App\Models\Partners\Partner;
use App\Models\Partners\Performances\PerformanceModel;
use App\Models\Promos\ClaimedPromotion;
use App\Models\Partners\Transporter;
use App\Models\User;
use App\Models\Geo\Regency;
use App\Models\Geo\District;
use App\Models\Geo\SubDistrict;
use App\Concerns\Models\HasCode;
use App\Models\Payments\Payment;
use App\Models\Customers\Customer;
use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\Deliverable;
use App\Concerns\Models\HasPhoneNumber;
use App\Models\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Jalameta\Attachments\Concerns\Attachable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jalameta\Attachments\Contracts\AttachableContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use ReflectionClass;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Package model.
 *
 * @property int $id
 * @property int $customer_id
 * @property string $service_code
 * @property string $transporter_type
 * @property string $sender_name
 * @property string $sender_phone
 * @property string $sender_address
 * @property string $sender_way_point
 * @property string $sender_latitude
 * @property string $sender_longitude
 *
 * @property string $receiver_phone
 * @property string $receiver_name
 * @property string $receiver_address
 * @property string $receiver_way_point
 * @property string $receiver_latitude
 * @property string $receiver_longitude
 *
 * @property string $status
 * @property bool $is_separate_item
 * @property float $total_amount
 * @property float $total_weight
 * @property float $tier_price
 * @property string $payment_status
 * @property int $origin_regency_id
 * @property int $origin_district_id
 * @property int $origin_sub_district_id
 * @property int $destination_regency_id
 * @property int $destination_district_id
 * @property int $destination_sub_district_id
 * @property string|null $received_by
 * @property \Carbon\Carbon|null $received_at
 * @property array $handling
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property int $created_by
 * @property int $updated_by
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
 * @property-read \Illuminate\Database\Eloquent\Collection item_codes
 * @property-read null|Deliverable pivot
 * @property-read User|null packager
 * @property-read User|null estimator
 * @property-read \App\Models\Partners\Performances\Package|null $partner_performance
 * @property int estimator_id
 * @property int packager_id
 * @property Code code
 * @property-read \App\Models\Packages\MotorBike|null $moto_bikes
 */
class Package extends Model implements AttachableContract
{
    use HasPhoneNumber, SoftDeletes, HashableId, HasCode, HasFactory, Attachable, CanSearch, CustomSerializeDate;

    public const PACKAGE_SYSTEM_ID = 0;

    public const STATUS_CANCEL = 'cancel';
    public const STATUS_LOST = 'lost';
    public const STATUS_CREATED = 'created';
    public const STATUS_PENDING = 'pending';
    public const STATUS_WAITING_FOR_PICKUP = 'waiting_for_pickup';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_WAITING_FOR_ESTIMATING = 'waiting_for_estimating';
    public const STATUS_ESTIMATING = 'estimating';
    public const STATUS_ESTIMATED = 'estimated';
    public const STATUS_WAITING_FOR_APPROVAL = 'waiting_for_approval';
    public const STATUS_REVAMP = 'revamp';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_WAITING_FOR_PACKING = 'waiting_for_packing';
    public const STATUS_PACKING = 'packing';
    public const STATUS_PACKED = 'packed';
    public const STATUS_MANIFESTED = 'manifested';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_WITH_COURIER = 'with_courier';
    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCEL_SELF_PICKUP = 'cancel_self_pickup';
    public const STATUS_CANCEL_DELIVERED = 'cancel_delivered';

    public const PAYMENT_STATUS_DRAFT = 'draft';
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';

    public const ATTACHMENT_RECEIPT = 'receipt';
    public const ATTACHMENT_PACKAGE = 'package';
    public const ATTACHMENT_RECEIVED = 'received';

    public const TYPE_WALKIN = 'walkin';
    public const TYPE_APP = 'app';

    /**Define type for different bike or pack */
    public const TYPE_BIKE = 'bike';
    public const TYPE_ITEM = 'item';

    /**
     * Phone number column.
     *
     * @var string
     */
    protected $phoneNumberColumn = 'receiver_phone';

    /**
     * @var string
     */
    protected $codeType = 'RCP';

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
        'sender_way_point',
        'sender_latitude',
        'sender_longitude',
        'status',
        'payment_status',

        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'receiver_way_point',
        'receiver_latitude',
        'receiver_longitude',

        'handling',
        'estimator_id',
        'packager_id',
        'is_separate_item',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_regency_id',
        'destination_district_id',
        'destination_sub_district_id',
        'received_by',
        'received_at',
    ];

    protected $search_columns = [
        'sender_name',
        'sender_phone',
        'sender_address',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'handling',
        'created_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'customer_id',
        'estimator_id',
        'packager_id',
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
        'service_price',
        'discount_service_price',
        'type',
        'order_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'total_weight' => 'int',
        'total_amount' => 'float',
        'tier_price' => 'float',
        'is_separate_item' => 'boolean',
        'received_at' => 'datetime',
        'handling' => 'array',
    ];

    /**
     * Get error codes.
     *
     * @return string[]
     */
    public static function getStatusConst(): array
    {
        $class = new ReflectionClass(__CLASS__);
        return array_filter($class->getConstants(), fn ($key) => str_starts_with($key, 'STATUS'), ARRAY_FILTER_USE_KEY);
    }

    public static function getAvailableCancelPickupMethod()
    {
        return [
            self::STATUS_CANCEL_SELF_PICKUP,
            self::STATUS_CANCEL_DELIVERED
        ];
    }

    /**
     * Get error codes.
     *
     * @return string[]
     */
    public static function getPaymentStatusConst(): array
    {
        $class = new ReflectionClass(__CLASS__);
        return array_filter($class->getConstants(), fn ($key) => str_starts_with($key, 'PAYMENT_STATUS'), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get all available statuses.
     *
     * @return string[]
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_CANCEL,
            self::STATUS_LOST,
            self::STATUS_CREATED,
            self::STATUS_PENDING,
            self::STATUS_WAITING_FOR_PICKUP,
            self::STATUS_PICKED_UP,
            self::STATUS_WAITING_FOR_ESTIMATING,
            self::STATUS_ESTIMATING,
            self::STATUS_ESTIMATED,
            self::STATUS_WAITING_FOR_APPROVAL,
            self::STATUS_REVAMP,
            self::STATUS_ACCEPTED,
            self::STATUS_WAITING_FOR_PACKING,
            self::STATUS_PACKING,
            self::STATUS_PACKED,
            self::STATUS_MANIFESTED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_WITH_COURIER,
            self::STATUS_DELIVERED,
            self::STATUS_CANCEL_SELF_PICKUP,
            self::STATUS_CANCEL_DELIVERED
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

    public function getServicePriceAttribute()
    {
        $discount = $this->prices()->where('type', Price::TYPE_DISCOUNT)
            ->where('description', Price::TYPE_SERVICE)
            ->first();
        $discount = ($discount == null) ? 0 : $discount['amount'];

        // return $this->prices()->where('type', Price::TYPE_SERVICE)->first()->amount - $discount;
        $amount = $this->prices()->where('type', Price::TYPE_SERVICE)->first()->amount ?? 0;
        if ($amount == null) {
            return 0;
        } else {
            // return $amount - $discount;
            return $amount;
        }
    }

    public function getDiscountServicePriceAttribute()
    {
        try {
            $discount = $this->prices()->where('type', Price::TYPE_DISCOUNT)
                ->where('description', Price::TYPE_SERVICE)
                ->first()->amount;
            if ($discount == null) {
                $discount_service_price = 0;
            } else {
                $discount_service_price = $discount;
            }
            return $discount_service_price;
        } catch (\Throwable $th) {
            return 0;
        }
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

    public function item_codes(): HasManyThrough
    {
        return $this
            ->hasManyThrough(
                Code::class,
                Item::class,
                'package_id',
                'codeable_id',
                'id',
                'id'
            )
            ->where('codes.codeable_type', Item::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(History::class, 'package_id', 'id');
    }

    public function historyPool(): HasMany
    {
        return $this->hasMany(History::class, 'package_id', 'id')->whereRelation('partner', 'type', Partner::TYPE_POOL);
    }

    public function historyBusiness(): HasMany
    {
        return $this->hasMany(History::class, 'package_id', 'id')->whereRelation('partner', 'type', Partner::TYPE_BUSINESS);
    }

    public function historyTransporter(): HasMany
    {
        return $this->hasMany(History::class, 'package_id', 'id')->whereRelation('partner', 'type', Partner::TYPE_TRANSPORTER);
    }

    public function historySpace(): HasMany
    {
        return $this->hasMany(History::class, 'package_id', 'id')->whereRelation('partner', 'type', Partner::TYPE_SPACE);
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

    public function motoBikes(): HasOne
    {
        return $this->hasOne(MotorBike::class, 'package_id', 'id');
    }

    public function tarif(): HasMany
    {
        return $this->hasMany(\App\Models\Price::class, 'destination_id', 'destination_sub_district_id');
    }

    public function picked_up_by()
    {
        return $this->deliveries()->orderByPivot('created_at')->with('partner');
    }

    public function claimed_promotion(): HasOne
    {
        return $this->hasOne(ClaimedPromotion::class, 'package_id', 'id');
    }

    public function claimed_voucher(): HasOne
    {
        return $this->hasOne(ClaimedVoucher::class, 'package_id', 'id');
    }

    public function deliveries(): MorphToMany
    {
        return $this->morphToMany(Delivery::class, 'deliverable')
            ->withPivot(['is_onboard', 'status', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->orderByPivot('created_at')
            ->using(Deliverable::class);
    }

    public function code(): MorphOne
    {
        return $this->morphOne(Code::class, 'codeable');
    }

    public function estimator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estimator_id', 'id');
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'updated_by', 'id');
    }

    public function packager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packager_id', 'id');
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

    public function scopeWalkin($query)
    {
        return $query->where('transporter_type', NULL);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PENDING);
    }


    public function scopeFailed($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_FAILED);
    }

    public function getTypeAttribute()
    {
        if (!$this->transporter_type) {
            return self::TYPE_WALKIN;
        } else {
            return self::TYPE_APP;
        }
    }

    public function getChargePriceNoteAttribute()
    {
        return \App\Models\Price::query()
            ->where('origin_regency_id', $this->attributes['origin_regency_id'])
            ->where('destination_id', $this->attributes['destination_sub_district_id'])
            ->first();
    }

    public static function getAvailableDescriptionFormat()
    {
        return [
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_CREATED],
                'description' => 'Pesanan diinput',
                'variable' => []
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_PENDING],
                'description' => 'Menunggu Konfirmasi Mitra :partner_code menerima pesanan',
                'variable' => ['partner_code']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_WAITING_FOR_PICKUP],
                'description' => 'Courier menuju customer',
                'variable' => []
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_PICKED_UP],
                'description' => 'Paket telah dijemput oleh kurir Mitra :partner_code',
                'variable' => ['partner_code']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_WAITING_FOR_APPROVAL],
                'description' => 'Menunggu konfirmasi customer',
                'variable' => []
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_WAITING_FOR_ESTIMATING],
                'description' => 'Menunggu proses ukur dan timbang',
                'variable' => []
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_ESTIMATING],
                'description' => 'Paket sedang di ukur dan timbang oleh :estimator_name :partner_code',
                'variable' => ['partner_code', 'estimator_name']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_ESTIMATED],
                'description' => 'Paket telah di ukur dan timbang oleh :estimator_name :partner_code',
                'variable' => ['partner_code', 'estimator_name']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_REVAMP],
                'description' => 'Menunggu Konfirmasi Revisi oleh Mitra',
                'variable' => ['updated_at']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_DRAFT],
                'status' => [self::STATUS_CANCEL],
                'description' => 'Pesanan dibatalkan',
                'variable' => []
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PENDING],
                'status' => [self::STATUS_ACCEPTED],
                'description' => 'Menunggu pembayaran customer',
                'variable' => []
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PAID],
                'status' => [self::STATUS_WAITING_FOR_PACKING],
                'description' => 'Pembayaran sudah diverifikasi',
                'variable' => []
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PAID],
                'status' => [self::STATUS_PACKING],
                'description' => 'Paket sedang di packing oleh :packager_name :partner_code',
                'variable' => ['packager_name', 'partner_code']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PAID],
                'status' => [self::STATUS_PACKED],
                'description' => 'Paket telah dipacking dan siap diantar menuju gudang transit',
                'variable' => ['packager_name', 'partner_code']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PAID],
                'status' => [self::STATUS_MANIFESTED],
                'description' => 'Paket siap diantar ke Mitra :partner_code',
                'variable' => ['partner_code']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PAID],
                'status' => [self::STATUS_IN_TRANSIT],
                'description' => 'Paket sudah sampai di Mitra :partner_code dan diterima oleh :unloader_name',
                'variable' => ['partner_code', 'unloader_name']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PAID],
                'status' => [self::STATUS_WITH_COURIER],
                'description' => 'Paket diantar ke penerima oleh :origin_partner_code',
                'variable' => ['origin_partner_code']
            ],
            [
                'payment_status' => [self::PAYMENT_STATUS_PAID],
                'status' => [self::STATUS_DELIVERED],
                'description' => 'Paket diterima oleh :received_by',
                'variable' => ['received_by']
            ],

        ];
    }

    /**
     * get detail transporter.
     * @return mixed
     */
    public function getTransporterDetailAttribute(): ?array
    {
        $transporterType = $this->transporter_type;
        if (!$transporterType) {
            return null;
        }
        return Arr::first(Transporter::getDetailAvailableTypes(), function ($transporter) use ($transporterType) {
            if ($transporter['name'] === $transporterType) {
                return $transporter;
            } else {
                return null;
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function partner_performance(): HasOne
    {
        return $this->hasOne(\App\Models\Partners\Performances\Package::class, 'package_id', 'id')
            ->where('status', PerformanceModel::STATUS_ON_PROCESS)
            ->orderBy('created_at', 'desc');
    }

    public function getOrderTypeAttribute()
    {
        $motoBikes = $this->motoBikes()->first();

        if (is_null($motoBikes)) {
            return self::TYPE_ITEM;
        } else {
            return self::TYPE_BIKE;
        }
    }

    public function fileuploads(): HasMany
    {
        return $this->hasMany(FileUpload::class, 'package_id');
    }
}
