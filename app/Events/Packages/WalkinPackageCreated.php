<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class WalkinPackageCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;
    public string $partner_code;

    /**
     * PackageCreated constructor.
     *
     * @param \App\Models\Packages\Package $package
     */
    public function __construct(Package $package, string $partner_code)
    {
        $this->package = $package;
        $this->partner_code = $partner_code;
    }
}
