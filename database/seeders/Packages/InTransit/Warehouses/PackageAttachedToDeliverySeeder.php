<?php

namespace Database\Seeders\Packages\InTransit\Warehouses;


use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Packages\PackageAttachedToDelivery;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Jobs\Deliveries\CreateNewDelivery;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Event;

class PackageAttachedToDeliverySeeder extends Seeder
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
        $packages = Package::query()->where('status', Package::STATUS_PACKED)->get();

        $packages->each(function (Package $package) {
            /** @var Delivery $delivery */
            $delivery = $package->deliveries->first();

            /** @var Partner $partner */
            $partner = $delivery->partner;

            // $warehouse = $partner->users()->getQuery()->where('role', UserablePivot::ROLE_WAREHOUSE)->first();

            $job = new CreateNewDelivery(['type' => $this->DELIVERY_TYPE, 'status' => Delivery::STATUS_WAITING_ASSIGN_PACKAGE], $partner);
            $this->dispatch($job);
            $deliveryNext = $job->delivery;

            $codes = $package->item_codes()->get()->pluck('content')->toArray();

            $job = new ProcessFromCodeToDelivery($deliveryNext, ['code' => $codes, 'role' => UserablePivot::ROLE_WAREHOUSE, 'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE]);
            $this->dispatch($job);
        });
    }
    private function prepareDependency(): void
    {
        if (Package::query()->where('status', Package::STATUS_PACKED)->count() === 0) {
            $this->call(PackageAlreadyPackedByWarehouseSeeder::class);
        }
    }
}
