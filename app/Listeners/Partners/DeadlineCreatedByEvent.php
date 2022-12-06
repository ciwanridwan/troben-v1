<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\DriverAssigned;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Packages\PackagesAttachedToDelivery;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Events\Payment\Nicepay\PayByNicePayDummy;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Performances\Delivery as PartnerDeliveryPerformance;
use App\Models\Partners\Performances\Package as PartnerPackagePerformance;
use App\Models\Partners\Performances\PerformanceModel;
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
                // hold temporary
                // case $event instanceof PackagesAttachedToDelivery:

                //     break;
            case $event instanceof DriverAssigned:
                $delivery = $event->delivery;
                // if ($delivery->status === Delivery::STATUS_ACCEPTED) {
                //     break;
                // }
                $partnerOrigin = $delivery->origin_partner;

                $startTimeAlert = Carbon::today()->addHours(18);
                $now = Carbon::now();
                if ($now < $startTimeAlert) {
                    break;
                }
                // $deadline = Carbon::now() < Carbon::today()->addHours(20) ? Carbon::now()->endOfDay() : Carbon::tomorrow()->endOfDay();
                $deadline = $now > $startTimeAlert ? Carbon::now()->endOfDay() : null;
                $performanceDelivery = PartnerDeliveryPerformance::query()->where('partner_id', $partnerOrigin->id)->where('delivery_id', $delivery->id)->first();

                if (!$performanceDelivery || is_null($performanceDelivery)) {
                    $performanceQuery = PartnerDeliveryPerformance::query()->create([
                        'partner_id' => $partnerOrigin->id,
                        'delivery_id' => $delivery->id,
                        'deadline' => $deadline,
                        'level' => 1,
                        'status' => PerformanceModel::STATUS_ON_PROCESS
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

                $performanceDelivery = PartnerDeliveryPerformance::query()->where('partner_id', $partnerDestination->id)->where('delivery_id', $delivery->id)->first();
                if (!$performanceDelivery || is_null($performanceDelivery)) {
                    $performanceQuery = PartnerDeliveryPerformance::query()->create([
                        'partner_id' => $partnerDestination->id,
                        'delivery_id' => $delivery->id,
                        'deadline' => $deadline,
                    ]);
                } else {
                    break;
                }

                Log::debug('Deadline delivery created Listen to driver unload in destination warehouse: ', [$performanceQuery]);
                break;
            case $event instanceof PayByNicePayDummy:
                $package = $event->package;
                $deadline = Carbon::now()->addHour(2);

                $partnerPickup = $package->picked_up_by->first()->partner;

                $performanceQuery = PartnerPackagePerformance::query()->create([
                    'partner_id' => $partnerPickup->id,
                    'package_id' => $package->id,
                    'deadline' => $deadline,
                    'level' => 0,
                    'status' => 1
                ]);

                Log::debug('Deadline Package Created Listener: ', [$performanceQuery]);
                break;
            case $event instanceof PackageAlreadyPackedByWarehouse:
                $package = $event->package;
                $deadline = Carbon::now()->endOfDay();
                $partnerPickup = $package->picked_up_by->first()->partner;

                $performanceQuery = PartnerPackagePerformance::query()->create([
                    'partner_id' => $partnerPickup->id,
                    'package_id' => $package->id,
                    'deadline' => $deadline,
                    'level' => 1,
                    'status' => 1
                ]);
                break;
        }
    }
}
