<?php

namespace Database\Seeders\Packages\InTransit;

use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Database\Seeders\Packages\PostPayment\ManifestSeeder;
use Database\Seeders\Packages\PostPayment\PackedSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PackageAssignedToManifestSeeder extends Seeder
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

        /** @var Delivery $delivery */
        $deliveries = Delivery::query()->where('status', Delivery::STATUS_WAITING_ASSIGN_PACKAGE)->get();

        $deliveries->each(function (Delivery $delivery) {
            /** @var Partner $partner */
            $partner = $delivery->origin_partner;
            /** @var Delivery $deliveries */
            $deliveries = $partner->deliveries;

            $packages = new Collection();

            $deliveries->each(fn (Delivery $deliveryPick) => $packages->push($deliveryPick->packages->first()));

            $item_codes = new Collection();
            $packages->each(fn (Package $package) => $item_codes->push($package->item_codes->pluck('content')->toArray()));
            $item_codes = $item_codes->flatten()->toArray();

            $inputs = [
                'code' => $item_codes,
                'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
                'role' => UserablePivot::ROLE_WAREHOUSE
            ];

            $job = new ProcessFromCodeToDelivery($delivery, $inputs);
            $this->dispatch($job);
            $delivery = $job->delivery;
        });
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('status', Delivery::STATUS_WAITING_ASSIGN_PACKAGE)->count() === 0) {
            $this->call(ManifestSeeder::class);
        }
    }
}
