<?php

namespace App\Contracts;

use App\Models\OneTimePassword;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface HasOtpToken.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OneTimePassword[] $one_time_passwords
 */
interface HasOtpToken
{
    /**
     * Define `morphMany` relationship with OneTimePassword model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function one_time_passwords(): MorphMany;

    /**
     * Get all active OTP.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activeOtp(): MorphMany;

    /**
     * Create new OTP token.
     *
     * @return \App\Models\OneTimePassword
     */
    public function createOtp(): OneTimePassword;
}
