<?php

namespace App\Models\Partners\Pivot;

use App\Models\User;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

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
        self::ROLE_CASHIER,
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
    public function userable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
