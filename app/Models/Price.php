<?php

namespace App\Models;

use App\Models\Geo\Regency;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property float                            $price
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
        'origin_id',
        'origin_province_id',
        'origin_city_id',
        'origin_district_id',
        'destination_id',
        'service_code',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
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
     * Define `belongsTo` relationship with City model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'origin_city_id', 'id');
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