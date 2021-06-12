<?php

namespace Database\Seeders\Packages\InTransit;

use App\Jobs\Deliveries\Actions\AssignPartnerToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PartnerAssignedToDeliverySeeder extends Seeder
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

        /** @var Partner $partners */
        $partners = Partner::query()->where('type', Partner::TYPE_TRANSPORTER)->get();

        /** @var Delivery $deliveries */
        $deliveries = Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_WAITING_ASSIGN_PARTNER)->get();

        $deliveries->each(function (Delivery $delivery) use ($partners) {
            $job = new AssignPartnerToDelivery($delivery, $partners->random());
            $this->dispatch($job);
        });
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_WAITING_ASSIGN_PARTNER)->count() === 0) {
            $this->call(RequestPartnerSeeder::class);
        }
    }
}
