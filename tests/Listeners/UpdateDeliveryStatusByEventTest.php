<?php

namespace Tests\Listeners;

use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Listeners\Deliveries\UpdateDeliveryStatusByEvent;
use App\Models\Deliveries\Delivery;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateDeliveryStatusByEventTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AssignedPackagesSeeder::class);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    private function getDelivery(): Delivery
    {
        return Delivery::query()->whereNotNull('userable_id')->first();
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
}
