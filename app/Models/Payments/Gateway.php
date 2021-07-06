<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Payment gateway model.
 *
 * @property int $id
 * @property string $channel
 * @property string $name
 * @property float $admin_charges
 * @property bool $is_fixed
 * @property bool $is_bank_transfer
 * @property string $account_bank
 * @property string $account_number
 * @property string $account_name
 * @property array $options
 * @property bool $auto_approve
 * @property bool $is_active
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Payments\Payment[]|\Illuminate\Database\Eloquent\Collection $payments
 */
class Gateway extends Model
{
    use HashableId;

    public const CHANNEL_NICEPAY_MANDIRI_VA = 'npmdrva';
    public const CHANNEL_NICEPAY_BCA_VA = 'npbcava';
    public const CHANNEL_NICEPAY_PERMATA_VA = 'nppmtva';
    public const CHANNEL_NICEPAY_BRI_VA = 'npbriva';
    public const CHANNEL_NICEPAY_BNI_VA = 'npbniva';
    public const CHANNEL_NICEPAY_CIMB_VA = 'npcimbva';
    public const CHANNEL_NICEPAY_SHPPEE_QRIS = 'npsppqris';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_gateways';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'admin_charges' => 'float',
        'is_bank_transfer' => 'boolean',
        'options' => 'json',
        'auto_approve' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'id',
    ];

    protected $appends = [
        'hash',
        'type',
        'bank'
    ];

    /**
     * Define `hasMany` relationship with Payment model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'gateway_id', 'id');
    }

    /**
     * @param string $channel
     * @return string[]
     */
    public static function convertChannel(string $channel): array
    {
        $bank = [
            self::CHANNEL_NICEPAY_BCA_VA => [
                'bank' => 'bca',
                'type' => 'va',
            ],
            self::CHANNEL_NICEPAY_MANDIRI_VA => [
                'bank' => 'mandiri',
                'type' => 'va'
            ],
            self::CHANNEL_NICEPAY_PERMATA_VA => [
                'bank' => 'permata',
                'type' => 'va'
            ],
            self::CHANNEL_NICEPAY_BNI_VA => [
                'bank' => 'bni',
                'type' => 'va'
            ],
            self::CHANNEL_NICEPAY_BRI_VA => [
                'bank' => 'bri',
                'type' => 'va'
            ],
            self::CHANNEL_NICEPAY_CIMB_VA => [
                'bank' => 'cimb',
                'type' => 'va'
            ],
            self::CHANNEL_NICEPAY_SHPPEE_QRIS => [
                'bank' => 'shoppe-pay',
                'type' => 'qris'
            ]
        ];

        return $bank[$channel];
    }

    /**
     * Accessor for get type.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return self::convertChannel($this->channel)['type'];
    }

    /**
     * Accessor for get bank.
     *
     * @return string
     */
    public function getBankAttribute()
    {
        return self::convertChannel($this->channel)['bank'];
    }
}
