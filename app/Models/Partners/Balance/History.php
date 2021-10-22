<?php

namespace App\Models\Partners\Balance;

use App\Concerns\Controllers\CustomSerializeDate;
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
class History extends Model
{
    use CustomSerializeDate, HasFactory;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';
    public const TYPE_CHARGE = 'charge';

    public const DESCRIPTION_SERVICE = 'service'; // service fee get order
    public const DESCRIPTION_PICKUP = 'pickup'; // pickup fee by transporter
    public const DESCRIPTION_HANDLING = 'handling'; // handling fee (packing)
    public const DESCRIPTION_INSURANCE = 'insurance'; // insurance fee
    public const DESCRIPTION_TRANSIT = 'transit'; // transit items
    public const DESCRIPTION_DELIVERY = 'delivery'; // delivery fee for transit items (transporter)
    public const DESCRIPTION_DOORING = 'dooring'; // dooring fee to end user (transporter)
    public const DESCRIPTION_RETURN = 'return'; // return fee (transporter)

    protected $table = 'partner_balance_histories';

    protected $fillable = [
        'partner_id',
        'package_id',
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
}
