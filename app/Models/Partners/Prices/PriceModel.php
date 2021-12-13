<?php
namespace App\Models\Partners\Prices;

use App\Models\Geo\Regency;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Base Partner Price Model
 *
 * @property-read Partner $partner
 * @property-read Regency $origin_regency
 */
class PriceModel extends Pivot
{
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

    public $incrementing = false;

    /**
     * define belongs to with partners.
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class,'partner_id','id');
    }

    /**
     * define belongs to with regency tables.
     * @return BelongsTo
     */
    public function origin_regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class,'origin_regency_id', 'id');
    }
}
