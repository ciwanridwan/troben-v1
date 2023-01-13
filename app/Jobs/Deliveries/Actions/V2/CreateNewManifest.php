<?php

namespace App\Jobs\Deliveries\Actions\V2;

use App\Events\Deliveries\DeliveryCreatedWithDeadline;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Validator;
use App\Jobs\Deliveries\Actions\V2\CreateNewDelivery;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\Dispatchable;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class CreateNewManifest
{
    use Dispatchable;

    public Delivery $delivery;
    private array $attributes;

    public UserablePivot|null $userable;
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
            'partner_hash' => ['required', new ExistsByHash(Partner::class)],
            // 'userable_hash' => ['nullable', new ExistsByHash(UserablePivot::class)],
        ])->validate();

        if (count($inputs) == 3) {
            Validator::make($inputs, [
                'userable_hash' => ['nullable', new ExistsByHash(UserablePivot::class)]
            ])->validate();

            $this->userable = UserablePivot::byHashOrFail($inputs['userable_hash']);
        } else {
            $this->userable = null;
        }

        $this->originPartner = $originPartner;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle()
    {
        $userableId = null;

        if (is_null($this->userable)) {
            $this->attributes['status'] = Delivery::STATUS_WAITING_ASSIGN_PARTNER;
        } else {
            $userableId = $this->userable->id;
            $this->attributes['status'] = Delivery::STATUS_ACCEPTED;
        }

        $partner = Partner::byHashOrFail($this->attributes['partner_hash']);

        $this->attributes['type'] = Delivery::TYPE_TRANSIT;
        $this->attributes['userable_id'] = $userableId;
        $this->attributes['origin_regency_id'] = $this->originPartner->geo_regency_id;
        $this->attributes['origin_district_id'] = $this->originPartner->geo_district_id;
        $this->attributes['origin_sub_district_id'] = $this->originPartner->geo_sub_district_id;
        $this->attributes['destination_regency_id'] = $partner->geo_regency_id;
        $this->attributes['destination_district_id'] = $partner->geo_district_id;
        $this->attributes['destination_sub_district_id'] = $partner->geo_sub_district_id;
        $this->attributes['created_by'] = auth()->user()->id;

        $job = new CreateNewDelivery($this->attributes, $partner);
        dispatch_now($job);

        if ($job->delivery->exists) {
            $job->delivery->origin_partner()->associate($this->originPartner);
            $job->delivery->save();
        }

        event(new DeliveryCreatedWithDeadline($job->delivery));
        $this->delivery = $job->delivery;
    }
}
