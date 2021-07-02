<?php

namespace App\Supports\Translates;

use App\Contracts\HasCodeLog;
use App\Models\Deliveries\Delivery as DeliveriesDelivery;
use Illuminate\Database\Eloquent\Model;

class Delivery implements HasCodeLog
{
    public DeliveriesDelivery $delivery;

    /**
     * @param App\Models\Deliveries\Delivery $delivery
     */
    function __construct(DeliveriesDelivery $delivery)
    {
        $this->$delivery = $delivery;
    }
    public function translate(): string
    {
        $deliveryDescriptionFormat = $this->getDescriptionFormat();
        if (!$deliveryDescriptionFormat) {
            return $this->delivery->status . '_' . $this->delivery->payment_status;
        }

        $deliveryDescriptionFormat['variable'] = array_flip($deliveryDescriptionFormat['variable']);

        foreach ($deliveryDescriptionFormat['variable'] as $key => $value) {
            $deliveryDescriptionFormat['variable'][$key] = $this->replacer($key);
        }
        $description = __($deliveryDescriptionFormat['description'], $deliveryDescriptionFormat['variable']);

        return $description;
    }

    public function getDescriptionFormat(): array
    {
        foreach (DeliveriesDelivery::getAvailableDescriptionFormat() as $key => $value) {
            if (in_array($this->delivery->type, $value['type']) && in_array($this->delivery->status, $value['status'])) {
                return $value;
            }
        }
        return false;
    }

    public function replacer(string $replace): string
    {
        switch ($replace) {
            default:
                return '';
        }
    }
}
