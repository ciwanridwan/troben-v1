<?php

namespace Tests\Listeners;

use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Listeners\Packages\UpdatePackageStatusByEvent;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\FinishedDeliveriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePackageStatusByEventTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AssignedPackagesSeeder::class);
    }

    public function test_on_event_package_loaded()
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
    }

    public function test_on_event_package_unload()
    {
        $event = new DriverUnloadedPackageInWarehouse($this->getDelivery());
        $listener = new UpdatePackageStatusByEvent();

        $listener->handle($event);

        $event->delivery->packages()->cursor()
            ->each(fn (Package $package) => $this->assertDatabaseHas('packages', [
                'id' => $package->id,
                'status' => Package::STATUS_ESTIMATING,
            ]))->each(fn (Package $package) => $this->assertDatabaseHas('delivery_package', [
                'delivery_id' => $event->delivery->id,
                'package_id' => $package->id,
                'is_onboard' => false,
            ]));
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    private function getDelivery(): Delivery
    {
        return Delivery::query()->whereNotNull('userable_id')->first();
    }

    public function test_on_event_package_estimated_by_warehouse()
    {
        $this->seed(FinishedDeliveriesSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_ESTIMATING)->first();

        $event = new PackageEstimatedByWarehouse($package);
        $listener = new UpdatePackageStatusByEvent();

        $listener->handle($event);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_ESTIMATED,
        ]);
    }
}
