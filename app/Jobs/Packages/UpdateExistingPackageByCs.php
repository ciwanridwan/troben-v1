<?php

namespace App\Jobs\Packages;

use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Events\Packages\CustomerServices\PackageUpdatedByCs;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UpdateExistingPackageByCs
{
    use Dispatchable;

    
    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    private array $attributes;
    private ?bool $isSeparated;

    /**
     * UpdateExistingPackage constructor.
     * @param \App\Models\Packages\Package $package
     * @param array $inputs
     * @param bool $isSeparated
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Package $package, array $inputs)
    {
        $this->package = $package;

        unset($inputs['items'], $inputs['order_type']);
        $this->attributes = $inputs;
        
    }

    public function handle()
    {
        $this->package->fill($this->attributes);

        Log::info('update-package', [
            'request' => request()->all(),
            'attributes' => $this->attributes,
            'changes' => $this->package->getChanges()
        ]);

        $this->package->save();

        event(new PackageUpdatedByCs($this->package));
    }
}
