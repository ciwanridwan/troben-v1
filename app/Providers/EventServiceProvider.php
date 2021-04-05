<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackageUpdated;
use App\Events\Packages\PackageCheckedByCashier;
use App\Listeners\Packages\GeneratePackagePrices;
use App\Events\Deliveries\Pickup as DeliveryPickup;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Listeners\Packages\UpdatePackageStatusByEvent;
use App\Listeners\Deliveries\UpdateDeliveryStatusByEvent;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PackageCreated::class => [
            GeneratePackagePrices::class,
        ],
        PackageUpdated::class => [
            GeneratePackagePrices::class,
        ],
        DeliveryPickup\DriverArrivedAtPickupPoint::class => [
            //
        ],
        DeliveryPickup\PackageLoadedByDriver::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
        ],
        DeliveryPickup\DriverArrivedAtWarehouse::class => [
            //
        ],
        DeliveryPickup\DriverUnloadedPackageInWarehouse::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
        ],
        WarehouseIsEstimatingPackage::class => [
            UpdatePackageStatusByEvent::class,
        ],
        PackageEstimatedByWarehouse::class => [
            UpdatePackageStatusByEvent::class,
        ],
        PackageCheckedByCashier::class => [
            UpdatePackageStatusByEvent::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
