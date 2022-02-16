<?php

namespace App\Models\Partners\Prices;

use App\Models\Geo\Regency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Partner Transit Price model.
 *
 * @property int                $partner_id
 * @property int                $origin_regency_id
 * @property int                $destination_regency_id
 * @property int                $type
 * @property int                $value
 * @property int                $shipment_type
 * @property int|null           $created_by
 * @property int|null           $updated_by
 * @property \Carbon\Carbon     $created_at
 * @property \Carbon\Carbon     $updated_at
 * @property-read Regency       $destination_regency
 */
class Transit extends PriceModel
{
    use HasFactory;

    public const SHIPMENT_LAND = 1;
    public const SHIPEMNT_SEA = 2;
    public const SHIPMENT_AIRWAY = 3;

    protected $table = 'partner_transit_prices';

    protected $fillable = [
        'partner_id',
        'origin_regency_id',
        'destination_regency_id',
        'type',
        'value'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Define destination regency belongsto relation.
     *
     * @return BelongsTo
     */
    public function destination_regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'destination_regency_id', 'id');
    }
}
