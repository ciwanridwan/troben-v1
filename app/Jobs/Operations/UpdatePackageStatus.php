<?php

namespace App\Jobs\Operations;

use App\Models\Packages\MultiDestination;
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

        // check for child package if multi type, set it same as parent, for some status
        $statusPackage = $this->package->status;
        $childPackageSetter = [
            Package::STATUS_WAITING_FOR_PICKUP,
            Package::STATUS_PICKED_UP,
            Package::STATUS_WAITING_FOR_ESTIMATING,
        ];
        if (in_array($statusPackage, $childPackageSetter))
        $childs = MultiDestination::where('parent_id', $this->package->getKey())->get();
        if ($childs->count()) {
            foreach ($childs as $c) {
                $packageChild = Package::find($c->child_id);
                if ($packageChild) {
                    $packageChild->status = Package::STATUS_WAITING_FOR_ESTIMATING;
                    $packageChild->save();
                }
            }
        }

        return $this->package->exists;
    }
}
