<?php

namespace App\Models\Partners\Prices;

use App\Models\Geo\SubDistrict;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Partner Dooring Price model.
 *
 * @property int                $partner_id
 * @property int                $origin_regency_id
 * @property int                $destination_sub_district_id
 * @property int                $type
 * @property int                $value
 * @property int|null           $created_by
 * @property int|null           $updated_by
 * @property \Carbon\Carbon     $created_at
 * @property \Carbon\Carbon     $updated_at
 * @property-read SubDistrict   $destination_sub_district
 */
class Dooring extends PriceModel
{
    use HasFactory;

    protected $table = 'partner_dooring_prices';

    protected $fillable = [
        'partner_id',
        'origin_regency_id',
        'destination_sub_district_id',
        'type',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Define destination sub district belongsto relation.
     *
     * @return BelongsTo
     */
    public function destination_sub_districts(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'destination_sub_district_id', 'id');
    }
}
