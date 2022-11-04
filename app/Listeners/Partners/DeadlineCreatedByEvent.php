<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Packages\PackagesAttachedToDelivery;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Performances\Delivery as PartnerDeliveryPerformance;
use App\Models\Partners\Performances\Package as PartnerPackagePerformance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeadlineCreatedByEvent
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event): void
    {
        switch (true) {
            case $event instanceof PayByNicepay:
                $package = $event->package;
                $partnerPickup = $package->picked_up_by->first()->partner;

                # add performance
                $deadline = Carbon::now() < Carbon::today()->addHours(20) ? Carbon::now()->endOfDay() : Carbon::tomorrow()->endOfDay();
                $performanceQuery = PartnerPackagePerformance::query()->create([
                    'partner_id' => $partnerPickup->id,
                    'package_id' => $package->id,
                    'deadline' => $deadline
                ]);

                Log::debug('Deadline Package Created Listener: ', [$performanceQuery]);
                break;
            case $event instanceof PackagesAttachedToDelivery:
                $delivery = $event->delivery;
                if ($delivery->status === Delivery::STATUS_ACCEPTED) {
                    break;
                }
                $partnerOrigin = $delivery->origin_partner;

                $deadline = Carbon::now() < Carbon::today()->addHours(20) ? Carbon::now()->endOfDay() : Carbon::tomorrow()->endOfDay();
                $performanceDelivery = PartnerDeliveryPerformance::query()->where('partner_id', $partnerOrigin->id)->where('delivery_id', $delivery->id)->first();
                
                if (!$performanceDelivery || is_null($performanceDelivery)) {
                    $performanceQuery = PartnerDeliveryPerformance::query()->create([
                        'partner_id' => $partnerOrigin->id,
                        'delivery_id' => $delivery->id,
                        'deadline' => $deadline
                    ]);                    
                } else {
                    break;
                }

                Log::debug('Deadline Delivery Created Listener: ', [$performanceQuery]);
                break;
            case $event instanceof DriverUnloadedPackageInDestinationWarehouse:
                $delivery = $event->delivery;
                $partnerDestination = $delivery->partner;

                $deadline = Carbon::now()->addHours(2);
                $performanceQuery = PartnerDeliveryPerformance::query()->create([
                    'partner_id' => $partnerDestination->id,
                    'delivery_id' => $delivery->id,
                    'deadline' => $deadline,
                ]);

                Log::debug('Deadline delivery created Listen to driver unload in destination warehouse: ', [$performanceQuery]);
                break;
        }
    }
}
