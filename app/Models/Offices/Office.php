<?php

namespace App\Models\Offices;

use App\Concerns\Models\CanSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContact;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Class Office.
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $username
 * @property string $email
 * @property string $phone
 * @property bool $is_active
 * @property string $password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read  \Illuminate\Database\Eloquent\Collection role
 * @property-read  \Illuminate\Database\Eloquent\Collection permission
 */
class Office extends Authenticatable implements AuthenticatableContact
{
    use HasFactory,
        SoftDeletes,
        HashableId,
        CanSearch;

    protected $table = 'offices';
    protected string $guard_name = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'address',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hash',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function permission(): MorphMany
    {
        return $this->morphMany(Permissionable::class, 'permission');
    }
}
