<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Transporter model.
 *
 * @property int $id
 * @property int $partner_id
 * @property string $name
 * @property string $registration_number
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Partners\Partner $partner
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
        'length',
        'width',
        'height',
        'weight',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
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
                'lenght' => 40,
                'width' => 35,
                'height' => 55,
                'weight' => 40,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_MPV,
                'lenght' => 175,
                'width' => 100,
                'height' => 85,
                'weight' => 300,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_PICKUP,
                'lenght' => 210,
                'width' => 150,
                'height' => 120,
                'weight' => 700,
                'path_icons' => '',
            ],
            [
                'name' => self::TYPE_PICKUP_BOX,
                'lenght' => 200,
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
}
