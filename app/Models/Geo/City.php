<?php

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * City model.
 *
 * @property int $id
 * @property int $country_id
 * @property int $province_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \App\Models\Geo\Country|null  $country
 * @property-read \App\Models\Geo\Province|null $province
 * @property-read \App\Models\Geo\District[]|\Illuminate\Database\Eloquent\Collection $districts
 * @property-read \App\Models\Geo\SubDistrict[]|\Illuminate\Database\Eloquent\Collection $sub_districts
 */
class City extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'geo_cities';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'country_id',
        'province_id',
        'name',
    ];

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
     * Define `belongsTo` relationship with Country model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Define `hasMany` relationship with District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'city_id', 'id');
    }

    /**
     * Define `hasMany` relationship with SubDistrict model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sub_districts(): HasMany
    {
        return $this->hasMany(SubDistrict::class, 'city_id', 'id');
    }
}
