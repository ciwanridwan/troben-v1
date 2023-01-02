<?php

namespace App\Jobs\Deliveries\Actions\V2;

use App\Events\Deliveries\DeliveryCreatedWithDeadline;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Validator;
use App\Jobs\Deliveries\Actions\V2\CreateNewDelivery;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewManifest
{
    use Dispatchable;

    public Delivery $delivery;
    private array $attributes;

    /**
     * @var \App\Models\Partners\Partner
     */
    private Partner $originPartner;

    /**
     * CreateNewManifest constructor.
     * @param \App\Models\Partners\Partner $originPartner
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Partner $originPartner, array $inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'destination_regency_id' => ['nullable'],
            'destination_district_id' => ['nullable'],
            'destination_sub_district_id' => ['nullable'],
        ])->validate();

        $this->originPartner = $originPartner;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle()
    {
        $this->attributes['type'] = Delivery::TYPE_TRANSIT;
        $this->attributes['status'] = Delivery::STATUS_WAITING_ASSIGN_PACKAGE;
        $this->attributes['origin_regency_id'] = $this->originPartner->geo_regency_id;
        $this->attributes['origin_district_id'] = $this->originPartner->geo_district_id;
        $this->attributes['origin_sub_district_id'] = $this->originPartner->geo_sub_district_id;
        $this->attributes['created_by'] = auth()->user()->id;

        $job = new CreateNewDelivery($this->attributes);

        dispatch_now($job);

        if ($job->delivery->exists) {
            $job->delivery->origin_partner()->associate($this->originPartner);
            $job->delivery->save();
        }

        event(new DeliveryCreatedWithDeadline($job->delivery));
        $this->delivery = $job->delivery;
    }
}
