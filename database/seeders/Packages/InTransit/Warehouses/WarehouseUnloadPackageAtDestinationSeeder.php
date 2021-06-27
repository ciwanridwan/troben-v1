<?php

namespace Database\Seeders\Packages\InTransit\Warehouses;

use App\Events\Deliveries\Transit\WarehouseUnloadedPackage;
use App\Jobs\Deliveries\Actions\UnloadCodeFromDelivery;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use Database\Seeders\Packages\InTransit\Drivers\DriverLoadPackageSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Event;

class WarehouseUnloadPackageAtDestinationSeeder extends Seeder
{
    use DispatchesJobs;
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

        Event::listen(WarehouseUnloadedPackage::class, fn (WarehouseUnloadedPackage $event) => $this->command->warn('Manifest ID ' . $event->delivery->id . ' Was Unloaded By ' . $event->delivery->partner->name . ' Warehouse'));

        $deliveries->each(function (Delivery $delivery) {
            $job = new UnloadCodeFromDelivery($delivery, ['code' => $delivery->item_codes->pluck('content')->toArray(), 'status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE]);
            $this->dispatch($job);
        });
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_FINISHED)->count() === 0) {
            $this->call(DriverLoadPackageSeeder::class);
        }
    }
}
