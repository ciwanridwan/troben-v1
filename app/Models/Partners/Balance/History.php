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
    public const TYPE_PENALTY = 'penalty';
    public const TYPE_DISCOUNT = 'discount';

    public const DESCRIPTION_SERVICE = 'service'; // service fee get order
    public const DESCRIPTION_PICKUP = 'pickup'; // pickup fee by transporter
    public const DESCRIPTION_HANDLING = 'handling'; // handling fee (packing)
    public const DESCRIPTION_INSURANCE = 'insurance'; // insurance fee
    public const DESCRIPTION_TRANSIT = 'transit'; // transit items
    public const DESCRIPTION_DELIVERY = 'delivery'; // delivery fee for transit items (transporter)
    public const DESCRIPTION_DOORING = 'dooring'; // dooring fee to end user (transporter)
    public const DESCRIPTION_RETURN = 'return'; // return fee (transporter)
    public const DESCRIPTION_ADDITIONAL = 'additional'; // charge
    public const DESCRIPTION_LATENESS = 'late'; // pengiriman terlambat
    public const DESCRIPTION_SERVICE_REGULAR = 'regular'; // income pengiriman motor
    public const DESCRIPTION_SERVICE_BIKE = 'bike'; // income pengiriman motor
    public const DESCRIPTION_SERVICE_CUBIC = 'cubic'; // income pengiriman kubikasi


    public const DESCRIPTION_WITHDRAW_REQUESTED = 'request';
    public const DESCRIPTION_WITHDRAW_APPROVED = 'approve';

    protected $table = 'partner_balance_histories';

    protected $fillable = [
        'partner_id',
        'package_id',
        'balance',
        'type',
        'description',
        'disbursement_id',
        'services'
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
            self::TYPE_PENALTY,
            self::TYPE_DISCOUNT
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
            self::DESCRIPTION_ADDITIONAL,
            self::DESCRIPTION_LATENESS,
            self::DESCRIPTION_WITHDRAW_REQUESTED,
            self::DESCRIPTION_WITHDRAW_APPROVED
        ];
    }

    public static function getAvailableServices(): array
    {
        return [
            self::DESCRIPTION_SERVICE_REGULAR,
            self::DESCRIPTION_SERVICE_BIKE,
            self::DESCRIPTION_SERVICE_CUBIC,
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

    public static function setLabel(string $type, string $description)
    {
            switch (true) {
                case $type === History::TYPE_DISCOUNT && $description === History::DESCRIPTION_SERVICE:
                    $label = 'Diskon Pengiriman';
                    break;
                case $type === History::TYPE_DISCOUNT && $description === History::DESCRIPTION_PICKUP:
                    $label = 'Diskon Penjemputan';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_PICKUP:
                    $label = 'Penjemputan';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_SERVICE:
                    $label = 'Pengiriman';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_DOORING:
                    $label = 'Dooring';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_DELIVERY:
                    $label = 'Delivery';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_INSURANCE:
                    $label = 'Asuransi';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_HANDLING:
                    $label = 'Handling';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_ADDITIONAL:
                    $label = 'Biaya Tambahan (Deposit)';
                    break;
                case $type === History::TYPE_CHARGE && $description === History::DESCRIPTION_ADDITIONAL:
                    $label = 'Biaya Tambahan (Charge)';
                    break;
                case $type === History::TYPE_DEPOSIT && $description === History::DESCRIPTION_TRANSIT:
                    $label = 'Transit';
                    break;
                case $type === History::TYPE_PENALTY:
                    if ($description === History::DESCRIPTION_LATENESS) {
                        $label = 'Denda: Pengiriman Terlambat';
                    } else {
                        $label = 'Denda: ' . $description;
                    }
                    break;
                default:
                    $label = sprintf('%s: %s', $type, $description);
                    break;
            }

            return $label;
    }
}
