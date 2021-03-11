<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateExistingPackage
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * Package attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Package item attributes.
     *
     * @var array
     */
    protected array $items;

    public function __construct(Package $package, array $attributes, array $items)
    {
        $this->package = $package;
    }
}
