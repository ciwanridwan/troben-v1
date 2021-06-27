<?php

namespace Database\Seeders\Packages\InTransit\Warehouses;

use App\Events\Packages\WarehouseIsStartPacking;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Database\Seeders\Packages\PostPayment\PostPaymentSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class WarehouseIsStartPackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareDependency();
        $packages = Package::query()->where('status', Package::STATUS_WAITING_FOR_PACKING)->get();


        $packages->each(function (Package $package) {
            /** @var Delivery $delivery */
            $delivery = $package->deliveries->first();

            /** @var Partner $partner */
            $partner = $delivery->partner;

            $warehouse = $partner->users()->getQuery()->where('role', UserablePivot::ROLE_WAREHOUSE)->first();

            auth()->setUser($warehouse);

            event(new WarehouseIsStartPacking($package));
        });
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_FINISHED)->count() === 0) {
            $this->call(PostPaymentSeeder::class);
        }
    }
}
