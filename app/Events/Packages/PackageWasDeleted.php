<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PackageWasDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * PackageDeletion constructor.
     *
     * @param \App\Models\Packages\Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }
}
