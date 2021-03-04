<?php

namespace App\Models;

use App\Contracts\HasOtpToken;
use App\Models\Partners\Partner;
use Laravel\Sanctum\HasApiTokens;
use App\Concerns\Models\HasPhoneNumber;
use App\Concerns\Models\VerifiableByOtp;
use Illuminate\Notifications\Notifiable;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Veelasky\LaravelHashId\Eloquent\HashableId;

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
 */
class User extends Authenticatable implements HasOtpToken
{
    use HasFactory, Notifiable, HasApiTokens, HasPhoneNumber, VerifiableByOtp, SoftDeletes, HashableId;

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
        'password',
        'remember_token',
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

    public function partners()
    {
        return $this->morphedByMany(Partner::class, 'userable', 'userables')->using(UserablePivot::class);
    }
}
