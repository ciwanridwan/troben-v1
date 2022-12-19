<?php

namespace App\Models\Notifications;

use App\Casts\Notification\Data;
use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Customers\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Template Model.
 *
 * @property string $id
 * @property string $type
 * @property array|null $data
 * @property string $priority
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Template extends Model
{
    use HasFactory, CustomSerializeDate;

    public const TYPE_CUSTOMER_HAS_PAID = 'customer_has_paid';
    public const TYPE_PARTNER_BALANCE_UPDATED = 'partner_balance_updated';
    public const TYPE_CS_GET_NEW_ORDER = 'cs_get_new_order';
    public const TYPE_DRIVER_GET_ALERT_ONE_LEVEL = 'driver_get_alert_one_level';
    public const TYPE_WAREHOUSE_START_PACKING = 'warehouse_should_packing';

    protected $table = 'notification_templates';

    protected $fillable = [
        'type',
        'data',
        'priority',
    ];

    protected $casts = [
        'data' => Data::class,
    ];

    /**
     * @return MorphToMany
     */
    public function customers(): MorphToMany
    {
        return $this->morphedByMany(Customer::class, 'notifiable')
            ->withPivot('read_at')
            ->withTimestamps()
            ->orderByPivot('created_at')
            ->using(Notification::class);
    }

    /**
     * @return MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'notifiable')
            ->withPivot('read_at')
            ->withTimestamps()
            ->orderByPivot('created_at')
            ->using(Notification::class);
    }
}
