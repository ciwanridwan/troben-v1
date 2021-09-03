<?php

namespace App\Models\Geo;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Regency model.
 *
 * @property int $id
 * @property int $country_id
 * @property int $province_id
 * @property string $name
 * @property string $capital
 * @property string $bsn_code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \App\Models\Geo\Country|null  $country
 * @property-read \App\Models\Geo\Province|null $province
 * @property-read \App\Models\Geo\District[]|\Illuminate\Database\Eloquent\Collection $districts
 * @property-read \App\Models\Geo\SubDistrict[]|\Illuminate\Database\Eloquent\Collection $sub_districts
 */
class Regency extends Model
{
    public const JABODETABEK = [
            'Kota Tangerang',
            'Kota Tangerang Selatan',
            'Kota Adm. Jakarta Barat',
            'Kota Adm. Jakarta Pusat',
            'Kota Adm. Jakarta Selatan',
            'Kota Adm. Jakarta Timur',
            'Kota Adm. Jakarta Utara',
            'Kabupaten Bekasi',
            'Kota Bekasi',
            'Kabupaten Bogor',
            'Kota Bogor',
            'Kota Depok',
    ];

    use CustomSerializeDate;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'geo_regencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'country_id',
        'province_id',
        'name',
        'capital',
        'bsn_code',
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

    /**
     * Get data jabodetabek.
     *
     * @return array
     */
    public static function getJabodetabekId(): array
    {
        return Regency::query()->whereIn('name', self::JABODETABEK)->get('id')->pluck('id')->toArray();
    }
}
