<?php

namespace App\Models\Geo;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * District model.
 *
 * @property int                                                                         $id
 * @property int                                                                         $country_id
 * @property int                                                                         $province_id
 * @property int                                                                         $city_id
 * @property string                                                                      $name
 * @property \Carbon\Carbon                                                              $created_at
 * @property \Carbon\Carbon                                                              $updated_at
 *
 * @property-read \App\Models\Geo\Country|null                                           $country
 * @property-read \App\Models\Geo\Province|null                                          $province
 * @property-read \App\Models\Geo\Regency|null                                           $regency
 * @property-read \App\Models\Geo\SubDistrict[]|\Illuminate\Database\Eloquent\Collection $sub_districts
 */
class District extends Model
{
    use CustomSerializeDate;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'geo_districts';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'country_id',
        'province_id',
        'regency_id',
        'name',
    ];

    /**
     * Define `belongsTo` relationship with Country model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Province model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with City model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'id');
    }

    /**
     * Define `hasMany` relationship with SubDistrict model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sub_districts(): HasMany
    {
        return $this->hasMany(SubDistrict::class, 'district_id', 'id');
    }
}
