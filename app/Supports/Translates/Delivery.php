<?php

namespace App\Supports\Translates;

use App\Models\Deliveries\Delivery as DeliveriesDelivery;

class Delivery
{

    public static function translateDelivery(DeliveriesDelivery $delivery)
    {
        $deliveryDescriptionFormat = self::getDeliveryDescriptionFormat($delivery);
        if (!$deliveryDescriptionFormat) {
            return $delivery->status . '_' . $delivery->payment_status;
        }
        // throw_if(!$deliveryDescriptionFormat, Error::make(Response::RC_CODE_LOG_UNAVAILABLE));

        foreach ($deliveryDescriptionFormat['variable'] as $key => $value) {
            $deliveryDescriptionFormat['variable'][$key] = self::deliveryReplacer($delivery, $key);
        }
        $description = __($deliveryDescriptionFormat['description'], $deliveryDescriptionFormat['variable']);

        return $description;
    }

    public static function getDeliveryDescriptionFormat(DeliveriesDelivery $delivery)
    {
        foreach (DeliveriesDelivery::getAvailableDescriptionFormat() as $key => $value) {
            if (in_array($delivery->type, $value['type']) && in_array($delivery->status, $value['status'])) {
                return $value;
            }
        }
        return false;
    }

    public static function deliveryReplacer(DeliveriesDelivery $delivery, string $replace)
    {
        switch ($replace) {
            default:
                return '';
        }
    }
}
