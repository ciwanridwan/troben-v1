<?php

namespace Database\Seeders\Packages\InTransit\Warehouses;


use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class PackageAlreadyPackedByWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareDependency();
        $packages = Package::query()->where('status', Package::STATUS_PACKING)->get();

        $packages->each(function (Package $package) {
            /** @var Delivery $delivery */
            $delivery = $package->deliveries->first();

            /** @var Partner $partner */
            $partner = $delivery->partner;

            $warehouse = $partner->users()->getQuery()->where('role', UserablePivot::ROLE_WAREHOUSE)->first();

            event(new PackageAlreadyPackedByWarehouse($package));
        });
    }
    private function prepareDependency(): void
    {
        if (Package::query()->where('status', Package::STATUS_PACKING)->count() === 0) {
            $this->call(WarehouseIsStartPackingSeeder::class);
        }
    }
}
