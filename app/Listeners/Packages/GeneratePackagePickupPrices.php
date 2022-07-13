<?php

namespace App\Listeners\Packages;

use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
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
        $origin = $package->sender_latitude.', '.$package->sender_longitude;
        $partner = Partner::where('code', $event->partner_code)->first();
        $destination = $partner->latitude.', '.$partner->longitude;
        $distance = DistanceMatrix::calculateDistance($origin, $destination);

        if ($package->transporter_type == null) {
            $pickup_price = 0;
        } elseif ($package->transporter_type == Transporter::GENERAL_TYPE_BIKE) {
            if ($distance < 5) {
                $pickup_price = 8000;
            } else {
                $substraction = $distance - 4;
                $pickup_price = 8000 + (2000 * $substraction);
                // dd($pickup_price);
            }
        } else {
            if ($distance < 5) {
                $pickup_price = 15000;
            } else {
                $substraction = $distance - 4;
                $pickup_price = 15000 + (4000 * $substraction);
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
