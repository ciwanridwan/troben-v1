<?php

namespace App\Jobs\Operations;

use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class CancelPackage
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
                'status' => ['exists:packages,status']
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
        $this->attributes['status'] = Package::STATUS_CANCEL;
        $this->package->fill($this->attributes);
        $this->package->save();

        return $this->package->exists;
    }
}
