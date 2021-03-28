<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Package;
use App\Events\Packages\PackageUpdated;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\Rule;

class UpdateExistingPackage
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
    public function __construct(Package $package, array $inputs, bool $isSeparated = null)
    {
        $this->package = $package;

        $this->attributes = Validator::make($inputs, [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['nullable', 'exists:services,code'],
            'transporter_type' => ['required', Rule::in(Transporter::getAvailableTypes())],
            'sender_name' => ['nullable'],
            'sender_phone' => ['nullable'],
            'sender_address' => ['nullable'],
            'receiver_name' => ['nullable'],
            'receiver_phone' => ['nullable'],
            'receiver_address' => ['nullable'],
            'origin_regency_id' => ['nullable'],
            'destination_regency_id' => ['nullable'],
            'destination_district_id' => ['nullable'],
            'destination_sub_district_id' => ['nullable'],
        ])->validated();

        $this->isSeparated = $isSeparated;
    }

    public function handle()
    {
        if ($this->isSeparated !== null) {
            $this->package->is_separate_item = $this->isSeparated;
        }

        $this->package->fill($this->attributes);

        $this->package->save();

        event(new PackageUpdated($this->package));
    }
}
