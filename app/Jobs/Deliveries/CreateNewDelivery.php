<?php

namespace App\Jobs\Deliveries;

use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewDelivery
{
    use Dispatchable;

    public Delivery $delivery;

    private array $attributes;

    private ?Partner $partner;

    private ?Transporter $transporter;

    /**
     * CreateNewDelivery constructor.
     *
     * @param array $inputs
     * @param \App\Models\Partners\Partner|null $partner
     * @param \App\Models\Partners\Transporter|null $transporter
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs = [], ?Partner $partner = null, ?Transporter $transporter = null)
    {
        $this->attributes = Validator::make($inputs, [
            'type' => ['required', Rule::in(Delivery::getAvailableTypes())],
            'status' => ['nullable', Rule::in(Delivery::getAvailableStatus())],
        ])->validated();

        $this->delivery = new Delivery();
        $this->partner = $partner;
        $this->transporter = $transporter;
    }

    public function handle(): void
    {
        $this->delivery->fill($this->attributes);

        if ($this->partner) {
            $this->delivery->partner()->associate($this->partner);
        }

        if ($this->transporter) {
            $this->delivery->transporter()->associate($this->transporter);
        }

        $this->delivery->save();
    }
}
