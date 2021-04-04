<?php

namespace App\Jobs\Packages;

use App\Models\Handling;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Events\Packages\PackageUpdated;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

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
            'transporter_type' => ['nullable', Rule::in(Transporter::getAvailableTypes())],
            'sender_name' => ['nullable'],
            'sender_phone' => ['nullable'],
            'sender_address' => ['nullable'],
            'receiver_name' => ['nullable'],
            'receiver_phone' => ['nullable'],
            'receiver_address' => ['nullable'],
            'handling' => ['nullable', 'array'],
            'handling.*' => ['numeric', 'exists:handling,id'],
            'origin_regency_id' => ['nullable'],
            'destination_regency_id' => ['nullable'],
            'destination_district_id' => ['nullable'],
            'destination_sub_district_id' => ['nullable'],
        ])->validate();

        $this->isSeparated = $isSeparated;
    }

    public function handle()
    {
        if ($this->isSeparated !== null) {
            $this->package->is_separate_item = $this->isSeparated;
        }

        $this->attributes['handling'] = collect($this->attributes['handling'] ?? [])
            ->map(fn ($id) => Handling::query()->find($id))
            ->filter(fn (?Handling $handling) => $handling !== null)
            ->toArray();

        $this->package->fill($this->attributes);

        $this->package->save();

        event(new PackageUpdated($this->package));
    }
}
