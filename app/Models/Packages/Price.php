<?php

namespace App\Models\Packages;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Package price model.
 *
 * @property int $id
 * @property int $package_id
 * @property string $type
 * @property string $description
 * @property float $amount
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \App\Models\Packages\Package $package
 */
class Price extends Model
{
    use CustomSerializeDate;
    public const TYPE_ADDITIONAL = 'additional';
    public const TYPE_SERVICE = 'service';
    public const TYPE_HANDLING = 'handling';
    public const TYPE_INSURANCE = 'insurance';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_DISCOUNT = 'discount';
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_OTHER = 'other';
    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_PLATFORM = 'platform';
    public const DESCRIPTION_TYPE_BIKE = 'bike';
    public const DESCRIPTION_TYPE_WOOD = 'wood';
    public const DESCRIPTION_TYPE_CUBIC = 'kubikasi';
    public const DESCRIPTION_TYPE_EXPRESS = 'express';
    public const DESCRIPTION_PLATFORM_FEE = 'fee';
    
    public const FEE_PLATFORM = 3000;
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
        'package_item_id',
        'type',
        'description',
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

    protected $hidden = [
        'id',
        'package_id',
        'package_item_id',
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
            self::TYPE_PICKUP,
            self::TYPE_OTHER,
            self::TYPE_DELIVERY,
            self::TYPE_ADDITIONAL,
            self::TYPE_PLATFORM,
            self::DESCRIPTION_TYPE_BIKE,
            self::DESCRIPTION_TYPE_WOOD,
            self::DESCRIPTION_TYPE_CUBIC,
            self::DESCRIPTION_TYPE_EXPRESS
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

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'package_item_id', 'id');
    }
}
