<?php

namespace App\Models\Partners\Pivot;

use App\Models\Deliveries\Delivery;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class UserablePivot.
 *
 * @property  int id
 * @property  string role
 * @property-read  User user
 * @property-read  \App\Models\Partners\Partner|\App\Models\Partners\Transporter userable
 */
class UserablePivot extends Relations\MorphPivot
{
    use HashableId;

    const ROLE_OWNER = 'owner';
    const ROLE_DRIVER = 'driver';
    const ROLE_CASHIER = 'cashier';
    const ROLE_CS = 'customer-service';
    const ROLE_WAREHOUSE = 'warehouse';

    public $incrementing = true;

    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'userables';

    /**
     * @var array
     */
    protected $appends = ['hash'];

    /**
     * @var array
     */
    protected $hidden = [
        'id',
        'userable_id',
        'userable_type',
        'user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'userable_type',
        'userable_id',
        'role',
    ];

    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_DRIVER,
            self::ROLE_CASHIER,
            self::ROLE_CS,
            self::ROLE_WAREHOUSE,
        ];
    }

    public static function getHomeRouteRole($roleName): string
    {
        $route = [
            self::ROLE_CASHIER => route('partner.cashier.home.all'),
            self::ROLE_CS => route('partner.customer_service.home'),
        ];

        if (! Arr::has($route, $roleName)) {
            abort(404);
        }

        return $route[$roleName];
    }

    public function userable(): Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->where('deleted_at');
    }

    public function deliveries(): Relations\HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->whereHas('user')->whereHas('userable');
        });
    }
}
