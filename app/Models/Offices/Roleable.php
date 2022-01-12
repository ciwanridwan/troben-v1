<?php

namespace App\Models\Offices;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Permission\Models\Role;

class Roleable extends Model
{
    use CustomSerializeDate;

    protected $table = 'model_has_roles';

    protected $casts = [];

    /**
     * @return BelongsTo
     */
    public function detail(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
