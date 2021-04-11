<?php

namespace App\Jobs\Deliveries\Actions;

use App\Jobs\Packages\CreateNewPackage;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class CreateNewManifest
{
    use Dispatchable;

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
            'target_partner_hash' => ['required', new ExistsByHash(Partner::class)],
            'destination_regency_id' => ['nullable'],
            'destination_district_id' => ['nullable'],
            'destination_sub_district_id' => ['nullable'],
        ])->validate();
        $this->originPartner = $originPartner;
    }

    public function handle()
    {
        $this->attributes['type'] = Delivery::TYPE_TRANSIT;
        $this->attributes['origin_regency_id'] = $this->originPartner->geo_regency_id;
        $this->attributes['origin_district_id'] = $this->originPartner->geo_district_id;
        $this->attributes['origin_sub_district_id'] = $this->originPartner->geo_sub_district_id;

        CreateNewPackage::dispatch($this->attributes, Partner::byHashOrFail($this->attributes['target_partner_hash']));
    }
}
