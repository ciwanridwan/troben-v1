<?php

namespace Database\Seeders\Packages\InTransit\Warehouses;

use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Bus\DispatchesJobs;

class AssignDriverToDeliverySeeder extends Seeder
{
    use DispatchesJobs;
    public string $DELIVERY_TYPE = Delivery::TYPE_TRANSIT;
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

            $method = 'partner';

            $job = new AssignDriverToDelivery($delivery, $transporter->pivot, $method);
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
