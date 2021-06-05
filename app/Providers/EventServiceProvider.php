<?php

namespace App\Providers;

use App\Events\Codes\CodeCreated;
use App\Events\CodeScanned;
use Illuminate\Auth\Events\Registered;
use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackageUpdated;
use App\Events\Packages\PackagePaymentVerified;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Listeners\Packages\GeneratePackagePrices;
use App\Events\Packages\PackageApprovedByCustomer;
use App\Events\Packages\PackageAttachedToDelivery;
use App\Events\Deliveries\Pickup as DeliveryPickup;
use App\Events\Deliveries\Transit as DeliveryTransit;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Listeners\Packages\UpdatePackageStatusByEvent;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Listeners\Deliveries\UpdateDeliveryStatusByEvent;
use App\Events\Deliveries\Deliverable\DeliverableItemCodeUpdate;
use App\Events\Packages\PackageCanceledByAdmin;
use App\Listeners\Codes\UpdateOrCreateScannedCode;
use App\Listeners\Codes\WriteCodeLog;
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
            WriteCodeLog::class
        ],
        PackageUpdated::class => [
            GeneratePackagePrices::class,
            WriteCodeLog::class
        ],
        DeliveryPickup\DriverArrivedAtPickupPoint::class => [
            //
        ],
        DeliveryPickup\PackageLoadedByDriver::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        DeliveryPickup\DriverArrivedAtWarehouse::class => [
            //
        ],
        DeliveryPickup\DriverUnloadedPackageInWarehouse::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        DeliveryTransit\DriverArrivedAtPickupPoint::class => [
            //
        ],
        DeliveryTransit\PackageLoadedByDriver::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        DeliveryTransit\DriverArrivedAtWarehouse::class => [
            //
        ],
        DeliveryTransit\DriverUnloadedPackageInDestinationWarehouse::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        WarehouseIsEstimatingPackage::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        PackageEstimatedByWarehouse::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        PackageCanceledByAdmin::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        PackageCheckedByCashier::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        PackageApprovedByCustomer::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        WarehouseIsStartPacking::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],

        PackageAlreadyPackedByWarehouse::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        PackagePaymentVerified::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        PackageAttachedToDelivery::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        DeliverableItemCodeUpdate::class => [
            WriteCodeLog::class
        ],
        CodeCreated::class => [
            // UpdateOrCreateScannedCode::class
        ],
        CodeScanned::class => [
            // UpdateOrCreateScannedCode::class,
            WriteCodeLog::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Package::observe(CodeObserver::class);
    }
}
