<?php

namespace App\Models\Customers;

use App\Models\Orders\Order;
use App\Contracts\HasOtpToken;
use Laravel\Sanctum\HasApiTokens;
use App\Auditor\Concerns\Auditable;
use Illuminate\Auth\Authenticatable;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Models\VerifiableByOtp;
use Illuminate\Notifications\Notifiable;
use App\Auditor\Contracts\AuditableContract;
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
class Customer extends Model implements AuthenticatableContract, CanResetPasswordContract, HasOtpToken, AuditableContract
{
    use SoftDeletes,
        HashableId,
        Authenticatable,
        CanResetPassword,
        Notifiable,
        HasPhoneNumber,
        VerifiableByOtp,
        HasApiTokens,
        HasFactory,
        Auditable;

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

    protected $verifiedColumn = 'phone_verified_at';

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
     * Define `hasMany` relationship with Address model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'customer_id', 'id');
    }

    /**
     * Get all of the orders for the Customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
}
