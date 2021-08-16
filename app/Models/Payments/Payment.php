<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment model.
 *
 * @property int $id
 * @property int|null $gateway_id
 * @property string $payable_type
 * @property int $payable_id
 * @property string $service_type
 * @property float $payment_amount
 * @property float $payment_admin_charges
 * @property float $total_payment
 * @property string $sender_bank
 * @property string $sender_name
 * @property string $sender_account
 * @property string $payment_content
 * @property string $payment_ref_id
 * @property string $status
 * @property \Carbon\Carbon $expired_at
 * @property int $confirmed_by
 * @property \Carbon\Carbon $confirmed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Payments\Gateway|null $gateway
 */
class Payment extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_WAITING_FOR_APPROVAL = 'waiting_for_approval';
    public const STATUS_EXPIRED = 'expired';

    public const SERVICE_TYPE_PAYMENT = 'pay';
    public const SERVICE_TYPE_REVERSAL = 'rev';
    public const SERVICE_TYPE_WITHDRAWAL = 'wdr';
    public const SERVICE_TYPE_DEPOSIT = 'dep';


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    public $fillable = [
        'gateway_id',
        'service_type',
        'payable_type',
        'payable_id',
        'payment_amount',
        'payment_admin_charges',
        'total_payment',
        'sender_bank',
        'sender_name',
        'sender_account',
        'payment_content',
        'payment_ref_id',
        'expired_at',
        'confirmed_by',
        'confirmed_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_amount' => 'float',
        'payment_admin_charges' => 'float',
        'total_payment' => 'float',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get all available statuses.
     *
     * @return string[]
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED,
            self::STATUS_SUCCESS,
            self::STATUS_WAITING_FOR_APPROVAL,
        ];
    }

    /**
     * Get all available services.
     *
     * @return string[]
     */
    public static function getAvailableServices(): array
    {
        return [
            self::SERVICE_TYPE_PAYMENT,
            self::SERVICE_TYPE_REVERSAL,
            self::SERVICE_TYPE_WITHDRAWAL,
            self::SERVICE_TYPE_DEPOSIT
        ];
    }

    /**
     * Define `belongsTo` relationship with gateway model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'gateway_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function payable(): MorphTo
    {
        return $this->morphTo('payable');
    }
}
