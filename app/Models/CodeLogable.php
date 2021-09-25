<?php

namespace App\Models;

use App\Casts\Code\Log\Description;
use App\Casts\Code\Showable;
use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ReflectionClass;

class CodeLogable extends MorphPivot
{
    use HasFactory, CustomSerializeDate;
    public const TYPE_ERROR = 'error';
    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_NEUTRAL = 'neutral';
    public const TYPE_SCAN = 'scan';

    public const SHOW_CUSTOMER = 'customer';
    public const SHOW_PARTNER = 'partner';
    public const SHOW_ADMIN = 'admin';
    public const SHOW_ALL = [
        self::SHOW_CUSTOMER,
        self::SHOW_PARTNER,
        self::SHOW_ADMIN
    ];

    public const STATUS_CREATED_DRAFT = 'created_draft';
    public const STATUS_PICKUP_WAITING_ASSIGN_PACKAGE = 'pickup_waiting_assign_package';
    public const STATUS_PICKUP_EN_ROUTE = 'pickup_en-route';
    public const STATUS_ESTIMATED_DRAFT = 'estimated_draft';
    public const STATUS_ESTIMATING_DRAFT = 'estimating_draft';
    public const STATUS_REVAMP_DRAFT = 'revamp_draft';
    public const STATUS_WAITING_FOR_APPROVAL_DRAFT = 'waiting_for_approval_draft';
    public const STATUS_WAITING_FOR_APPROVAL_PAID = 'waiting_for_approval_paid';
    public const STATUS_REVAMP_PENDING = 'revamp_pending';
    public const STATUS_ACCEPTED_PENDING = 'accepted_pending';
    public const STATUS_WAITING_FOR_PACKING_PAID = 'waiting_for_packing_paid';
    public const STATUS_PACKING_PAID = 'packing_paid';
    public const STATUS_PACKED_PAID = 'packed_paid';
    public const STATUS_WAREHOUSE = 'warehouse';
    public const STATUS_WAREHOUSE_UNLOAD = 'warehouse_unload';
    public const STATUS_TRANSIT_EN_ROUTE = 'transit_en-route';
    public const STATUS_TRANSIT_FINISHED = 'transit_finished';
    public const STATUS_IN_TRANSIT_PAID = 'in_transit_paid';
    public const STATUS_DRIVER = 'driver';
    public const STATUS_DRIVER_LOAD = 'driver_load';
    public const STATUS_DRIVER_DOORING_LOAD = 'driver_dooring_load';
    public const STATUS_WITH_COURIER_PAID = 'with_courier_paid';
    public const STATUS_DELIVERED_PAID = 'delivered_paid';
    public const STATUS_CANCEL_DRAFT = 'cancel_draft';

    public static $staticMakeVisible;
    protected $table = 'code_logables';

    protected $hidden = [
        'id',
        'code_id',
        'code_logable_id',
        'code_logable_type',
        'type',
        'showable',
        'status',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'showable' => Showable::class,
        // 'description' => Description::class,
    ];



    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        if (isset(self::$staticMakeVisible)) {
            $this->makeVisible(self::$staticMakeVisible);
        }
    }

    public function __destruct()
    {
        self::$staticMakeVisible = null;
    }

    public function code(): BelongsTo
    {
        return $this->belongsTo(Code::class, 'code_id', 'id');
    }

    public function code_logable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array
     */
    public static function getLogPackageStatusAvailables(): array
    {
        $statuses = [];
        foreach (Package::getStatusConst() as $status => $statusValue) {
            foreach (Package::getPaymentStatusConst() as $paymentStatus => $paymentStatusValue) {
                $statuses[$status.'_'.$paymentStatus] = $statusValue.'_'.$paymentStatusValue;
            }
        }
        return $statuses;
    }

    /**
     * @return array
     */
    public static function getLogDeliveryStatusAvailables(): array
    {
        $statuses = [];
        foreach (Delivery::getTypeConst() as $type => $typeValue) {
            foreach (Delivery::getStatusConst() as $status => $statusValue) {
                $statuses[$type.'_'.$status] = $typeValue.'_'.$statusValue;
            }
        }
        return $statuses;
    }

    public static function getAvailableStatusCode()
    {
        return array_flip(array_merge(self::getLogPackageStatusAvailables(), self::getLogDeliveryStatusAvailables(), [
            UserablePivot::ROLE_DRIVER,
            UserablePivot::ROLE_WAREHOUSE,
            self::STATUS_WAREHOUSE_UNLOAD,
            self::STATUS_DRIVER_LOAD,
            self::STATUS_DRIVER_DOORING_LOAD
        ]));
    }

    /**
     * Get error codes.
     *
     * @return string[]
     */
    public static function getStatusCode(): array
    {
        $class = new ReflectionClass(__CLASS__);

        return array_flip($class->getConstants());
    }

    public static function getAvailableTypes()
    {
        return [
            self::TYPE_ERROR,
            self::TYPE_INFO,
            self::TYPE_WARNING,
            self::TYPE_NEUTRAL,
            self::TYPE_SCAN
        ];
    }

    public static function getAvailableShowable()
    {
        return [
            self::SHOW_ADMIN,
            self::SHOW_CUSTOMER,
            self::SHOW_PARTNER
        ];
    }
}
