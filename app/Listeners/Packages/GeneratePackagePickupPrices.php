<?php

namespace App\Listeners\Packages;

use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;

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
        $distance = $this->distance_matrix($origin, $destination);

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

    public function distance_matrix($origin, $destination)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->get('https://maps.googleapis.com/maps/api/distancematrix/json?destinations=' . $destination . '&origins=' . $origin . '&units=metric&key=AIzaSyAo47e4Aymv12UNMv8uRfgmzjGx75J1GVs');
        $response = json_decode($response->body());

        $distance = 0;
        if (count($response->rows)
            && count($response->rows[0]->elements)
            && isset($response->rows[0]->elements[0]->distance)) {
            $distance = $response->rows[0]->elements[0]->distance->text;
            $distance = str_replace("km", "", $distance);
            $distance = str_replace(",", "", $distance);
            $distance = (float) $distance;
        } else {
            Log::info("distancezero", ['dest' => $destination, 'origin' => $origin]);
        }

        return $distance;
    }
}
