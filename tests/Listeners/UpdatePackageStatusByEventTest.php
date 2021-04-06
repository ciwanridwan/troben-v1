<?php

namespace Tests\Listeners;

use App\Events\Packages\PackageApprovedByCustomer;
use Database\Seeders\Packages\CustomerInChargeSeeder;
use Tests\TestCase;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use App\Events\Packages\PackageCheckedByCashier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Packages\PackageEstimatedByWarehouse;
use Database\Seeders\Packages\CashierInChargeSeeder;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use App\Listeners\Packages\UpdatePackageStatusByEvent;
use Database\Seeders\Packages\WarehouseInChargeSeeder;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;

class UpdatePackageStatusByEventTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AssignedPackagesSeeder::class);
    }

    public function test_on_event_that_driver_in_charge()
    {
        $event = new PackageLoadedByDriver($this->getDelivery());
        $listener = new UpdatePackageStatusByEvent();

        $listener->handle($event);

        $event->delivery->packages()->cursor()
            ->each(fn (Package $package) => $this->assertDatabaseHas('packages', [
                'id' => $package->id,
                'status' => Package::STATUS_PICKED_UP,
            ]))->each(fn (Package $package) => $this->assertDatabaseHas('delivery_package', [
                'delivery_id' => $event->delivery->id,
                'package_id' => $package->id,
                'is_onboard' => true,
            ]));

        $event = new DriverUnloadedPackageInWarehouse($this->getDelivery());

        $listener->handle($event);

        $event->delivery->packages()->cursor()
            ->each(fn (Package $package) => $this->assertDatabaseHas('packages', [
                'id' => $package->id,
                'status' => Package::STATUS_WAITING_FOR_ESTIMATING,
            ]))->each(fn (Package $package) => $this->assertDatabaseHas('delivery_package', [
                'delivery_id' => $event->delivery->id,
                'package_id' => $package->id,
                'is_onboard' => false,
            ]));
    }

    public function test_on_event_warehouse_estimating_process()
    {
        $this->seed(WarehouseInChargeSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);
        $this->actingAs($user);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_ESTIMATING)->first();

        $listener = new UpdatePackageStatusByEvent();

        $listener->handle(new WarehouseIsEstimatingPackage($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_ESTIMATING,
            'estimator_id' => $user->id,
        ]);

        $listener->handle(new PackageEstimatedByWarehouse($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_ESTIMATED,
        ]);
    }

    public function test_on_cashier_in_charge()
    {
        $this->seed(CashierInChargeSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_CASHIER);
        $this->actingAs($user);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_ESTIMATED)->first();

        $listener = new UpdatePackageStatusByEvent();

        $listener->handle(new PackageCheckedByCashier($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_WAITING_FOR_APPROVAL,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function test_on_customer_in_charge()
    {
        $this->seed(CustomerInChargeSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_APPROVAL)->first();

        $listener = new UpdatePackageStatusByEvent();

        $listener->handle(new PackageApprovedByCustomer($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_ACCEPTED,
            'payment_status' => Package::PAYMENT_STATUS_PENDING,
        ]);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    private function getDelivery(): Delivery
    {
        return Delivery::query()->whereNotNull('userable_id')->first();
    }
}
