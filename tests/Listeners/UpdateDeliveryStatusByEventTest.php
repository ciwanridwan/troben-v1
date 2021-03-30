<?php

namespace Tests\Listeners;

use Tests\TestCase;
use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Listeners\Deliveries\UpdateDeliveryStatusByEvent;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;

class UpdateDeliveryStatusByEventTest extends TestCase
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
        $listener = new UpdateDeliveryStatusByEvent();

        $listener->handle($event);

        $this->assertDatabaseHas('deliveries', [
            'id' => $event->delivery->id,
            'status' => Delivery::STATUS_EN_ROUTE,
        ]);
    }


    public function test_on_event_package_unloaded()
    {
        $event = new DriverUnloadedPackageInWarehouse($this->getDelivery());
        $listener = new UpdateDeliveryStatusByEvent();

        $listener->handle($event);

        $this->assertDatabaseHas('deliveries', [
            'id' => $event->delivery->id,
            'status' => Delivery::STATUS_FINISHED,
        ]);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    private function getDelivery(): Delivery
    {
        return Delivery::query()->whereNotNull('userable_id')->first();
    }
}
