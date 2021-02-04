<?php

namespace App\Models\Partners;

use App\Models\User;
use App\Models\Geo\Regency;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Warehouse model.
 *
 * @property int $id
 * @property int $partner_id
 * @property int $geo_province_id
 * @property int $geo_regency_id
 * @property int $geo_district_id
 * @property string $code
 * @property string $name
 * @property string                            $address
 * @property string                            $geo_area
 * @property bool                              $is_pool
 * @property bool                              $is_counter
 * @property \Carbon\Carbon                    $created_at
 * @property \Carbon\Carbon                    $updated_at
 * @property \Carbon\Carbon|null               $deleted_at
 *
 * @property-read \App\Models\Partners\Partner $partner
 * @property-read \App\Models\Geo\Province     $province
 * @property-read \App\Models\Geo\Regency      $regency
 * @property-read \App\Models\Geo\District     $district
 */
class Warehouse extends Model
{
    use SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'warehouses';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'partner_id',
        'geo_province_id',
        'geo_city_id',
        'geo_district_id',
        'code',
        'name',
        'address',
        'geo_area',
        'is_pool',
        'is_counter',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
        'is_pool' => 'boolean',
        'is_counter' => 'boolean',
    ];

    /**
     * Define `belongsTo` relationship with Partner model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Province Model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'geo_province_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with City model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'geo_city_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with District model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'geo_district_id', 'id');
    }

    /**
     * Define `morphToMany` relationship with User Model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphToMany(User::class, 'userable', 'userables')
            ->using(UserablePivot::class);
    }
}
