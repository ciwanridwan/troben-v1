<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Userable Model.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $userable_type Partner | Transporter | Warehouse
 * @property int $userable_id
 * @property string $role owner | cashier | customer service
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Userable extends Model
{
    /**
     * Define Polymorph relationship.
     * 
     * @return MorphTo
     */
    public function partnerable() : MorphTo
    {
        return $this->morphTo('partner', 'userable_type', 'userable_id');
    }
}
