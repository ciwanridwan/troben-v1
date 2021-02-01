<?php

namespace App\Events\Geo;

use App\Models\Geo\Regency;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class RegencyModified
{
    use Dispatchable, SerializesModels;

    /**
     * Regency instance.
     *
     * @var \App\Models\Geo\Regency
     */
    public Regency $regency;

    /**
     * Determine if the instance is new.
     *
     * @var bool
     */
    public bool $is_new;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Geo\Regency $regency
     * @param bool                    $isNew
     */
    public function __construct(Regency $regency, bool $isNew = false)
    {
        $this->regency = $regency;
        $this->is_new = $isNew;
    }
}
