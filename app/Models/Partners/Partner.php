<?php

namespace App\Models\Partners;

use App\Models\User;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 */
class Partner extends Model
{
    use SoftDeletes, HashableId, HasPhoneNumber;

    const TYPE_BUSINESS = 'business';
    const TYPE_POOL = 'pool';
    const TYPE_SPACE = 'space';
    const TYPE_TRANSPORTER = 'transporter';


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
        'address',
        'geo_location',
        'type',
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
            ->using(UserablePivot::class);
    }
}
