<?php

namespace Tests\Listeners;

use Tests\TestCase;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Validation\ValidationException;
use App\Events\Packages\PackagePaymentVerified;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Packages\WarehouseIsStartPacking;
use Database\Seeders\Packages\PostPaymentSeeder;
use App\Events\Packages\PackageApprovedByCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Packages\PackageEstimatedByWarehouse;
use Database\Seeders\Packages\CashierInChargeSeeder;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\CustomerInChargeSeeder;
use App\Listeners\Packages\UpdatePackageStatusByEvent;
use Database\Seeders\Packages\WarehouseInChargeSeeder;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Packages\PackageCanceledByAdmin;
use App\Events\Packages\PackageCanceledByCustomer;

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

        $this->runListener($event);

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

        $this->runListener($event);

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

        $this->runListener(new WarehouseIsEstimatingPackage($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_ESTIMATING,
            'estimator_id' => $user->id,
        ]);

        $this->runListener(new PackageEstimatedByWarehouse($package));
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

        $this->runListener(new PackageCheckedByCashier($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_WAITING_FOR_APPROVAL,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function test_on_admin_cancel()
    {
        $this->seed(CustomerInChargeSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_APPROVAL)->first();

        $this->runListener(new PackageCanceledByAdmin($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_CANCEL,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function test_on_customer_cancel()
    {
        $this->seed(CustomerInChargeSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_APPROVAL)->first();

        $this->runListener(new PackageCanceledByCustomer($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_CANCEL,
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

        $this->runListener(new PackageApprovedByCustomer($package));
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_ACCEPTED,
            'payment_status' => Package::PAYMENT_STATUS_PENDING,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function test_on_admin_confirm_payment()
    {
        $this->seed(CustomerInChargeSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_APPROVAL)->first();

        $package->setAttribute('status', Package::STATUS_ACCEPTED)->save();

        try {
            $this->runListener(new PackagePaymentVerified($package));
        } catch (ValidationException $e) {
            $this->assertTrue($e instanceof ValidationException);
        }

        $package
            ->setAttribute('status', Package::STATUS_ACCEPTED)
            ->setAttribute('payment_status', Package::PAYMENT_STATUS_PENDING)
            ->save();

        $this->runListener(new PackagePaymentVerified($package));

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_WAITING_FOR_PACKING,
            'payment_status' => Package::PAYMENT_STATUS_PAID,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function test_event_warehouse_packing_a_package()
    {
        $this->seed(PostPaymentSeeder::class);

        /** @var Package $package */
        $package = Package::query()
            ->where('status', Package::STATUS_WAITING_FOR_PACKING)
            ->where('payment_status', Package::PAYMENT_STATUS_PAID)
            ->first();

        $user = $package
            ->deliveries()
            ->first()
            ->partner->users()->wherePivot('role', UserablePivot::ROLE_WAREHOUSE)->first();

        $this->actingAs($user);

        $this->runListener(new WarehouseIsStartPacking($package));

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_PACKING,
            'packager_id' => $user->id,
        ]);

        $this->runListener(new PackageAlreadyPackedByWarehouse($package));

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_PACKED,
        ]);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    private function getDelivery(): Delivery
    {
        return Delivery::query()->whereNotNull('userable_id')->first();
    }

    private function runListener(object $event): void
    {
        $listener = new UpdatePackageStatusByEvent();

        $listener->handle($event);
    }
}
