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

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    private array $inputs;

    /**
     * @var \App\Models\Partners\Partner|null
     */
    private ?Partner $partner;

    /**
     * @var \App\Models\Partners\Transporter|null
     */
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
        $this->inputs = Validator::make($inputs, [
            'type' => ['required', Rule::in(Delivery::getAvailableTypes())],
            'status' => ['nullable', Rule::in(Delivery::getAvailableStatus())],
        ])->validated();

        $this->delivery = new Delivery();
        $this->partner = $partner;
        $this->transporter = $transporter;
    }

    public function handle(): void
    {
        $this->delivery->fill($this->inputs);

        if ($this->partner) {
            $this->delivery->partner()->associate($this->partner);
        }

        if ($this->transporter) {
            $this->delivery->transporter()->associate($this->transporter);
        }

        $this->delivery->save();
    }
}
