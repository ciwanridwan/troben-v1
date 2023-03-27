<?php

namespace App\Models;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Service model.
 *
 * @property string $code
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \App\Models\Price[]|\Illuminate\Database\Eloquent\Collection $prices
 */
class Service extends Model
{
    use CustomSerializeDate;

    public const TRAWLPACK_INSTANT = 'tpi';
    public const TRAWLPACK_SAMEDAY = 'tpd';
    public const TRAWLPACK_EXPRESS = 'tpx';
    public const TRAWLPACK_STANDARD = 'tps';
    public const TRAWLPACK_CUBIC = 'tpc';

    public const TPS = 'Reguler';
    public const TPX = 'Express';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Define `hasMany` relationship with Price model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'service_code', 'code');
    }

    /**
     * To get available service type
     */
    public static function getAvailableType(): array
    {
        return [
            self::TRAWLPACK_EXPRESS,
            self::TRAWLPACK_STANDARD
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('services', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }
}
