<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class Code
 * @package App\Models
 *
 * @property-read  null|\App\Models\Deliveries\Delivery|\App\Models\Packages\Package|\App\Models\Packages\Item codeable
 */
class Code extends Model
{
    use HasFactory;

    public function codeable(): Relations\MorphTo
    {
        return $this->morphTo();
    }
}
