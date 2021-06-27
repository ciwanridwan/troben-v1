<?php

namespace Database\Seeders\Packages\InTransit\Warehouses;


use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Packages\PackageAttachedToDelivery;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Deliveries\CreateNewDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;
use App\Models\User;
use Database\Seeders\TransportersTableSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Event;

class AssignDriverToDeliverySeeder extends Seeder
{
    public string $DELIVERY_TYPE = Delivery::TYPE_TRANSIT;
    use DispatchesJobs;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareDependency();
        $deliveries = Delivery::query()->where('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER)->get();

        $deliveries->each(function (Delivery $delivery) {

            /** @var Partner $partner */
            $partner = $delivery->partner;

            /** @var User $driver */
            $driver = $partner->drivers->first();

            /** @var Transporter $transporter */
            $transporter = $driver->transporters->first();

            $job = new AssignDriverToDelivery($delivery, $transporter->pivot);
            $this->dispatch($job);
        });
    }
    private function prepareDependency(): void
    {
        if (Package::query()->where('status', Package::STATUS_PACKED)->count() === 0) {
            $this->call(PackageAttachedToDeliverySeeder::class);
        }
    }
}
