<?php

namespace App\Models\Partners\Balance;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
 */
class History extends Pivot
{
    use CustomSerializeDate, HasFactory;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    public const DESCRIPTION_SERVICE = 'service';
    public const DESCRIPTION_PICKUP = 'pickup';
    public const DESCRIPTION_PACKING = 'packing';
    public const DESCRIPTION_INSURANCE = 'insurance';
    public const DESCRIPTION_RETURN = 'return';

    protected $table = 'partner_balance_histories';

    protected $fillable = [
        'partner_id',
        'package_id',
        'balance',
        'type',
        'description',
    ];

    public static function getAvailableType()
    {
        return [
            self::TYPE_DEPOSIT,
            self::TYPE_WITHDRAW,
        ];
    }

    public static function getAvailableDescription()
    {
        return [
            self::DESCRIPTION_SERVICE,
            self::DESCRIPTION_PICKUP,
            self::DESCRIPTION_PACKING,
            self::DESCRIPTION_INSURANCE,
            self::DESCRIPTION_RETURN,
        ];
    }
}
