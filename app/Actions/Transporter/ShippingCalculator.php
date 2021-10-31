<?php

namespace App\Actions\Transporter;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;

class ShippingCalculator
{
    /** @var int Mean earth radius in [km] */
    public const EARTH_RADIUS = 6371;

    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * ref: https://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
     *
     * @param array $originPoint Array of latitude and longitude of start point in [deg decimal]
     * @param array $destinationPoint Array of latitude and longitude of destination point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [km]
     * @return float Distance between points in [km] (same as earthRadius)
     */
    public static function calculateDistance(array $originPoint, array $destinationPoint, $earthRadius = self::EARTH_RADIUS)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($originPoint[0]);
        $lonFrom = deg2rad($originPoint[1]);
        $latTo = deg2rad($destinationPoint[0]);
        $lonTo = deg2rad($destinationPoint[1]);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return ceil($angle * $earthRadius);
    }

    /**
     * Calculating pickup price by distance from customer/partner to partner.
     *
     * @param Delivery $delivery   Instance of Delivery
     * @param bool $isFromCustomer Flag distance from customer or partner
     * @return int
     * @throws \Throwable
     */
    public static function getDeliveryFeeByDistance(Delivery $delivery, bool $isFromCustomer = true): int
    {
        if (!in_array($delivery->type, [Delivery::TYPE_TRANSIT,Delivery::TYPE_PICKUP]) || is_null($delivery->partner_id) || is_null($delivery->userable_id)) return 0;

        if ($isFromCustomer) {
            /** @var Package $package */
            $package = $delivery->packages()->first();
            $originPoint = [$package->sender_latitude, $package->sender_longitude];
        } else {
            $origin = $delivery->origin_partner;
            $originPoint = [$origin->latitude,$origin->longitude];
        }

        $destination = $delivery->partner;
        $destinationPoint = [$destination->latitude,$destination->longitude];

        if (in_array(null, $originPoint) || in_array(null, $destinationPoint)) return 0;
        $distance = self::calculateDistance($originPoint,$destinationPoint);

        $transporter = $delivery->transporter;
        $rateByType = Transporter::getAvailableRatesByType()[$transporter->type];

        if ($distance > $rateByType['start_rate']) {
            $distance -= $rateByType['start_rate'];
            $price = $rateByType['start_price'] + ceil($distance * $rateByType['rate_price']);
        } else {
            $price = $rateByType['start_price'];
        }
        return $price;
    }
}
