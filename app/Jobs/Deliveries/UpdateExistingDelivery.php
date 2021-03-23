<?php

namespace App\Jobs\Deliveries;

use Illuminate\Validation\Rule;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Validator;

class UpdateExistingDelivery
{
    public Delivery $delivery;

    private array $attributes;

    /**
     * UpdateExistingDelivery constructor.
     * @param \App\Models\Deliveries\Delivery $delivery
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Delivery $delivery, array $inputs = [])
    {
        $this->delivery = $delivery;
        $this->attributes = Validator::make($inputs, [
            'type' => ['required', Rule::in(Delivery::getAvailableTypes())],
            'status' => ['nullable', Rule::in(Delivery::getAvailableStatus())],
        ])->validated();
    }

    public function handle()
    {
        $this->delivery->fill($this->attributes);

        $this->delivery->save();
    }
}
