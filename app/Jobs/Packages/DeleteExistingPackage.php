<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Package;
use App\Events\Packages\PackageWasDeleted;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteExistingPackage
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * DeleteExistingPackage constructor.
     *
     * @param \App\Models\Packages\Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Handle the job.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        $deleted = (bool) $this->package->delete();
        if ($deleted) {
            event(new PackageWasDeleted($this->package));
        }

        return $deleted;
    }
}
