<?php

namespace App\Jobs\Partners\Balance;

use App\Events\Partners\Balance\NewDeliveryHistoryCreated;
use App\Models\Partners\Balance\DeliveryHistory;;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewBalanceDeliveryHistory
{
    use Dispatchable;

    /**
     * Partner balance delivery history attributes.
     *
     * @var array $attributes
     */
    public array $attributes;

    /**
     * Partner balance delivery history instance.
     *
     * @var DeliveryHistory $deliveryHistory
     */
    public DeliveryHistory $deliveryHistory;

    /**
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs)
    {
        $this->attributes = Validator::make($inputs, [
            'partner_id' => ['required', 'exists:partners,id'],
            'delivery_id' => ['required', 'exists:deliveries,id'],
            'balance' => ['required','numeric'],
            'type' => ['required', Rule::in(DeliveryHistory::getAvailableType())],
            'description' => ['required', Rule::in(DeliveryHistory::getAvailableDescription())],
        ])->validate();
        $this->deliveryHistory = new DeliveryHistory();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): bool
    {
        $this->deliveryHistory->fill($this->attributes);

        if ($this->deliveryHistory->save()) {
            event(new NewDeliveryHistoryCreated($this->deliveryHistory));
        }

        return $this->deliveryHistory->exists;
    }
}
