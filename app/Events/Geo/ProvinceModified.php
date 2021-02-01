<?php

namespace App\Events\Geo;

use App\Models\Geo\Province;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ProvinceModified
{
    use Dispatchable, SerializesModels;

    /**
     * Province instance.
     *
     * @var \App\Models\Geo\Province
     */
    public Province $province;

    /**
     * Determine if the instance is new.
     *
     * @var bool
     */
    public bool $is_new;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Geo\Province $province
     * @param bool                     $isNew
     */
    public function __construct(Province $province, bool $isNew = false)
    {
        $this->province = $province;
        $this->is_new = $isNew;
    }
}
