<?php

namespace App\Models\Customers;

use libphonenumber\PhoneNumberUtil;
use Illuminate\Auth\Authenticatable;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * Customer model.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property string $google_id
 * @property string $facebook_id
 * @property string $fcm_token
 * @property \Carbon\Carbon $verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Customers\Address[]|\Illuminate\Database\Eloquent\Collection $addresses
 */
class Customer extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use SoftDeletes,
        HashableId,
        Authenticatable,
        CanResetPassword,
        Notifiable,
        HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'google_id',
        'facebook_id',
        'fcm_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
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

    /**
     * Set `phone` number attribute mutator.
     *
     * @param $value
     *
     * @throws \libphonenumber\NumberParseException
     */
    public function setPhoneAttribute($value)
    {
        $util = PhoneNumberUtil::getInstance();
        $this->attributes['phone'] = $util->format($util->parse($value, 'ID'), PhoneNumberFormat::E164);
    }

    /**
     * Define `hasMany` relationship with Address model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'customer_id', 'id');
    }
}
