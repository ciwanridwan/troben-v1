<?php

namespace App\Jobs\Packages\Motobikes;

use App\Casts\Package\Items\Handling;
use App\Models\Packages\MotorBike;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreatePackageForBike
{
    use Dispatchable;

    protected array $attributes;

    protected array $items;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $inputs, array $items)
    {
        $this->attributes = Validator::make($inputs, [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],
            'transporter_type' => ['required', Rule::in(Transporter::getListForBike())],
            'partner_code' => ['required', 'exists:partners,code'],

            'sender_name' => ['required', 'string'],
            'sender_phone' => ['required', 'string'],
            'sender_address' => ['required', 'string'],
            'sender_detail_address' => ['nullable', 'string'],
            'sender_lat' => ['required', 'numeric'],
            'sender_lon' => ['required', 'numeric'],
            'origin_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'origin_district_id' => ['nullable', 'exists:geo_districts,id'],
            'origin_sub_district_id' => ['nullable', 'exists:geo_sub_districts,id'],

            'receiver_name' => ['required', 'string'],
            'receiver_phone' => ['required', 'string'],
            'receiver_address' => ['required', 'string'],
            'receiver_detail_address' => ['nullable', 'string'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],
            'created_by' => ['nullable', 'exists:customers,id']
        ]);

        $this->items = Validator::make($items, [
            'item' => ['nullable'],
            '*.moto_type' => ['nullable', Rule::in(MotorBike::getListType())],  
            '*.moto_brand' => ['nullable', 'string'],
            '*.moto_cc' => ['nullable', 'numeric'],
            '*.moto_year' => ['nullable', 'numeric'],
            '*.is_insured' => ['nullable', 'boolean'],
            '*.price' => ['required_if:*.is_insured,true', 'numeric'],

            '*.handling' => ['nullable'],
            '*.handling.*' => ['nullable', Rule::in(Handling::TYPE_WOOD)]
        ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        dd($this->attributes);
    }
}
