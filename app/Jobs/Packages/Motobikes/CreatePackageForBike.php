<?php

namespace App\Jobs\Packages\Motobikes;

use App\Casts\Package\Items\Handling;
use App\Models\Packages\Item;
use App\Models\Packages\MotorBike;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreatePackageForBike
{
    use Dispatchable;

    protected array $attributes;

    protected array $items;

    protected array $bikes;

    public Package $package;

    public Item $item;

    public MotorBike $motoBike;
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
            'sender_way_point' => ['nullable', 'string'],
            'sender_latitude' => ['required', 'numeric'],
            'sender_longitude' => ['required', 'numeric'],
            'origin_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'origin_district_id' => ['nullable', 'exists:geo_districts,id'],
            'origin_sub_district_id' => ['nullable', 'exists:geo_sub_districts,id'],

            'receiver_name' => ['required', 'string'],
            'receiver_phone' => ['required', 'string'],
            'receiver_address' => ['required', 'string'],
            'receiver_way_point' => ['nullable', 'string'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],
            'created_by' => ['nullable', 'exists:customers,id']
        ])->validate();

        $this->items = Validator::make($items, [
            'is_insured' => ['nullable', 'boolean'],
            'price' => ['required_if:is_insured,true', 'numeric'],
            'handling' => ['required', Rule::in(Handling::TYPE_BIKES)],
            'qty' => ['required', 'numeric'],
            'name' => ['required', 'string'],
            'weight' => ['required', 'numeric'],
            'height' => ['required', 'numeric'],
            'width' => ['required', 'numeric'],
            'length' => ['required', 'numeric'],
            'category_item_id' => ['required', 'exists:category_items,id']
        ])->validate();

        $this->bikes = Validator::make($items, [
            'moto_merk' => ['required', 'string'],
            'moto_type' => ['required', 'string'],
            'moto_year' => ['required', 'numeric'],
            'moto_cc' => ['required', 'numeric'],
        ])->validate();

        $this->package = new Package();
        $this->item = new Item();
        $this->motoBike = new MotorBike();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $this->package->fill($this->attributes);
        $this->package->is_separate_item = false;
        $this->package->save();

        if ($this->package->exists) {
            $this->bikes['package_id'] = $this->package->id;
            $this->bikes['cc'] = $this->bikes['moto_cc'];
            $this->bikes['years'] = $this->bikes['moto_year'];
            $this->bikes['merk'] = $this->bikes['moto_merk'];
            $this->bikes['type'] = $this->bikes['moto_type'];
            
            $this->motoBike->fill($this->bikes);
            $this->motoBike->save();

            $this->items['package_id'] = $this->package->id;
            $this->item->fill($this->items);
            $this->item->save();

            $this->motoBike['package_item_id'] = $this->item->id;
            $this->motoBike->save();
        }

        return $this->package->exists;
    }
}
