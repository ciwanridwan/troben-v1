<?php

namespace App\Models\Customers;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Contracts\HasOtpToken;
use App\Models\Notifications\Notification;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Jalameta\Attachments\Concerns\Attachable;
use Jalameta\Attachments\Contracts\AttachableContract;
use Laravel\Sanctum\HasApiTokens;
use App\Auditor\Concerns\Auditable;
use Illuminate\Auth\Authenticatable;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Models\VerifiableByOtp;
use Illuminate\Notifications\Notifiable;
use App\Auditor\Contracts\AuditableContract;
use App\Models\CodeLogable;
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
 * @property string $address
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
class Customer extends Model implements AttachableContract, AuthenticatableContract, CanResetPasswordContract, HasOtpToken, AuditableContract
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
        Auditable,
        attachable,
        CustomSerializeDate;

    public const ATTACHMENT_PHOTO_PROFILE = 'avatar';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';
    protected string $guard_name = 'jwt';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
        'google_id',
        'facebook_id',
        'fcm_token',
    ];

    protected $verifiedColumn = 'phone_verified_at';

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
        'verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
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

    public function code_logs()
    {
        return $this->morphMany(CodeLogable::class, 'code_logable');
    }

    /**
     * Get all of the orders for the Customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'customer_id', 'id');
    }

    /**
     * Define 'MorphMany' relations with notification.
     *
     * @return MorphMany
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class,'notifiable');
    }
}
