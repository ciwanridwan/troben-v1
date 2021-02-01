<?php

namespace App\Events\Geo;

use App\Models\Geo\SubDistrict;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class SubDistrictModified
{
    use Dispatchable, SerializesModels;

    /**
     * Sub District instance.
     *
     * @var \App\Models\Geo\SubDistrict
     */
    public SubDistrict $district;

    /**
     * Determine if the instance is new.
     *
     * @var bool
     */
    public bool $is_new;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Geo\SubDistrict $subDistrict
     * @param bool                     $isNew
     */
    public function __construct(SubDistrict $subDistrict, bool $isNew = false)
    {
        $this->district = $subDistrict;
        $this->is_new = $isNew;
    }
}
