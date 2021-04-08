<?php

namespace Database\Seeders\Packages;

use App\Events\Packages\PackageCreated;
use App\Models\Service;
use App\Models\Packages\Item;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Seeder;
use App\Models\Packages\Package;
use App\Models\Customers\Customer;
use App\Models\Partners\Transporter;
use Database\Seeders\ServiceTableSeeder;
use Database\Seeders\GeoTableSimpleSeeder;

class PackagesTableSeeder extends Seeder
{
    public static int $CUSTOMER_PACKAGES = 2;

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
            fn (Customer $customer) => Package::factory()
                ->count(self::$CUSTOMER_PACKAGES)
                ->state([
                    'customer_id' => $customer->id,
                    'sender_name' => $customer->name,
                    'service_code' => Service::TRAWLPACK_STANDARD,
                    'transporter_type' => Transporter::TYPE_BIKE,
                ])
                ->create()
                ->each(fn (Package $package) => Item::factory()->state(['package_id' => $package->id])->create())
                ->each(fn (Package $package) => event(new PackageCreated($package))))
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
    }
}
