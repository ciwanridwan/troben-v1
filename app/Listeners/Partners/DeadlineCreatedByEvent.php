<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\DeliveryCreatedWithDeadline;
use App\Events\Deliveries\DeliveryDooringCreated;
use App\Events\Deliveries\DriverAssigned;
use App\Events\Deliveries\DriverAssignedOfTransit;
use App\Events\Deliveries\PartnerAssigned;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Events\Payment\Nicepay\PayByNicePayDummy;
use App\Events\Payment\Nicepay\PaymentIsCorporateMode;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Performances\Delivery as PartnerDeliveryPerformance;
use App\Models\Partners\Performances\Package as PartnerPackagePerformance;
use App\Models\Partners\Performances\PerformanceModel;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;
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

                # Add performance to child receipt
                if ($package->multiDestination()->exists()) {
                    $childId = $package->multiDestination()->get()->pluck('child_id')->toArray();
                    $packageChild = Package::query()->whereIn('id', $childId)->get();
                    $packageChild->each(function ($r) use ($deadline, $partnerPickup) {
                        PartnerPackagePerformance::query()->create([
                            'partner_id' => $partnerPickup->id,
                            'package_id' => $r->id,
                            'deadline' => $deadline
                        ]);
                    });
                }

                Log::debug('Deadline Payment Has Been Created: ', [$performanceQuery]);
                break;
            case $event instanceof PaymentIsCorporateMode:
                $package = $event->package;
                $partnerPickup = $package->picked_up_by->first()->partner;

                # add performance
                $deadline = Carbon::now() < Carbon::today()->addHours(20) ? Carbon::now()->endOfDay() : Carbon::tomorrow()->endOfDay();
                $performanceQuery = PartnerPackagePerformance::query()->create([
                    'partner_id' => $partnerPickup->id,
                    'package_id' => $package->id,
                    'deadline' => $deadline
                ]);

                Log::debug('Deadline Payment Corporate Has Been Created: ', [$performanceQuery]);
                break;
            case $event instanceof DriverAssigned:
                $delivery = $event->delivery;
                $partnerOrigin = $delivery->partner;

                $startTimeAlert = Carbon::today()->addHours(18);
                $now = Carbon::now();
                if ($now < $startTimeAlert) {
                    break;
                }

                $deadline = $now > $startTimeAlert ? Carbon::now()->endOfDay() : null;
                $performanceDelivery = PartnerDeliveryPerformance::query()->where('partner_id', $partnerOrigin->id)->where('delivery_id', $delivery->id)->first();

                if (!$performanceDelivery || is_null($performanceDelivery)) {
                    $performanceQuery = PartnerDeliveryPerformance::query()->create([
                        'partner_id' => $partnerOrigin->id,
                        'delivery_id' => $delivery->id,
                        'deadline' => $deadline,
                        'level' => 1,
                        'status' => PerformanceModel::STATUS_ON_PROCESS,
                        'type' => PartnerDeliveryPerformance::TYPE_MB_DRIVER_TO_TRANSIT
                    ]);
                } else {
                    break;
                }

                Log::debug('Deadline Driver has been assigned and created: ', [$performanceQuery]);
                break;
            case $event instanceof DriverUnloadedPackageInDestinationWarehouse:
                $delivery = $event->delivery;
                $partnerDestination = $delivery->partner;

                $now = Carbon::now();
                $endTime = Carbon::today()->addHours(18);

                // real first time
                $firstTime = Carbon::today()->addHours(12);

                // for test
                // $firstTime = Carbon::today()->addHours(9);
                if ($now < $firstTime) {
                    Log::info('Deadline not create because outside the specified time is less 12.00 hours');
                    break;
                }

                $deadline = $now < $endTime ? $endTime : null;

                if ($now > $endTime) {
                    Log::info('Deadline not create because outside the specified time is more than 18.00 hours');
                    break;
                }

                $performanceDelivery = PartnerDeliveryPerformance::query()->where('partner_id', $partnerDestination->id)->where('delivery_id', $delivery->id)->first();

                if (!$performanceDelivery || is_null($performanceDelivery)) {
                    $performanceQuery = PartnerDeliveryPerformance::query()->create([
                        'partner_id' => $partnerDestination->id,
                        'delivery_id' => $delivery->id,
                        'deadline' => $deadline,
                        'level' => 1,
                        'type' => PartnerDeliveryPerformance::TYPE_MPW_WAREHOUSE_GOOD_RECEIVE
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
                    'status' => 1,
                    'type' => PartnerDeliveryPerformance::TYPE_MB_WAREHOUSE_PACKING
                ]);

                Log::debug('Deadline Package Created With Dummy Nicepay: ', [$performanceQuery]);
                break;
            case $event instanceof DeliveryCreatedWithDeadline:
                $delivery = $event->delivery;

                $now = Carbon::now();
                $firstTime = Carbon::today()->addHours(12);
                $endTime = Carbon::today()->addHours(18);
                if ($now < $firstTime) {
                    Log::info('Deadline not create because outside the specified time is before 12.00');
                    break;
                }

                $originPartner = $delivery->origin_partner;
                $deadline = $now < $endTime ? $endTime : null;

                if ($now > $endTime) {
                    Log::info('Deadline not create because outside the specified time is more 18.00');
                    break;
                }

                if ($originPartner->type === Partner::TYPE_BUSINESS) {
                    Log::info('Deadline cant create bacause this partner type is business and position in warehouse');
                    break;
                }

                $performanceDelivery = PartnerDeliveryPerformance::query()->create([
                    'partner_id' => $originPartner->id,
                    'delivery_id' => $delivery->id,
                    'deadline' => $deadline,
                    'level' => 1,
                    'status' => 1,
                    'type' => PartnerDeliveryPerformance::TYPE_MPW_WAREHOUSE_REQUEST_TRANSPORTER
                ]);

                Log::debug('Deadline Created With Deadline: ', [$performanceDelivery]);
                break;
            case $event instanceof PartnerAssigned:
                $delivery = $event->delivery;

                $now = Carbon::now();
                $firstTime = Carbon::today()->addHours(12);
                $endTime = Carbon::today()->addHours(18);
                if ($now < $firstTime) {
                    Log::info('Deadline not create because outside the specified time');
                    break;
                }

                $partnerTransporter = $delivery->assigned_to->userable;
                $deadline = $now < $endTime ? $endTime : null;

                if ($now > $endTime) {
                    Log::info('Deadline not create because outside the specified time');
                    break;
                }

                $performanceDelivery = PartnerDeliveryPerformance::query()->create([
                    'partner_id' => $partnerTransporter->id,
                    'delivery_id' => $delivery->id,
                    'deadline' => $deadline,
                    'level' => 1,
                    'status' => 1,
                    'type' => PartnerDeliveryPerformance::TYPE_MTAK_OWNER_TO_DRIVER
                ]);

                Log::debug('Deadline Partner Assigned Created: ', [$performanceDelivery]);
                break;
            case $event instanceof DriverAssignedOfTransit:
                $delivery = $event->delivery;
                $partnerTransporter = $event->delivery->assigned_to;
                $originPartner = $delivery->origin_partner;
                if (!$partnerTransporter instanceof UserablePivot || $partnerTransporter->userable_type !== Transporter::class) {
                    break;
                }
                $now = Carbon::now();
                // $firstTime = Carbon::today()->addHours(18);

                // for test
                $firstTime = Carbon::today()->addHours(9);
                $endTime = Carbon::now()->endOfDay();
                if ($now < $firstTime) {
                    break;
                }

                $deadline = $now < $endTime ? $endTime : null;
                if ($originPartner->type === Partner::TYPE_BUSINESS) {
                    $performanceDelivery = PartnerDeliveryPerformance::create([
                        'partner_id' => $delivery->transporter->partner_id,
                        'delivery_id' => $delivery->id,
                        'deadline' => $endTime,
                        'level' => 1,
                        'status' => 1,
                        'type' => PartnerDeliveryPerformance::TYPE_MB_DRIVER_TO_TRANSIT
                    ]);
                    Log::debug('Deadline Driver MB Assigned Created: ', [$performanceDelivery]);
                } else {
                    $performanceDelivery = PartnerDeliveryPerformance::create([
                        'partner_id' => $delivery->transporter->partner_id,
                        'delivery_id' => $delivery->id,
                        'deadline' => $endTime,
                        'level' => 1,
                        'status' => 1,
                        'type' => PartnerDeliveryPerformance::TYPE_MTAK_DRIVER_TO_WAREHOUSE
                    ]);

                    Log::debug('Deadline Driver MTAK Assigned Created: ', [$performanceDelivery]);
                }
                break;
            case $event instanceof DeliveryDooringCreated:
                $delivery = $event->delivery;
                $now = Carbon::now();
                $firstTime = Carbon::today()->addHours(12);
                $endTime = Carbon::today()->addHours(18);

                if ($now < $firstTime) {
                    break;
                }

                if ($now > $endTime) {
                    Log::info('Deadline cant create because outside the spesicified time');
                }

                $originPartner = $delivery->origin_partner->id;

                $performanceDelivery = PartnerDeliveryPerformance::query()->create([
                    'partner_id' => $originPartner,
                    'delivery_id' => $delivery->id,
                    'deadline' => $endTime,
                    'level' => 1,
                    'status' => 1,
                    'type' => PartnerDeliveryPerformance::TYPE_DRIVER_DOORING
                ]);

                Log::debug('Deadline Delivery Dooring Created: ', [$performanceDelivery]);
                break;
        }
    }
}
