<?php

namespace App\Models\Partners;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use App\Models\User;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

/**
 * Transporter model.
 *
 * @property int $id
 * @property int $partner_id
 * @property string $registration_name
 * @property string $registration_number
 * @property int $production_year
 * @property int $registration_year
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Partners\Partner $partner
 * @property-read  null|UserablePivot pivot
 * @property-read  \Illuminate\Database\Eloquent\Collection drivers
 */
class Transporter extends Model
{
    use SoftDeletes,
        HashableId,
        HasFactory,
        CanSearch,
        CustomSerializeDate;

    public const GENERAL_TYPE_BIKE = 'bike';
    public const GENERAL_TYPE_CAR = 'car';

    public const TYPE_BIKE = 'bike';
    public const TYPE_MPV = 'mpv';
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_PICKUP_BOX = 'pickup box';
    public const TYPE_CDE_ENGKEL = 'cde engkel';
    public const TYPE_CDE_ENGKEL_BOX = 'engkel box';
    public const TYPE_CDE_ENGKEL_DOUBLE = 'engkel double';
    public const TYPE_CDE_ENGKEL_DOUBLE_BOX = 'engkel double box';
    public const TYPE_CDE_ENGKEL_BAK = 'cde engkel bak';
    public const TYPE_CDD_DOUBLE_BAK = 'cdd double bak';
    public const TYPE_CDD_DOUBLE_BOX = 'cdd double box';
    public const TYPE_FUSO_BOX = 'fuso box';
    public const TYPE_FUSO_BAK = 'fuso bak';
    public const TYPE_FUSO_6M = 'fuso 6m';
    public const TYPE_FUSO_9M = 'fuso 9m';
    public const TYPE_TRONTON = 'tronton';
    public const TYPE_WINGBOX = 'wingbox';
    public const TYPE_VAN = 'van';

    public const PRICE_BIKE = 10000;
    public const PRICE_CAR = 25000;



    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transporters';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'partner_id',
        'production_year',
        'registration_name',
        'registration_number',
        'registration_year',
        'type',
        'is_verified',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'partner_id',
        'is_verified',
        'verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'laravel_through_key',
    ];


    /**
     * Get transporter types.
     *
     * @return string[]
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_BIKE,
            self::TYPE_MPV,
            self::TYPE_PICKUP,
            self::TYPE_PICKUP_BOX,
            self::TYPE_CDE_ENGKEL,
            self::TYPE_CDE_ENGKEL_BOX,
            self::TYPE_CDE_ENGKEL_DOUBLE,
            self::TYPE_CDE_ENGKEL_DOUBLE_BOX,
            self::TYPE_CDE_ENGKEL_BAK,
            self::TYPE_CDD_DOUBLE_BAK,
            self::TYPE_CDD_DOUBLE_BOX,
            self::TYPE_FUSO_BOX,
            self::TYPE_FUSO_BAK,
            self::TYPE_FUSO_6M,
            self::TYPE_FUSO_9M,
            self::TYPE_TRONTON,
            self::TYPE_WINGBOX,
            self::TYPE_VAN,
        ];
    }

    public static function getAvailableCubicTypes(): array
    {
        return [
            self::TYPE_PICKUP,
            self::TYPE_PICKUP_BOX,
            self::TYPE_CDE_ENGKEL_BOX,
            self::TYPE_CDE_ENGKEL_BAK,
            self::TYPE_CDD_DOUBLE_BAK,
            self::TYPE_CDD_DOUBLE_BOX,
            self::TYPE_FUSO_BAK
        ];
    }

    public static function getGeneralType($type)
    {
        foreach (self::getAvailableGeneralTypes() as $key => $value) {
            if (in_array($type, $value)) {
                return $key;
            }
        }
    }
    public static function getGeneralTypePrice($type)
    {
        $general_type = self::getGeneralType($type);
        if (! $general_type) {
            return 0;
        }
        return self::getAvailableTransporterPrices()[$general_type];
    }

    public static function getAvailableGeneralTypes()
    {
        return [
            self::GENERAL_TYPE_BIKE => [
                self::TYPE_BIKE
            ],
            self::GENERAL_TYPE_CAR => [
                self::TYPE_MPV,
                self::TYPE_PICKUP,
                self::TYPE_PICKUP_BOX,
                self::TYPE_CDE_ENGKEL,
                self::TYPE_CDE_ENGKEL_BOX,
                self::TYPE_CDE_ENGKEL_DOUBLE,
                self::TYPE_CDE_ENGKEL_DOUBLE_BOX,
                self::TYPE_CDE_ENGKEL_BAK,
                self::TYPE_CDD_DOUBLE_BAK,
                self::TYPE_CDD_DOUBLE_BOX,
                self::TYPE_FUSO_BOX,
                self::TYPE_FUSO_BAK,
                self::TYPE_FUSO_6M,
                self::TYPE_FUSO_9M,
                self::TYPE_TRONTON,
                self::TYPE_WINGBOX,
                self::TYPE_VAN,
            ]
        ];
    }

    /**
     * get transporter price by general type on promo.
     *
     * @return int[]
     */
    public static function getAvailableTransporterPrices(): array
    {
        return [
            self::GENERAL_TYPE_BIKE => 10000,
            self::GENERAL_TYPE_CAR => 25000
        ];
    }

    /**
     * Get detail for transporter types.
     *
     * @return array
     */
    public static function getDetailAvailableTypes(): array
    {
        return [
            [
                'name' => self::TYPE_BIKE,
                'length' => 55,
                'width' => 40,
                'height' => 35,
                'weight' => 40,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_MPV,
                'length' => 175,
                'width' => 100,
                'height' => 85,
                'weight' => 300,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_PICKUP,
                'length' => 210,
                'width' => 150,
                'height' => 150,
                'weight' => 700,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_PICKUP_BOX,
                'length' => 200,
                'width' => 130,
                'height' => 120,
                'weight' => 1000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDE_ENGKEL_BOX,
                'length' => 370,
                'width' => 169,
                'height' => 200,
                'weight' => 2500,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDE_ENGKEL_BAK,
                'length' => 370,
                'width' => 169,
                'height' => 212,
                'weight' => 2500,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDD_DOUBLE_BAK,
                'length' => 530,
                'width' => 200,
                'height' => 200,
                'weight' => 5000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDD_DOUBLE_BOX,
                'length' => 530,
                'width' => 200,
                'height' => 220,
                'weight' => 5000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_FUSO_BAK,
                'length' => 630,
                'width' => 210,
                'height' => 200,
                'weight' => 5000,
                'path_icons' => '',
            ]
        ];
    }

    public static function getDetailCubicTypes(): array
    {
        return [
            [
                'name' => self::TYPE_PICKUP,
                'length' => 210,
                'width' => 150,
                'height' => 150,
                'weight' => 700,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_PICKUP_BOX,
                'length' => 200,
                'width' => 130,
                'height' => 120,
                'weight' => 1000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDE_ENGKEL_BOX,
                'length' => 370,
                'width' => 169,
                'height' => 200,
                'weight' => 2500,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDE_ENGKEL_BAK,
                'length' => 370,
                'width' => 169,
                'height' => 212,
                'weight' => 2500,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDD_DOUBLE_BAK,
                'length' => 530,
                'width' => 200,
                'height' => 200,
                'weight' => 5000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDD_DOUBLE_BOX,
                'length' => 530,
                'width' => 200,
                'height' => 220,
                'weight' => 5000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_FUSO_BAK,
                'length' => 630,
                'width' => 210,
                'height' => 200,
                'weight' => 5000,
                'path_icons' => '',
            ]
        ];
    }

    /**
     * get rates available by type.
     *
     * @return int[][]
     */
    public static function getAvailableRatesByType(): array
    {
        return [
            self::TYPE_BIKE => [
                'start_price' => 10000,
                'rate_price' => 2500,
                'start_rate' => 5,
            ],
            self::TYPE_MPV => [
                'start_price' => 25000,
                'rate_price' => 5000,
                'start_rate' => 5,
            ],
            self::TYPE_PICKUP => [
                'start_price' => 50000,
                'rate_price' => 7000,
                'start_rate' => 5,
            ],
            self::TYPE_PICKUP_BOX => [
                'start_price' => 75000,
                'rate_price' => 7500,
                'start_rate' => 5,
            ],
            self::TYPE_CDE_ENGKEL_BOX => [
                'start_price' => 250000,
                'rate_price' => 7500,
                'start_rate' => 5,
            ],
            self::TYPE_CDE_ENGKEL_BAK => [
                'start_price' => 250000,
                'rate_price' => 7500,
                'start_rate' => 5,
            ],
            self::TYPE_CDD_DOUBLE_BAK => [
                'start_price' => 500000,
                'rate_price' => 7500,
                'start_rate' => 5,
            ],
            self::TYPE_CDD_DOUBLE_BOX => [
                'start_price' => 500000,
                'rate_price' => 10000,
                'start_rate' => 5,
            ],
            self::TYPE_FUSO_BAK => [
                'start_price' => 850000,
                'rate_price' => 15000,
                'start_rate' => 5,
            ],
        ];
    }

    /**
     * Define `belongsTo` relationship with partner model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }

    /**
     * Define `morphToMany` relationship with User Model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphToMany(User::class, 'userable', 'userables')
            ->withPivot('id', 'role')
            ->withTimestamps()
            ->using(UserablePivot::class);
    }

    public function drivers(): MorphToMany
    {
        return $this->users()->wherePivot('role', UserablePivot::ROLE_DRIVER);
    }

    public function deliveries(): HasManyThrough
    {
        $morphAlias = array_flip(Relation::$morphMap)[self::class] ?? self::class;

        return $this->hasManyThrough(Delivery::class, UserablePivot::class, 'userable_id', 'userable_id', 'id', 'id')
            ->where('userables.userable_type', $morphAlias);
    }

    public static function getTranporterOfBike(): array
    {
        return [
            self::TYPE_PICKUP,
            self::TYPE_PICKUP_BOX,
            self::TYPE_CDE_ENGKEL_BOX,
            self::TYPE_CDE_ENGKEL_BAK,
            self::TYPE_CDD_DOUBLE_BAK,
            self::TYPE_CDD_DOUBLE_BOX
        ];
    }

    public static function getDetailTransporterOfBike(): array
    {
        $url = asset('storage/app/icons/pickup.png');
        return [
            [
                'name' => self::TYPE_PICKUP,
                'length' => 210,
                'width' => 150,
                'height' => 150,
                'weight' => 700,
                // 'path_icons' => storage_path('app/icons/pickup.png'),
                'path_icons' => $url
            ],
            [
                'name' => self::TYPE_PICKUP_BOX,
                'length' => 200,
                'width' => 130,
                'height' => 120,
                'weight' => 1000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDE_ENGKEL_BOX,
                'length' => 370,
                'width' => 169,
                'height' => 200,
                'weight' => 2500,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDE_ENGKEL_BAK,
                'length' => 370,
                'width' => 169,
                'height' => 212,
                'weight' => 2500,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDD_DOUBLE_BAK,
                'length' => 530,
                'width' => 200,
                'height' => 200,
                'weight' => 5000,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_CDD_DOUBLE_BOX,
                'length' => 530,
                'width' => 200,
                'height' => 220,
                'weight' => 5000,
                'path_icons' => '',
            ]
        ];
    }
}
