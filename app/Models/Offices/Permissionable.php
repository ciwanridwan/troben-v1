<?php

namespace App\Models\Offices;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Permissionable extends Model
{
    use CustomSerializeDate;

    protected $table = 'model_has_permissions';

    protected $casts = [

    ];

    /**
     * Define 'MortpTo' relation.
     *
     * @return MorphTo
     */
    public function permission(): MorphTo
    {
        return $this->morphTo();
    }
}
