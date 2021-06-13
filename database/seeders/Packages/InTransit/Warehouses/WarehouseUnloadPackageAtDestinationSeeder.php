<?php

namespace Database\Seeders\Packages\InTransit\Warehouses;

use App\Events\Deliveries\Transit\WarehouseUnloadedPackage;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class WarehouseUnloadPackageAtDestinationSeeder extends Seeder
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
        $deliveries = Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_FINISHED)->get();

        Event::listen(WarehouseUnloadedPackage::class, fn (WarehouseUnloadedPackage $event) => $this->command->warn('Manifest ID ' . $event->delivery->id . ' Was Unloaded By ' . $event->delivery->partner->name . ' Warehouse deliverable'));

        $deliveries->each(fn (Delivery $delivery) => event(new WarehouseUnloadedPackage($delivery, ['code' => $delivery->item_codes->pluck('content')->toArray()])));
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_FINISHED)->count() === 0) {
            $this->call(DriverLoadPackageSeeder::class);
        }
    }
}
