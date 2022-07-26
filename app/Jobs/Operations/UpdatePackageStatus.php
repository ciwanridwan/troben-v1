<?php

namespace App\Jobs\Operations;

use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class UpdatePackageStatus
{
    use Dispatchable;

    /**
     * The podcast instance.
     *
     * @var \App\Models\Packages\Package
     */
    public $package;

    private array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package $package, array $inputs)
    {
        $this->package = $package;

        $this->attributes = Validator::make(
            $inputs,
            [
                'status' => ['required', 'exists:packages,status'],
                'payment_status' => ['nullable', 'exists:packages,payment_status'],
                'estimator_id' => ['nullable', 'exists:users,id'],
                'is_onboard' => ['nullable', 'exists:deliverables,is_onboard'],
            ]
        )->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->package->fill($this->attributes);
        $this->package->save();

        return $this->package->exists;
    }
}
