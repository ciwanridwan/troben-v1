<?php

namespace App\Listeners\Packages;

use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Deliveries\Delivery;
use App\Models\PackageMeta;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use App\Models\PartnerSatellite;
use App\Supports\DistanceMatrix;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GeneratePackagePickupPrices
{
    use DispatchesJobs;
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     * @throws ValidationException
     */
    public function handle($event)
    {
        /** @var Package $package */
        $package = $event->package->refresh();
        $origin = $package->sender_latitude . ', ' . $package->sender_longitude;
        $partner = Partner::where('code', $event->partner_code)->first();
        $destination = $partner->latitude . ', ' . $partner->longitude;

        $checkSatellite = PackageMeta::query()
            ->where('package_id', $package->getKey())
            ->where('key', PackageMeta::KEY_PARTNER_SATELLITE)
            ->first();
        if (!is_null($checkSatellite)) {
            $partnerSatellite = PartnerSatellite::find($checkSatellite->meta['partner_satellite']);
            if (!is_null($partnerSatellite)) {
                // override partner property
                $destination = $partner->latitude . ', ' . $partner->longitude;
            }
        }

        $distance = DistanceMatrix::calculateDistance($origin, $destination);

        $metaDistance = [
            'distance' => $distance,
            'origin' => $origin,
            'destination' => $destination,
        ];
        $pickupDistance = PackageMeta::query()
            ->where('package_id', $package->getKey())
            ->where('key', PackageMeta::KEY_PICKUP_DISTANCE)
            ->first();
        if (! is_null($pickupDistance)) {
            $pickupDistance->meta = $metaDistance;
            $pickupDistance->save();
        } else {
            PackageMeta::create([
                'package_id' => $package->getKey(),
                'key' => PackageMeta::KEY_PICKUP_DISTANCE,
                'meta' => $metaDistance,
            ]);
        }

        if ($package->transporter_type == null) {
            $pickup_price = 0;
        } elseif ($package->transporter_type == Transporter::GENERAL_TYPE_BIKE) {
            if ($distance < 5) {
                $pickup_price = 15000;
            } else {
                $substraction = $distance - 4;
                $pickup_price = 15000 + (3000 * $substraction);
            }
        } else {
            if ($distance < 5) {
                $pickup_price = 25000;
            } else {
                $substraction = $distance - 4;
                $pickup_price = 25000 + (6000 * $substraction);
            }
        }

        // generate pickup price
        $job = new UpdateOrCreatePriceFromExistingPackage($package, [
            'type' => Price::TYPE_DELIVERY,
            'description' => Delivery::TYPE_PICKUP,
            'amount' => $pickup_price,
        ]);
        $this->dispatch($job);
    }
}
