<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Partner Price model.
 *
 * @property int                              $partner_id
 * @property int                              $origin_regency_id
 * @property int                              $destination_id
 * @property string                           $service_code
 * @property string                           $notes
 * @property float                            $tier_1
 * @property float                            $tier_2
 * @property float                            $tier_3
 * @property float                            $tier_4
 * @property float                            $tier_5
 * @property float                            $tier_6
 * @property float                            $tier_7
 * @property float                            $tier_8
 * @property float                            $tier_9
 * @property float                            $tier_10
 * @property \Carbon\Carbon                   $created_at
 * @property \Carbon\Carbon                   $updated_at
 */
class Price extends Pivot
{
    use HasFactory;

    protected $table = 'partner_prices';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tier_1' => 'float',
        'tier_2' => 'float',
        'tier_3' => 'float',
        'tier_4' => 'float',
        'tier_5' => 'float',
        'tier_6' => 'float',
        'tier_7' => 'float',
        'tier_8' => 'float',
        'tier_9' => 'float',
        'tier_10' => 'float',
    ];
}
