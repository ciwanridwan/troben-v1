<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\Deliveries\DeliveryDooringCreated;
use App\Jobs\Deliveries\Actions\V2\CreateNewDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class CreateNewDooring
{
    use Dispatchable;

    public Delivery $delivery;

    public UserablePivot|null $userable;

    private Partner $originPartner;

    private array $attributes;

    /**
     * CreateNewDooring constructor.
     * @param Partner $originPartner
     */
    public function __construct(Partner $originPartner, array $inputs)
    {
        $this->originPartner = $originPartner;
        $this->attributes = [
            'type' => Delivery::TYPE_DOORING,
            'status' => Delivery::STATUS_ACCEPTED
        ];
        if (count($inputs) == 2) {
            Validator::make($inputs, [
                'userable_hash' => ['nullable', new ExistsByHash(UserablePivot::class)]
            ])->validate();

            $this->userable = UserablePivot::byHashOrFail($inputs['userable_hash']);
        } else {
            $this->userable = null;
        }
    }

    /**
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(): bool
    {
        $this->attributes['userable_id'] = $this->userable ? $this->userable->id : null;
        if (is_null($this->attributes['userable_id'])) {
            $this->attributes['status'] = Delivery::STATUS_WAITING_ASSIGN_PARTNER;
        }

        $job = new CreateNewDelivery($this->attributes, null, $this->originPartner);
        dispatch_now($job);
        $this->delivery = $job->delivery;

        event(new DeliveryDooringCreated($this->delivery));

        return $this->delivery->exists;
    }
}
