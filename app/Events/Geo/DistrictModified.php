<?php

namespace App\Events\Geo;

use App\Models\Geo\District;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class DistrictModified
{
    use Dispatchable, SerializesModels;

    /**
     * District instance.
     *
     * @var \App\Models\Geo\District
     */
    public District $district;

    /**
     * Determine if the instance is new.
     *
     * @var bool
     */
    public bool $is_new;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Geo\District $district
     * @param bool                     $isNew
     */
    public function __construct(District $district, bool $isNew = false)
    {
        $this->district = $district;
        $this->is_new = $isNew;
    }
}
