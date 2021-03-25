<?php

namespace Database\Seeders\Packages;

use App\Models\Service;
use App\Models\Handling;
use App\Models\Packages\Item;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Seeder;
use App\Models\Packages\Package;
use App\Models\Customers\Customer;
use Database\Seeders\HandlingSeeder;
use Database\Seeders\ServiceTableSeeder;
use Database\Seeders\GeoTableSimpleSeeder;

class PackagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $this->checkOrSeedDependenciesData();
        $this->command->info('=> create order for each customer.');

        Customer::query()->get()->each(
            fn (Customer $customer) => Package::factory()->count(2)
                ->state([
                    'customer_id' => $customer->id,
                    'sender_name' => $customer->name,
                ])->create()->each(
                fn (Package $package) => Item::factory()->count(random_int(1, 5))
                    ->state(['package_id' => $package->id])->create()))
            ->each(fn (Customer $customer) => $this->command->warn('=> 2 order created for customer : '.$customer->name));
    }

    private function checkOrSeedDependenciesData()
    {
        if (SubDistrict::query()->count() === 0) {
            $this->call(GeoTableSimpleSeeder::class);
        }

        if (Service::query()->count() === 0) {
            $this->call(ServiceTableSeeder::class);
        }

        if (Handling::query()->count() === 0) {
            $this->call(HandlingSeeder::class);
        }
    }
}
