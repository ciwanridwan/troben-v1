<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Models\UuidAsPrimaryKey;

class OneTimePassword extends Model
{
    use UuidAsPrimaryKey;

    const TOKEN_TTL = 1800;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'one_time_passwords';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'verifiable_type',
        'verifiable_id',
        'token',
        'claimed_at',
        'sent_with',
        'sent_ref_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'claimed_at' => 'datetime',
        'expired_at' => 'datetime',
        'token' => 'string',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::creating(fn (self $self) => $this->attributes['expired_at'] = Carbon::now()->addSeconds(self::TOKEN_TTL));
    }

    /**
     * Set `token` attribute mutator.
     *
     * @param $value
     */
    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = app('encrypter')->encrypt($value);
    }

    /**
     * Get `token` attribute accessor.
     *
     * @param $value
     *
     * @return string
     */
    public function getTokenAttribute($value): string
    {
        return app('encrypter')->decrypt($value);
    }
}
