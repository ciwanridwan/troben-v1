<?php

namespace App\Models\Partners;

use App\Models\User;
use App\Models\Deliveries\Delivery;
use App\Concerns\Models\HasPartnerCode;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Partner Model.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $contact_email
 * @property string $contact_phone
 * @property string $address
 * @property string $geo_location
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Partners\Warehouse[]|\Illuminate\Database\Eloquent\Collection $warehouses
 * @property-read \App\Models\Partners\Transporter[]|\Illuminate\Database\Eloquent\Collection $transporters
 * @property-read  \App\Models\User[]|\Illuminate\Database\Eloquent\Collection $users
 * @property  UserablePivot pivot
 */
class Partner extends Model
{
    use SoftDeletes,
        HashableId,
        HasPhoneNumber,
        HasFactory,
        HashableId,
        HasPartnerCode;

    const TYPE_BUSINESS = 'business'; // bisa order dari application.
    const TYPE_POOL = 'pool';
    const TYPE_SPACE = 'space';
    const TYPE_TRANSPORTER = 'transporter';
    const CODE_TYPE_BUSINESS = 'MB'; // bisa order dari application.
    const CODE_TYPE_POOL = 'MPW';
    const CODE_TYPE_SPACE = 'MS';
    const CODE_TYPE_TRANSPORTER = 'MTM';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'partners';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'code',
        'contact_email',
        'contact_phone',
        'geo_province_id',
        'geo_regency_id',
        'geo_district_id',
        'geo_sub_district_id',
        'address',
        'geo_location',
        'type',
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
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
     * Phone Number Column.
     *
     * @var string
     */
    protected string $phoneNumberColumn = 'contact_phone';

    /**
     * Get partner types.
     *
     * @return string[]
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_BUSINESS,
            self::TYPE_POOL,
            self::TYPE_SPACE,
            self::TYPE_TRANSPORTER,
        ];
    }
    public static function getAvailableCodeTypes(): array
    {
        return [
            self::TYPE_BUSINESS => self::CODE_TYPE_BUSINESS,
            self::TYPE_POOL => self::CODE_TYPE_POOL,
            self::TYPE_SPACE => self::CODE_TYPE_SPACE,
            self::TYPE_TRANSPORTER => self::CODE_TYPE_TRANSPORTER,
        ];
    }

    /**
     * Define `hasMany` relationship with Warehouse model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'partner_id', 'id');
    }

    /**
     * Define `hasMany` relationship with Transporter model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transporters(): HasMany
    {
        return $this->hasMany(Transporter::class, 'partner_id', 'id');
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

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'partner_id', 'id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'partner_id', 'id');
    }
}
