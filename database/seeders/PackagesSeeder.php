<?php

namespace Database\Seeders;

use App\Models\Geo\SubDistrict;
use App\Models\Handling;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PackagesSeeder extends Seeder
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

        Package::factory()->count(2)->create()->each(
            fn(Package $package) => Item::factory()->count(random_int(1, 5))->state(['package_id' => $package->id])->create());
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
