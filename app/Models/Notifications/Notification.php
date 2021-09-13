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
}
