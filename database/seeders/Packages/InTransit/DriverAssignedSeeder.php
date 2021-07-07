<?php

namespace Database\Seeders\Packages\InTransit;

use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DriverAssignedSeeder extends Seeder
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
        $deliveries = Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER)->get();

        $deliveries->each(function (Delivery $delivery) {
            /** @var Partner $partner */
            $partner = $delivery->origin_partner;

            /** @var User $driver */
            $driver = $partner->drivers->random();

            /** @var Transporter $transporter */
            $transporter = $driver->transporters->random();

            $job = new AssignDriverToDelivery($delivery, $transporter->pivot);
            $this->dispatch($job);
        });
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER)->count() === 0) {
            $this->call(PackageAssignedToManifestSeeder::class);
        }
    }
}
