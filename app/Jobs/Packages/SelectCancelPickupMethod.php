<?php

namespace App\Jobs\Packages;

use App\Events\Packages\PackageCancelMethodSelected;
use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SelectCancelPickupMethod
{
    use Dispatchable;

    public Package $package;

    protected array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package $package, array $inputs = [])
    {
        $this->attributes = Validator::validate($inputs, [
            'pickup_method' => ['required', Rule::in(Package::getAvailableCancelPickupMethod())]
        ]);
        $this->package =  $package;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->package->setAttribute('status', $this->attributes['pickup_method'])->save();
        event(new PackageCancelMethodSelected($this->package));
        return $this->package->exists;
    }
}
