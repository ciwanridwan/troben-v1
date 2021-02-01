<?php

namespace App\Events\Geo;

use App\Models\Geo\Country;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class CountryModified
{
    use Dispatchable, SerializesModels;

    /**
     * Country instance.
     *
     * @var \App\Models\Geo\Country
     */
    public Country $country;

    /**
     * Determine if the instance is new.
     *
     * @var bool
     */
    public bool $is_new;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Geo\Country $country
     * @param bool                    $isNew
     */
    public function __construct(Country $country, bool $isNew = false)
    {
        $this->country = $country;
        $this->is_new = $isNew;
    }
}
