<?php

namespace App\Models;

use App\Concerns\Models\CanSearch;
use Illuminate\Support\Arr;
use App\Contracts\HasOtpToken;
use App\Models\Partners\Partner;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use App\Concerns\Models\HasPhoneNumber;
use App\Concerns\Models\VerifiableByOtp;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * User instance.
 *
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $remember_token
 * @property \Carbon\Carbon|null $verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read UserablePivot|null pivot
 * @property-read  \Illuminate\Database\Eloquent\Collection partners
 * @property-read  bool $is_admin
 * @property-read  \Illuminate\Database\Eloquent\Collection transporters
 * @property-read  \Illuminate\Database\Eloquent\Collection deliveries
 * @method static  Builder partnerRole($types, $roles)
 */
class User extends Authenticatable implements HasOtpToken
{
    use HasFactory, Notifiable, HasApiTokens, HasPhoneNumber, VerifiableByOtp, SoftDeletes, HashableId, CanSearch;

    protected $table = 'users';

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
        'remember_token',
    ];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'deleted_at',
        'is_admin',
        'phone_verified_at',
        'email_verified_at',
        'verified_at',
        'created_at',
        'updated_at',
        'laravel_through_key',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @var array
     */
    protected $appends = ['hash'];

    /**
     * Set `password` attribute mutator.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function partners(): Relations\MorphToMany
    {
        return $this->morphedByMany(Partner::class, 'userable', 'userables')
            ->withPivot('id', 'role')
            ->withTimestamps()
            ->using(UserablePivot::class);
    }

    public function transporters(): Relations\MorphToMany
    {
        return $this->morphedByMany(Transporter::class, 'userable', 'userables')
            ->withPivot('id', 'role')
            ->withTimestamps()
            ->using(UserablePivot::class);
    }

    public function deliveries(): Relations\HasManyThrough
    {
        return $this->hasManyThrough(Delivery::class, UserablePivot::class, 'user_id', 'userable_id', 'id', 'id');
    }

    public function code_logs()
    {
        return $this->morphMany(CodeLogable::class, 'code_logable');
    }

    public function scopePartnerRole(Builder $builder, $types, $roles): void
    {
        $types = Arr::wrap($types);
        $roles = Arr::wrap($roles);

        $builder->whereHas(
            'partners',
            fn (Builder $builder) => $builder
                ->whereIn('userables.role', $roles)
                ->whereIn('type', $types)
        );
    }

    /**
     * Determine is the user has some role.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRoles($roles): bool
    {
        if (! $this->relationLoaded('partners')) {
            $this->load('partners');
        }

        return $this->partners->some(fn (Partner $partner) => in_array($partner->pivot->role, Arr::wrap($roles), true));
    }
}
