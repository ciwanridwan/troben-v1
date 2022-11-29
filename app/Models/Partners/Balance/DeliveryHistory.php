<?php

namespace App\Models\Partners\Balance;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * History Model.
 *
 * @property int $partner_id
 * @property int $package_id
 * @property float $balance
 * @property string $type
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Partner $partner
 * @property-read Package $package
 */
class DeliveryHistory extends Model
{
    use CustomSerializeDate, HasFactory;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';
    public const TYPE_CHARGE = 'charge';

    public const DESCRIPTION_SERVICE = 'service';
    public const DESCRIPTION_PICKUP = 'pickup';
    public const DESCRIPTION_HANDLING = 'handling';
    public const DESCRIPTION_INSURANCE = 'insurance';
    public const DESCRIPTION_TRANSIT = 'transit';
    public const DESCRIPTION_DELIVERY = 'delivery';
    public const DESCRIPTION_DOORING = 'dooring';
    public const DESCRIPTION_RETURN = 'return';
    public const DESCRIPTION_WITHDRAW_REQUEST = 'request';
    public const DESCRIPTION_WITHDRAW_REJECT = 'reject';
    public const DESCRIPTION_WITHDRAW_CONFIRMED = 'confirmed';
    public const DESCRIPTION_WITHDRAW_SUCCESS = 'success';

    protected $table = 'partner_balance_delivery_histories';

    protected $fillable = [
        'partner_id',
        'delivery_id',
        'balance',
        'type',
        'description',
    ];

    protected $casts = [
        'balance' => 'float'
    ];

    /**
     * Get all available type on partner balance histories.
     *
     * @return string[]
     */
    public static function getAvailableType(): array
    {
        return [
            self::TYPE_DEPOSIT,
            self::TYPE_WITHDRAW,
            self::TYPE_CHARGE,
        ];
    }

    /**
     * Get all available description on partner balance histories.
     *
     * @return string[]
     */
    public static function getAvailableDescription(): array
    {
        return [
            self::DESCRIPTION_SERVICE,
            self::DESCRIPTION_PICKUP,
            self::DESCRIPTION_HANDLING,
            self::DESCRIPTION_INSURANCE,
            self::DESCRIPTION_RETURN,
            self::DESCRIPTION_TRANSIT,
            self::DESCRIPTION_DELIVERY,
            self::DESCRIPTION_DOORING,
            self::DESCRIPTION_WITHDRAW_REQUEST,
            self::DESCRIPTION_WITHDRAW_REJECT,
            self::DESCRIPTION_WITHDRAW_SUCCESS,
            self::DESCRIPTION_WITHDRAW_CONFIRMED,
        ];
    }

    /**
     * Define `belongsTo` relationship with Partner model.
     *
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    /** Relation to deliveries tables as a manifest */
    public function deliveries(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id');
    }
}
