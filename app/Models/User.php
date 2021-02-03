<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Notifications\Notifiable;
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
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasPhoneNumber;

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
     * Set `password` attribute mutator.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
