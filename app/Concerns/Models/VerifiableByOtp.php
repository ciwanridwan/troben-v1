<?php

namespace App\Concerns\Models;

use Carbon\Carbon;
use App\Models\OneTimePassword;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        return $this->morphToMany(OneTimePassword::class, 'verifiable');
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
}
