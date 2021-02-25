<?php

namespace App\Models\Partners\Pivot;

use App\Models\Partners\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Veelasky\LaravelHashId\Eloquent\HashableId;

class UserablePivot extends MorphPivot
{
    use HashableId;
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
