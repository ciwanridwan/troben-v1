<?php

namespace App\Providers;

use App\Events\Codes\CodeCreated;
use App\Events\CodeScanned;
use App\Events\Deliveries\PartnerRequested;
use App\Events\Packages\PartnerAssigned;
use App\Events\Partners\Balance\NewDeliveryHistoryCreated;
use App\Events\Partners\Balance\NewHistoryCreated;
use App\Events\Partners\Balance\WithdrawalConfirmed;
use App\Events\Partners\Balance\WithdrawalRejected;
use App\Events\Partners\Balance\WithdrawalRequested;
use App\Events\Partners\Balance\WithdrawalSuccess;
use App\Events\Partners\PartnerCashierDiscount;
use App\Events\Payment\Nicepay\Registration;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Events\Promo\PromotionClaimed;
use App\Listeners\Packages\GeneratePackagePickupPrices;
use App\Listeners\Partners\GenerateBalanceHistory;
use App\Listeners\Partners\UpdatePartnerBalanceByEvent;
use App\Listeners\Payments\PaymentCreatedByEvent;
use App\Listeners\Payments\UpdatePaymentByEvent;
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
use App\Events\Deliveries\Dooring as DeliveryDooring;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Listeners\Packages\UpdatePackageStatusByEvent;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Listeners\Deliveries\UpdateDeliveryStatusByEvent;
use App\Events\Deliveries\Deliverable\DeliverableItemCodeUpdate;
use App\Events\Deliveries\Transit\WarehouseUnloadedPackage;
use App\Events\Packages\PackageCanceledByAdmin;
use App\Events\Packages\PackageCanceledByCustomer;
use App\Events\Packages\PackageCancelMethodSelected;
use App\Listeners\Codes\WriteCodeLog;
use App\Listeners\Deliveries\CreateDeliveryByEvent;
use App\Listeners\Packages\UpdatePackageTotalWeightByEvent;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Deliveries\DriverAssigned;
use Illuminate\Support\Facades\Event;

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
            UpdatePackageTotalWeightByEvent::class,
            GeneratePackagePickupPrices::class,
            GeneratePackagePrices::class,
            WriteCodeLog::class
        ],
        WalkinPackageCreated::class => [
            UpdatePackageTotalWeightByEvent::class,
            GeneratePackagePrices::class,
            WriteCodeLog::class
        ],
        PackageUpdated::class => [
            UpdatePackageTotalWeightByEvent::class,
            UpdatePackageStatusByEvent::class,
            GeneratePackagePrices::class,
            WriteCodeLog::class
        ],
        PromotionClaimed::class => [
            GeneratePackagePrices::class,
            WriteCodeLog::class
        ],
        PartnerCashierDiscount::class => [
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
            GenerateBalanceHistory::class,
            WriteCodeLog::class
        ],
        DeliveryTransit\DriverArrivedAtOriginWarehouse::class => [
            UpdateDeliveryStatusByEvent::class,
            // UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        DeliveryTransit\PackageLoadedByDriver::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            GenerateBalanceHistory::class,
            WriteCodeLog::class
        ],
        DeliveryTransit\DriverArrivedAtDestinationWarehouse::class => [
            //
        ],
        DeliveryTransit\DriverUnloadedPackageInDestinationWarehouse::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            GenerateBalanceHistory::class,
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
        PackageCanceledByCustomer::class => [
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        PackageCancelMethodSelected::class => [
            CreateDeliveryByEvent::class,
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
        WarehouseUnloadedPackage::class => [
            UpdatePackageStatusByEvent::class,
            UpdateDeliveryStatusByEvent::class,
            WriteCodeLog::class
        ],
        CodeCreated::class => [
            // UpdateOrCreateScannedCode::class
        ],
        CodeScanned::class => [
            // UpdateOrCreateScannedCode::class,
            WriteCodeLog::class
        ],
        DeliveryDooring\DriverArrivedAtOriginPartner::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            WriteCodeLog::class
        ],
        DeliveryDooring\PackageLoadedByDriver::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            GenerateBalanceHistory::class,
            WriteCodeLog::class
        ],
        DeliveryDooring\DriverUnloadedPackageInDooringPoint::class => [
            UpdateDeliveryStatusByEvent::class,
            UpdatePackageStatusByEvent::class,
            GenerateBalanceHistory::class,
            WriteCodeLog::class
        ],
        DriverAssigned::class => [
            PaymentCreatedByEvent::class
        ],
        PartnerRequested::class => [
            //
        ],
        Registration\NewVacctRegistration::class => [
            UpdatePackageStatusByEvent::class,
            PaymentCreatedByEvent::class,
        ],
        Registration\NewQrisRegistration::class => [
            UpdatePackageStatusByEvent::class,
            PaymentCreatedByEvent::class,
        ],
        PayByNicepay::class => [
            UpdatePackageStatusByEvent::class,
            UpdatePaymentByEvent::class,
            WriteCodeLog::class,
        ],
        NewHistoryCreated::class => [
            UpdatePartnerBalanceByEvent::class
        ],
        NewDeliveryHistoryCreated::class => [
            UpdatePartnerBalanceByEvent::class
        ],
        WithdrawalRequested::class => [
            GenerateBalanceHistory::class,
        ],
        WithdrawalConfirmed::class => [
            GenerateBalanceHistory::class,
        ],
        WithdrawalRejected::class => [
            GenerateBalanceHistory::class,
        ],
        WithdrawalSuccess::class => [
            GenerateBalanceHistory::class,
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

        Event::listen(function (PartnerAssigned $event) {
            $event->broadcast();
        });
    }
}
