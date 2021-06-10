<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\Deliveries\PartnerRequested;
use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

/**
 * Class RequestPartnerToDelivery.
 */
class RequestPartnerToDelivery
{
    use Dispatchable;

    /**
     * @var Delivery $delivery
     */
    public Delivery $delivery;

    /**
     * RequestPartnerToDelivery constructor.
     * @param Delivery $delivery
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        throw_if(
            $this->delivery->status !== Delivery::STATUS_WAITING_ASSIGN_PACKAGE,
            ValidationException::withMessages([
                'manifest' => __('manifest not ready to be sent.'),
            ])
        );
        $this->delivery->setAttribute('status', Delivery::STATUS_WAITING_ASSIGN_PARTNER)->save();
        event(new PartnerRequested($this->delivery));
    }
}
