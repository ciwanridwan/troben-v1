<?php

namespace App\Models\Geo;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Province model.
 *
 * @property int                                                                         $id
 * @property int                                                                         $country_id
 * @property string                                                                      $name
 * @property string                                                                      $iso_code
 * @property \Carbon\Carbon                                                              $created_at
 * @property \Carbon\Carbon                                                              $updated_at
 *
 * @property-read \App\Models\Geo\Country|null                                           $country
 * @property-read \App\Models\Geo\Regency[]|\Illuminate\Database\Eloquent\Collection     $regencies
 * @property-read \App\Models\Geo\District[]|\Illuminate\Database\Eloquent\Collection    $districts
 * @property-read \App\Models\Geo\SubDistrict[]|\Illuminate\Database\Eloquent\Collection $sub_districts
 */
class Province extends Model
{
    use CustomSerializeDate;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'geo_provinces';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'country_id',
        'name',
        'iso_code',
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
     * Define `hasMany` relationship with City model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class, 'province_id', 'id');
    }

    /**
     * Define `hasMany` relationship with District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'province_id', 'id');
    }

    /**
     * Define `hasMany` relationship with SubDistrict model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sub_districts(): HasMany
    {
        return $this->hasMany(SubDistrict::class, 'province_id', 'id');
    }
}
