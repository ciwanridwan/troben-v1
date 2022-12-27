<?php

namespace App\Models\Notifications;

use App\Casts\Notification\Data;
use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\UuidAsPrimaryKey;
use App\Models\Customers\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations;

/**
 * Notification Model.
 *
 * @property string $id
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property array|null $data
 * @property \Carbon\Carbon $read_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User|Customer $notifiable
 * @property-read Template $notification
 */
class Notification extends Relations\MorphPivot
{
    use CustomSerializeDate, UuidAsPrimaryKey;
    // level 3
    public const TIME_LIMIT_HAS_PASSED = 'Telah melewati batas waktu yang ditentukan';
    public const OWNER_HAS_LATE = 'Sudah melewati batas pengambilan barang';

    // level 2
    public const DRIVER_DELIVERY_ITEM = 'Segera lakukan pengiriman barang';
    public const WAREHOUSE_GOOD_RECEIVE = 'Segera lakukan good receive';
    public const WAREHOUSE_REQUEST_TRANSPORTER = 'Segera lakukan request transporter';
    public const DRIVER_DELIVERY_TO_WAREHOUSE = 'Barang harus segera dikirim ke gudang';
    public const OWNER_TAKE_ITEM = 'Segera melakukan pengambilan barang sesuai batas waktu yang ditentukan';

    protected $table = 'notifications';

    protected $casts = [
        'data' => Data::class,
    ];

    /**
     * Define 'MortpTo' relation.
     *
     * @return Relations\MorphTo
     */
    public function notifiable(): Relations\MorphTo
    {
        return $this->morphTo();
    }

    public static function messageLevelTree(): array
    {
        return [
            self::TIME_LIMIT_HAS_PASSED,
            self::OWNER_HAS_LATE
        ];
    }

    public static function messageLevelTwo(): array
    {
        return [
            self::DRIVER_DELIVERY_ITEM,
            self::WAREHOUSE_GOOD_RECEIVE,
            self::WAREHOUSE_REQUEST_TRANSPORTER,
            self::DRIVER_DELIVERY_TO_WAREHOUSE,
            self::OWNER_TAKE_ITEM
        ];
    }
}
