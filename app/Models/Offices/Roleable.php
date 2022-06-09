<?php

namespace App\Models\Offices;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
