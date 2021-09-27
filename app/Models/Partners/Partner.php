<?php

namespace App\Models\Partners;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use App\Models\Partners\Balance\History;
use App\Models\User;
use App\Models\Deliveries\Delivery;
use App\Concerns\Models\HasPartnerCode;
use App\Concerns\Models\HasPhoneNumber;
use App\Models\CodeLogable;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use App\Models\Geo\Regency;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Partner Model.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $contact_email
 * @property string $contact_phone
 * @property string $address
 * @property string $latitude
 * @property string $longitude
 * @property string $geo_location
 * @property string $type
 * @property float $balance
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read Warehouse[]|\Illuminate\Database\Eloquent\Collection $warehouses
 * @property-read \App\Models\Geo\Regency|null  $regency
 * @property-read Transporter[]|\Illuminate\Database\Eloquent\Collection $transporters
 * @property-read  User[]|\Illuminate\Database\Eloquent\Collection $users
 * @property  UserablePivot pivot
 * @property  int geo_province_id
 * @property  int geo_regency_id
 * @property  int geo_district_id
 * @property  int geo_sub_district_id
 */
class Partner extends Model
{
    use SoftDeletes,
        HashableId,
        HasPhoneNumber,
        HasFactory,
        HashableId,
        HasPartnerCode,
        CanSearch, CustomSerializeDate;

    public const TYPE_BUSINESS = 'business'; // bisa order dari application.
    public const TYPE_POOL = 'pool';
    public const TYPE_SPACE = 'space';
    public const TYPE_TRANSPORTER = 'transporter';
    public const CODE_TYPE_BUSINESS = 'MB'; // bisa order dari application.
    public const CODE_TYPE_POOL = 'MPW';
    public const CODE_TYPE_SPACE = 'MS';
    public const CODE_TYPE_TRANSPORTER = 'MTM';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'partners';

    protected $search_columns = [
        'name',
        'code',
        'contact_email',
        'contact_phone',
        'address',
        'type',
        'created_at'
    ];

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
        'latitude',
        'longitude',
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
        'geo_address',
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
    public function warehouses(): Relations\HasMany
    {
        return $this->hasMany(Warehouse::class, 'partner_id', 'id');
    }

    /**
     * Define `hasMany` relationship with Transporter model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transporters(): Relations\HasMany
    {
        return $this->hasMany(Transporter::class, 'partner_id', 'id');
    }

    /**
     * Define `morphToMany` relationship with User Model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): Relations\MorphToMany
    {
        return $this->morphToMany(User::class, 'userable', 'userables')
            ->withPivot('id', 'role')
            ->withTimestamps()
            ->using(UserablePivot::class);
    }

    public function owner(): Relations\MorphToMany
    {
        return $this->users()->wherePivot('role', UserablePivot::ROLE_OWNER);
    }

    public function drivers(): Relations\MorphToMany
    {
        return $this->users()->wherePivot('role', UserablePivot::ROLE_DRIVER);
    }

    public function inventories(): Relations\HasMany
    {
        return $this->hasMany(Inventory::class, 'partner_id', 'id');
    }

    public function deliveries(): Relations\HasMany
    {
        return $this->hasMany(Delivery::class, 'partner_id', 'id');
    }

    public function outbound(): Relations\HasMany
    {
        return $this->hasMany(Delivery::class, 'origin_partner_id', 'id');
    }

    public function code_logs()
    {
        return $this->morphMany(CodeLogable::class, 'code_logable');
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
     * Define `belongsTo` relationship with City model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'geo_regency_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Province model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'geo_province_id', 'id');
    }


    /**
     * @return BelongsTo
     */
    public function sub_district(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'district_id', 'id');
    }

    /**
     * Define 'hasMay' relationship with partner balance history model.
     *
     * @return Relations\HasMany
     */
    public function balance_history(): Relations\HasMany
    {
        return $this->hasMany(History::class, 'partner_id', 'id');
    }

    public function getGeoAddressAttribute()
    {
        $sub_district = $this->sub_district ? $this->sub_district->name : '';
        $district = $this->district ? $this->district->name : '';
        $regency = $this->regency ? $this->regency->name : '';
        $province = $this->province ? $this->province->name : '';
        $address = "$this->address, $sub_district $district $regency $province";
        $address .= $this->sub_district ? $this->zip_code : '';
        return $address;
    }

    /**
     * Validate partner in jabodetabek.
     *
     * @return bool
     */
    public function isJabodetabek(): bool
    {
        return in_array($this->geo_regency_id, Regency::getJabodetabekId());
    }
}
