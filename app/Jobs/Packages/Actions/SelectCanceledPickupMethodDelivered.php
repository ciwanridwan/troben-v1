<?php

namespace App\Jobs\Packages\Actions;

use App\Jobs\Packages\SelectCancelPickupMethod;
use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;

class SelectCanceledPickupMethodDelivered
{
    use Dispatchable;

    public Package $package;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $job = new SelectCancelPickupMethod($this->package, [
            'pickup_method' => Package::STATUS_CANCEL_DELIVERED
        ]);
        dispatch_now($job);
        $this->package = $job->package;
        return $this->package->exists;
    }
}
