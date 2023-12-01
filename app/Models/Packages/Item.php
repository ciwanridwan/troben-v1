<?php

namespace App\Models\Packages;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Code;
use App\Concerns\Models\HasCode;
use App\Casts\Package\Items\Handling;
use Illuminate\Database\Eloquent\Model;
use App\Actions\Pricing\PricingCalculator;
use Jalameta\Attachments\Concerns\Attachable;
use Jalameta\Attachments\Contracts\AttachableContract;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Package Item model.
 *
 * @property int $id
 * @property int $package_id
 * @property int $qty
 * @property string $name
 * @property float $price
 * @property string $desc
 * @property int $weight
 * @property int $height
 * @property int $length
 * @property int $width
 * @property bool $in_estimation
 * @property bool $is_insured
 * @property array|null $handling
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \App\Models\Packages\Package $package
 */
class Item extends Model implements AttachableContract
{
    use HashableId, HasCode, HasFactory, attachable, CustomSerializeDate;

    public const ATTACHMENT_PACKAGE_ITEM = 'package_item';
    public const TYPE_ITEM = 'item';
    public const TYPE_BIKE = 'bike';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'package_items';

    /**
     * @var string
     */
    protected $codeType = 'ITM';


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'package_id',
        'qty',
        'name',
        'desc',
        'weight',
        'height',
        'length',
        'width',
        'price',
        'in_estimation',
        'is_insured',
        'handling',
        'revision',
        'is_glassware',
        'category_item_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'qty' => 'int',
        'weight' => 'int',
        'height' => 'int',
        'length' => 'int',
        'width' => 'int',
        'price' => 'float',
        'in_estimation' => 'boolean',
        'is_insured' => 'boolean',
        'handling' => Handling::class,
        'is_glassware' => 'boolean'
    ];

    protected $hidden = [
        'id',
        'package_id',
    ];

    protected $appends = [
        'hash',
        'weight_volume',
        'weight_borne',
        'weight_borne_total',
        'weight_wood',
        'weight_original',
        'tier_price',
        'codeable'
    ];

    /**
     * Define `belongsTo` relationship with Package model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    /**
     * Get all of the prices for the Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(\App\Models\Packages\Price::class, 'package_item_id', 'id');
    }

    /**
     * @return MorphMany
     */
    public function codes(): MorphMany
    {
        return $this->morphMany(Code::class, 'codeable');
    }

    public function getWeightBorneAttribute()
    {
        $handling = $this->getHandling();
        if (in_array(Handling::TYPE_WOOD, $handling)) {
            return PricingCalculator::ceilByTolerance(Handling::woodWeightBorne($this->height, $this->length, $this->width, $this->weight, $this->getServiceCode()));
        }
        return PricingCalculator::getWeight($this->height, $this->length, $this->width, $this->weight, $this->getServiceCode());
    }

    public function getWeightBorneTotalAttribute()
    {
        $handling = $this->getHandling();
        return PricingCalculator::getWeightBorne($this->height, $this->length, $this->width, $this->weight, $this->qty, $handling);
    }

    public function getWeightVolumeAttribute()
    {
        $handling = $this->getHandling();
        // if (in_array(Handling::TYPE_WOOD, $handling)) {
        //     $add_dimension = Handling::ADD_WOOD_DIMENSION;
        //     return PricingCalculator::ceilByTolerance(PricingCalculator::getVolume($this->height + $add_dimension, $this->length + $add_dimension, $this->width + $add_dimension, $this->getServiceCode()));
        // }

        return PricingCalculator::ceilByTolerance(PricingCalculator::getVolume($this->height, $this->length, $this->width, $this->getServiceCode()));
    }

    public function getTierPriceAttribute()
    {
        $package = $this->package()->first();
        if (!is_null($package)) {
            $origin_province_id = $package->origin_regency->province_id;
            $origin_regency_id = $package->origin_regency_id;
            $destination_id = $package->destination_sub_district_id;
            try {
                $price = PricingCalculator::getPrice($origin_province_id, $origin_regency_id, $destination_id);
                $tierPrice = PricingCalculator::getTier($price, $this->WeightBorne);
            } catch (\Throwable $th) {
                $tierPrice = 0;
            }
            return $tierPrice;
        } else {
            return 0;
        }
    }

    public function getCodeableAttribute($value)
    {
        return ['qty' => $this->attributes['qty']];
    }

    /**Declare Type of item */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_ITEM,
            self::TYPE_BIKE
        ];
    }

    public function categories(): BelongsTo
    {
        return $this->belongsTo(CategoryItem::class, 'category_item_id', 'id');
    }

    private function getHandling()
    {
        return !empty($this->attributes['handling']) ? array_column(json_decode($this->attributes['handling']), 'type') : [];
    }

    /**
     * To get serviceCode from packages.
     * @return string $serviceCode
     */
    private function getServiceCode()
    {
        return  $this->package()->first() ? $this->package()->first()->service_code : null;
    }

    /**
     * get weight wood attribute
     */
    public function getWeightWoodAttribute()
    {
        $result = null;

        $handling = $this->getHandling();
        if (in_array(Handling::TYPE_WOOD, $handling)) {
            $result = Handling::woodWeightNew($this->weight, $this->height, $this->length, $this->width, $this->getServiceCode());
        }

        return $result;
    }

    /**
     * get weight wood attribute
     */
    public function getWeightOriginalAttribute()
    {
        $volume = PricingCalculator::ceilByTolerance(PricingCalculator::getVolume($this->height, $this->length, $this->width, $this->getServiceCode()));

        $result = [
            'height' => $this->height,
            'length' => $this->length,
            'width' => $this->width,
            'volume' => $volume,
        ];

        return $result;
    }
}
