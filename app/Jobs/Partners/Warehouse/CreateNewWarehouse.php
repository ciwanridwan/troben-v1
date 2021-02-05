<?php

namespace App\Jobs\Partners\Warehouse;

use App\Events\Partners\Warehouse\WarehouseCreated;
use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class CreateNewWarehouse
{
    /**
     * Instance Warehouse
     *
     * @var App\Models\Partners\Warehouse
     */
    public Warehouse $warehouse;
    /**
     * Instance partner
     *
     * @var App\Models\Partners\Partner
     */
    public Partner $partner;
    /**
     * Accept Attributes
     *
     * @var array
     */
    public array $attributes;

    use Dispatchable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs, [
            'geo_province_id' => ['nullable', 'exists:geo_provinces,id'],
            'geo_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'geo_district_id' => ['nullable', 'exists:geo_districts,id'],
            'code' => ['required'],
            'name' => ['required'],
            'address' => ['nullable'],
            'geo_area' => ['nullable'],
            'is_pool' => ['nullable', 'boolean'],
            'is_counter' => ['nullable', 'boolean']
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->warehouse = $this->partner->warehouses()->create($this->attributes);
        if ($this->warehouse) {
            event(new WarehouseCreated($this->warehouse));
        }

        return $this->warehouse->exists;
    }
}
