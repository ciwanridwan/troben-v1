<?php

namespace App\Models\Offices;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Roleable extends Model
{
    use CustomSerializeDate;

    protected $table = 'model_has_roles';

    protected $casts = [

    ];

    /**
     * Define 'MortpTo' relation.
     *
     * @return MorphTo
     */
    public function role(): MorphTo
    {
        return $this->morphTo();
    }
}
