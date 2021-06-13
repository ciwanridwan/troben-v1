<?php

namespace Database\Seeders\Packages\InTransit\Drivers;

use App\Events\Deliveries\Transit\DriverArrivedAtDestinationWarehouse;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Models\Deliveries\Delivery;
use Database\Seeders\Packages\InTransit\DriverAssignedSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class DriverArrivedAtDestinationWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareDependency();

        /** @var Delivery $deliveries */
        $deliveries = Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_EN_ROUTE)->get();

        $deliveries->each(function (Delivery $delivery) {
            Event::listen(DriverUnloadedPackageInDestinationWarehouse::class, fn (DriverUnloadedPackageInDestinationWarehouse $event) => $this->command->warn('Driver ' . $event->delivery->driver->name . ' arrived at destination warehouse with Manifest ID ' . $event->delivery->id));
            event(new DriverArrivedAtDestinationWarehouse($delivery));
            event(new DriverUnloadedPackageInDestinationWarehouse($delivery));
        });
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_EN_ROUTE)->count() === 0) {
            $this->call(DriverLoadPackageSeeder::class);
        }
    }
}
