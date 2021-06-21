<?php

namespace App\Jobs\Deliveries\Actions;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class RejectDeliveryFromPartner
{
    use Dispatchable;

    /**
     * @var Delivery
     */
    public Delivery $delivery;

    protected Partner $partner;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, Partner $partner)
    {
        $this->delivery = $delivery;
        $this->partner = $partner;
        $mustConditions = [Delivery::TYPE_PICKUP];
        throw_if(!in_array($this->delivery->type, $mustConditions), ValidationException::withMessages([
            'package' => __('Delivery should be in ' . implode(',', $mustConditions) . ' Type'),
        ]));
        if ($this->delivery->partner->id !== $this->partner->id) {
            throw new \LogicException('chosen partner must had the delivery');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->delivery->setAttribute('status', Delivery::STATUS_PENDING);
        $this->delivery->setAttribute('partner_id', null);
        $this->delivery->save();

        return $this->delivery->exists;
    }
}
