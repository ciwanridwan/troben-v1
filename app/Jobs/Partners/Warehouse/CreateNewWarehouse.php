<?php

namespace App\Jobs\Partners\Warehouse;

use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\Warehouse\WarehouseCreated;

class CreateNewWarehouse
{
    use Dispatchable;
    /**
     * Instance Warehouse.
     *
     * @var \App\Models\Partners\Warehouse
     */
    public Model $warehouse;
    /**
     * Instance partner.
     *
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;
    /**
     * Accept Attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Partners\Partner $partner
     * @param array                        $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs, [
            'geo_province_id' => ['nullable', 'exists:geo_provinces,id'],
            'geo_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'geo_district_id' => ['nullable', 'exists:geo_districts,id'],
            'address' => ['nullable'],
            'geo_area' => ['nullable'],
            'height' => ['required', 'numeric'],
            'length' => ['required', 'numeric'],
            'width' => ['required', 'numeric'],
            'is_pool' => ['nullable', 'boolean'],
            'is_counter' => ['nullable', 'boolean'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->warehouse = $this->partner->warehouses()->create($this->attributes);

        if ($this->warehouse) {
            event(new WarehouseCreated($this->warehouse));
        }

        return $this->warehouse->exists;
    }
}
