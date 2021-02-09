<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Package price model.
 *
 * @property int $id
 * @property int $package_id
 * @property string $type
 * @property float $amount
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \App\Models\Packages\Package $package
 */
class Price extends Model
{
    const TYPE_SERVICE = 'service';
    const TYPE_HANDLING = 'handling';
    const TYPE_INSURANCE = 'insurance';
    const TYPE_PROMOTION = 'promotion';
    const TYPE_DISCOUNT = 'discount';
    const TYPE_OTHER = 'other';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'package_prices';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'package_id',
        'type',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
    ];

    /**
     * Get all available types.
     *
     * @return string[]
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_SERVICE,
            self::TYPE_HANDLING,
            self::TYPE_INSURANCE,
            self::TYPE_PROMOTION,
            self::TYPE_DISCOUNT,
            self::TYPE_OTHER,
        ];
    }

    /**
     * Define `belongsTo` relationship with Package model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
