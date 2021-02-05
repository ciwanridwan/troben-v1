<?php

namespace App\Jobs\Partners\Warehouse;

use App\Events\Partners\Warehouse\WarehouseModificationFailed;
use App\Events\Partners\Warehouse\WarehouseModified;
use App\Models\Partners\Warehouse;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;

class UpdateExistingPartner
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    public Warehouse $warehouse;
    public array $attributes;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Warehouse $warehouse, $inputs = [])
    {
        $this->warehouse = $warehouse;
        $this->attributes = FacadesValidator::make($inputs,[
            'geo_province_id' => ['nullable', 'exists:geo_provinces,id'],
            'geo_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'geo_district_id' => ['nullable', 'exists:geo_districts,id'],
            'code' => ['filled'],
            'name' => ['filled'],
            'address' => ['nullable'],
            'geo_area' => ['nullable'],
            'is_pool' => ['nullable', 'boolean'],
            'is_counter' => ['nullable', 'boolean'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): bool
    {
        foreach ($this->attributes as $key => $value) {
            $this->warehouse->$key = $value;
        }

        if ($this->warehouse->isDirty()) {
            if ($this->warehouse->save()) {
                event(new WarehouseModified($this->warehouse));
            } else {
                event(new WarehouseModificationFailed($this->warehouse));
            }
        }

        return $this->warehouse->exists;
    }
}
