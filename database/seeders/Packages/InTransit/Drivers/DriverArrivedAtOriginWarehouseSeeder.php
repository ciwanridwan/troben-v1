<?php

namespace Database\Seeders\Packages\InTransit\Drivers;

use App\Events\Deliveries\Transit\DriverArrivedAtOriginWarehouse;
use App\Events\Deliveries\Transit\PackageLoadedByDriver;
use App\Models\Deliveries\Delivery;
use Database\Seeders\Packages\InTransit\PartnerAssignedToDeliverySeeder;
use Database\Seeders\Packages\InTransit\RequestPartnerSeeder;
use Database\Seeders\Packages\PostPayment\PackedSeeder;
use Database\Seeders\Packages\PostPayment\PostPaymentSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class DriverArrivedAtOriginWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareDependency();

        /** @var Delivery $deliveries */
        $deliveries = Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_ACCEPTED)->get();

        $deliveries->each(function (Delivery $delivery) {
            Event::listen(PackageLoadedByDriver::class, fn (PackageLoadedByDriver $event) => $this->command->warn('Manifest '.$event->delivery->code->content.' is Unloaded By Driver'));
            event(new DriverArrivedAtOriginWarehouse($delivery));
        });
    }
    private function prepareDependency(): void
    {
        if (Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_ACCEPTED)->count() === 0) {
            PostPaymentSeeder::$CUSTOMER_PACKAGE_ITEM_MAX = 2;
            PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
            $this->call([
                PostPaymentSeeder::class,
                PackedSeeder::class,
                RequestPartnerSeeder::class,
                PartnerAssignedToDeliverySeeder::class,
            ]);
        }
    }
}
