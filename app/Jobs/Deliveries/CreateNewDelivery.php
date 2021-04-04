<?php

namespace App\Jobs\Deliveries;

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

    /**
     * CreateNewDelivery constructor.
     *
     * @param array $inputs
     * @param \App\Models\Partners\Partner|null $partner
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs = [], ?Partner $partner = null)
    {
        $this->attributes = Validator::make($inputs, [
            'type' => ['required', Rule::in(Delivery::getAvailableTypes())],
            'status' => ['nullable', Rule::in(Delivery::getAvailableStatus())],
        ])->validate();

        $this->delivery = new Delivery();
        $this->partner = $partner;
    }

    public function handle(): void
    {
        $this->delivery->fill($this->attributes);

        if ($this->partner) {
            $this->delivery->partner()->associate($this->partner);
        }

        $this->delivery->save();
    }
}
