<?php

namespace App\Jobs\Deliveries\Actions\V2;

use App\Events\Deliveries\DeliveryCreated;
use App\Events\Deliveries\DeliveryDooringCreated;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewDelivery
{
    use Dispatchable;

    public Delivery $delivery;

    private array $attributes;

    private ?Partner $partner;

    private ?Partner $originPartner;

    /**
     * CreateNewDelivery constructor.
     *
     * @param array $inputs
     * @param \App\Models\Partners\Partner|null $partner
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs = [], ?Partner $partner = null, ?Partner $originPartner = null)
    {
        $this->attributes = Validator::make($inputs, [
            'type' => ['required', Rule::in(Delivery::getAvailableTypes())],
            'status' => ['nullable', Rule::in(Delivery::getAvailableStatus())],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'userable_id' => ['nullable', 'exists:userables,id'],
            'origin_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'origin_district_id' => ['nullable', 'exists:geo_districts,id'],
            'origin_sub_district_id' => ['nullable', 'exists:geo_sub_districts,id'],
            'destination_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'destination_district_id' => ['nullable', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['nullable', 'exists:geo_sub_districts,id'],
        ])->validate();

        $this->delivery = new Delivery();
        $this->partner = $partner;
        $this->originPartner = $originPartner;
    }

    public function handle(): void
    {
        $this->delivery->fill($this->attributes);
        if ($this->attributes['type'] == 'pickup') {
            $this->delivery->created_by = User::USER_SYSTEM_ID;
        } else {
            $this->delivery->created_by = auth()->user()->id;
        }
        if ($this->partner) {
            $this->delivery->partner()->associate($this->partner);
        }
        if ($this->originPartner) {
            $this->delivery->origin_partner()->associate($this->originPartner);
        }
        if ($this->delivery) {
            event(new DeliveryCreated($this->delivery));
        }

        $this->delivery->save();
    }
}
