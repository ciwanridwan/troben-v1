<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateInvoiceFromPackage
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * GenerateInvoiceFromPackage constructor.
     *
     * @param \App\Models\Packages\Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }


    public function handle()
    {
        // TODO: handle invoice.
    }
}
