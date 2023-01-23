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
    public const TYPE_CUSTOMER_SHOULD_CONFIRM_ORDER = 'customer_should_confirm_order';

    // push notification level 1 of sla
    public const TYPE_DRIVER_GET_ALERT_ONE_LEVEL = 'driver_get_alert_one_level';
    public const TYPE_WAREHOUSE_START_PACKING = 'warehouse_should_packing';
    public const TYPE_OWNER_SHOULD_TAKE_PACKAGE = 'owner_should_take_package';
    public const TYPE_WAREHOUSE_GOOD_RECEIVE = 'warehouse_good_receive';
    public const TYPE_WAREHOUSE_REQUEST_TRANSPORTER = 'warehouse_request_transporter';
    public const TYPE_DRIVER_DOORING_TO_RECEIVER = 'driver_dooring_to_receiver';

    // push notification for level 2 and 3 of sla
    // dooring
    public const TYPE_TIME_LIMIT_HAS_PASSED = 'time_limit_has_passed'; // push notif level 3
    public const TYPE_DRIVER_IMMEDIATELY_DELIVERY_OF_ITEM = 'driver_immediately_delivery_of_item'; // push notif level 2

    // warehouse good receive
    public const TYPE_WAREHOUSE_IMMEDIATELY_GOOD_RECEIVE = 'warehouse_immediately_good_receive'; // push notif level 2

    // warehouse after good receive
    public const TYPE_WAREHOUSE_IMMEDIATELY_REQUEST_TRANSPORTER = 'warehouse_immediately_request_transporter'; // push notif level 2

    //driver of mtak
    public const TYPE_DRIVER_IMMEDIATELY_DELIVERY_TO_WAREHOUSE = 'driver_immediately_delivery_to_warehouse'; // push notif level 2

    // mtak owner
    public const TYPE_OWNER_IMMEDIATELY_TAKE_ITEM = 'owner_immediately_take_item'; // push notif level 2
    public const TYPE_OWNER_HAS_LATE = 'owner_has_late'; // push notif level 3

    // driver of mb
    public const TYPE_DRIVER_SHOULD_DELIVERY_TO_WAREHOUSE = 'driver_should_delivery_to_warehouse'; // push notif level 2



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
