<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Partner Price model.
 *
 * @property int                $partner_id
 * @property int                $origin_regency_id
 * @property int                $destination_id
 * @property int                $type
 * @property int                $value
 * @property \Carbon\Carbon     $created_at
 * @property \Carbon\Carbon     $updated_at
 */
class Price extends Pivot
{
    use HasFactory;

    public const TYPE_SLA = 1;
    public const TYPE_FLAT = 2;
    public const TYPE_TIER_1 = 3;
    public const TYPE_TIER_2 = 4;
    public const TYPE_TIER_3 = 5;
    public const TYPE_TIER_4 = 6;
    public const TYPE_TIER_5 = 7;
    public const TYPE_TIER_6 = 8;
    public const TYPE_TIER_7 = 9;
    public const TYPE_TIER_8 = 10;

    protected $table = 'partner_prices';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * define belongs to with partners.
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class,'partner_id','id');
    }
}
