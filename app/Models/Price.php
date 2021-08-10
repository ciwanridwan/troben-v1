<?php

namespace App\Models;

use App\Models\Geo\Regency;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Models\AttributeColumns;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Price model.
 *
 * @property int $id
 * @property int $origin_id
 * @property int $origin_province_id
 * @property int                              $origin_city_id
 * @property int                              $origin_district_id
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
 *
 * @property-read \App\Models\Geo\SubDistrict $origin
 * @property-read \App\Models\Geo\Province    $province
 * @property-read \App\Models\Geo\Regency     $city
 * @property-read \App\Models\Geo\District    $district
 * @property-read \App\Models\Geo\SubDistrict $destination
 * @property-read \App\Models\Service         $service
 */
class Price extends Model
{
    use AttributeColumns, HasFactory, HashableId;

    public const TIER_1 = 10;
    public const TIER_2 = 30;
    public const TIER_3 = 50;
    public const TIER_4 = 100;
    public const TIER_5 = 1000;
    public const TIER_6 = 3000;

    public const DIVIDER_DARAT = 4000;
    public const DIVIDER_UDARA = 6000;

    public const MIN_WEIGHT = 10;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prices';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'origin_province_id',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_id',
        'zip_code',
        'service_code',
        'notes',
        'tier_1',
        'tier_2',
        'tier_3',
        'tier_4',
        'tier_5',
        'tier_6',
        'tier_7',
        'tier_8',
        'tier_9',
        'tier_10',
    ];

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

    /**
     * Define `belongsTo` relationship with SubDistrict model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function origin(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'origin_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'origin_district_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Regency model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'origin_regency_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Province model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'origin_province_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with SubDistrict model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'destination_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Service model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_code', 'code');
    }
}
