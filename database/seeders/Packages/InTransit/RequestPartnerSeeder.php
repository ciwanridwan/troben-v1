<?php

namespace Database\Seeders\Packages\InTransit;

use App\Jobs\Deliveries\Actions\RequestPartnerToDelivery;
use Illuminate\Database\Seeder;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Bus\DispatchesJobs;

class RequestPartnerSeeder extends Seeder
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
            $job = new RequestPartnerToDelivery($delivery);
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
