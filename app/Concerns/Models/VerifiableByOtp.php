<?php

namespace App\Concerns\Models;

use Carbon\Carbon;
use App\Models\OneTimePassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait VerifiableByOtp.
 *
 * @property-read bool $is_verified
 *
 * @method \Illuminate\Database\Eloquent\Builder verified()
 */
trait VerifiableByOtp
{
    /**
     * Define `morphMany` relationship with OneTimePassword model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function one_time_passwords(): MorphMany
    {
        /** @var \Illuminate\Database\Eloquent\Model $this */
        return $this->morphMany(OneTimePassword::class, 'verifiable');
    }

    /**
     * Create new OTP token.
     *
     * @return \App\Models\OneTimePassword
     */
    public function createOtp(): OneTimePassword
    {
        $otp = $this->one_time_passwords()->create([
            'token' => $this->generateRandomOtpToken(),
        ]);

        /** @var \App\Models\OneTimePassword $otp */
        return $otp;
    }

    /**
     * Get all active OTP.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activeOtp(): MorphMany
    {
        return $this->one_time_passwords()->where('expired_at', '>', Carbon::now());
    }

    /**
     * scope query with verified column.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified(Builder $builder): Builder
    {
        return $builder->whereNotNull($this->getVerifiedColumn());
    }

    /**
     * Get `is_verified` attribute.
     *
     * @return bool
     */
    public function getIsVerifiedAttribute(): bool
    {
        return ! empty($this->{$this->getVerifiedColumn()});
    }

    /**
     * Get verified column.
     *
     * @return string
     */
    protected function getVerifiedColumn(): string
    {
        return property_exists($this, 'verifiedColumn')
            ? $this->verifiedColumn
            : 'verified_at';
    }

    /**
     * Generate random otp token.
     *
     * @return false|string
     */
    protected function generateRandomOtpToken()
    {
        return in_array(env('APP_ENV'), ['local', 'staging'])
            ? '123456'
            : substr(str_shuffle(str_repeat('0123456789', 5)), 0, 6);
    }
}
