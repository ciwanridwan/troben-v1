<?php

namespace App\Models\Partners;

use App\Models\User;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
 */
class Transporter extends Model
{
    use SoftDeletes,
        HashableId,
        HasFactory;

    const TYPE_BIKE = 'bike';
    const TYPE_MPV = 'mpv';
    const TYPE_PICKUP = 'pickup';
    const TYPE_PICKUP_BOX = 'pickup box';
    const TYPE_CDE_ENGKEL = 'cde engkel';
    const TYPE_CDE_ENGKEL_BOX = 'engkel box';
    const TYPE_CDE_ENGKEL_DOUBLE = 'engkel double';
    const TYPE_CDE_ENGKEL_DOUBLE_BOX = 'engkel double box';
    const TYPE_FUSO_6M = 'fuso 6m';
    const TYPE_FUSO_9M = 'fuso 9m';
    const TYPE_TRONTON = 'tronton';
    const TYPE_WINGBOX = 'wingbox';
    const TYPE_VAN = 'van';

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
            self::TYPE_FUSO_6M,
            self::TYPE_FUSO_9M,
            self::TYPE_TRONTON,
            self::TYPE_WINGBOX,
            self::TYPE_VAN,
        ];
    }

    /**
     * Get detail for transporter types.
     *
     * @return array
     */
    public static function getDetailAvailableTypes()
    {
        return [
            [
                'name' => self::TYPE_BIKE,
                'length' => 40,
                'width' => 35,
                'height' => 55,
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
                'height' => 120,
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
        return $this->users()->where('userables.role', UserablePivot::ROLE_DRIVER);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'transporter_id', 'id');
    }
}
