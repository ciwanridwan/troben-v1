<?php

namespace App\Listeners\Packages;

use App\Actions\Pricing\PricingCalculator;
use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Packages\Price;
use App\Models\Packages\Promo;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GeneratePackagePromo
{
    use DispatchesJobs;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // check promo discount is available
        if (!is_null($event->package->promos)) {
            $promo = $event->package->promos;
            // set status event by condition
            if ($event->package->total_weight >= 50) {
                $promo->status = Promo::STATUS_VALID;
            } else {
                $promo->status = Promo::STATUS_FAIL;
            }
            $promo->save();
        } else {
            if (isset($event->partner) && ! is_null($event->partner) && $event->partner->isJabodetabek()) {
                // set promo discount
                if ($event->package->total_weight >= 50) {
                    $discountMax = 20000;

                    $pickupPrice = $event->package->prices()->where('type', Price::TYPE_DELIVERY)->where('description', Price::TYPE_PICKUP)->sum('amount');
                    // check if pickup price more than discount max
                    if ($pickupPrice >= $discountMax) {
                        $discount = $discountMax;
                    } else {
                        $discount = $pickupPrice;
                    }

                    // update package prices
                    $job = new UpdateOrCreatePriceFromExistingPackage($event->package, [
                        'type' => Price::TYPE_DISCOUNT,
                        'description' => Price::TYPE_PICKUP,
                        'amount' => $discount,
                    ]);
                    $this->dispatch($job);

                    // set meta
                    $meta = [
                        'amount' => $discount
                    ];

                    // create new record
                    Promo::create([
                        'package_id' => $event->package->id,
                        'type' => Promo::TYPE_DISCOUNT_PICKUP,
                        'status' => Promo::STATUS_PENDING,
                        'meta' => json_encode($meta)
                    ]);

                    $package = $event->package->refresh();

                    // update total amount
                    $package->setAttribute('total_amount', PricingCalculator::getPackageTotalAmount($package, false))->save();
                }
            }
        }
    }
}
