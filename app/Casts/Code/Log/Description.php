<?php

namespace App\Casts\Code\Log;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Description implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }



    public static function translateDelivery(Delivery $delivery)
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

    public static function getDeliveryDescriptionFormat(Delivery $delivery)
    {
        foreach (Delivery::getAvailableDescriptionFormat() as $key => $value) {
            if (in_array($delivery->type, $value['type']) && in_array($delivery->status, $value['status'])) {
                return $value;
            }
        }
        return false;
    }

    public static function deliveryReplacer(Delivery $delivery, string $replace)
    {
        switch ($replace) {
            default:
                return '';
        }
    }
}
