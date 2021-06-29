<?php

use App\Models\Code;
use App\Concerns\Models\HasCode;
use App\Casts\Package\Items\Handling;
use Illuminate\Database\Eloquent\Model;
use App\Actions\Pricing\PricingCalculator;
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
class Item extends Model
{
    use HashableId, HasCode, HasFactory;

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
        'tier_price'
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
        return $this->hasMany(Price::class, 'package_item_id', 'id');
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
        $weight = PricingCalculator::getWeight($this->height, $this->length, $this->width, $this->weight);
        return $weight;
    }

    public function getWeightBorneTotalAttribute()
    {
        $weight = PricingCalculator::getWeightBorne($this->height, $this->length, $this->width, $this->weight, $this->qty);
        return $weight;
    }
    public function getWeightVolumeAttribute()
    {
        $volume = PricingCalculator::ceilByTolerance(PricingCalculator::getVolume($this->height, $this->length, $this->width));
        return $volume;
    }
    public function getTierPriceAttribute()
    {
        $package = $this->package()->first();
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
    }
}
