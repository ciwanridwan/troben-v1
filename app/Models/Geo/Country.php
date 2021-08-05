<?php

namespace App\Models\Geo;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Country model.
 *
 * @property int                                                                         $id
 * @property string                                                                      $name
 * @property string                                                                      $alpha2
 * @property string                                                                      $alpha3
 * @property string                                                                      $numeric
 * @property string                                                                      $phone_prefix
 * @property \Carbon\Carbon                                                              $created_at
 * @property \Carbon\Carbon                                                              $updated_at
 *
 * @property-read \App\Models\Geo\Province[]|\Illuminate\Database\Eloquent\Collection    $provinces
 * @property-read \App\Models\Geo\Regency[]|\Illuminate\Database\Eloquent\Collection     $regencies
 * @property-read \App\Models\Geo\District[]|\Illuminate\Database\Eloquent\Collection    $districts
 * @property-read \App\Models\Geo\SubDistrict[]|\Illuminate\Database\Eloquent\Collection $sub_districts
 */
class Country extends Model
{
    use CustomSerializeDate;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'geo_countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'alpha2',
        'alpha3',
        'numeric',
        'phone_prefix',
    ];

    /**
     * Define `hasMany` relationship with Province model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'country_id', 'id');
    }

    /**
     * Define `hasMany` relationship with City model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class, 'country_id', 'id');
    }

    /**
     * Define `hasMany` relationship with District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'country_id', 'id');
    }

    /**
     * Define `hasMany` relationship with SubDistrict model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sub_districts(): HasMany
    {
        return $this->hasMany(SubDistrict::class, 'country_id', 'id');
    }
}
