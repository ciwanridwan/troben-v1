<?php

namespace App\Models\Partners\Pivot;

use App\Models\Partners\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Veelasky\LaravelHashId\Eloquent\HashableId;

class UserablePivot extends MorphPivot
{
    use HashableId;

    const ROLE_OWNER = 'owner';
    const ROLE_DRIVER = 'driver';
    const ROLE_CASHIER = 'cashier';
    const ROLE_CS = 'customer service';
    const ROLE_WAREHOUSE = 'warehouse';

    const ROLES = [
        self::ROLE_OWNER,
        self::ROLE_DRIVER,
        self::ROLE_CASHIER
    ];

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
    protected $hidden = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'userable_type',
        'userlable_id',
        'role',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->whereHas('user')->whereHas('userable');
        });
    }

    public function userable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->where('deleted_at', NULL);
    }
    public function get()
    {
    }
}
