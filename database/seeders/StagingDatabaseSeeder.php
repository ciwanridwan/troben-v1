<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Packages\PackagesTableSeeder;
use Database\Seeders\Packages\AssignedPackagesSeeder;

class StagingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            GeoTableSimpleSeeder::class,
            CustomersTableSeeder::class,
            ServiceTableSeeder::class,
            HandlingSeeder::class,
            PriceTableSimpleSeeder::class,
            ProductsTableSeeder::class,
            PackagesTableSeeder::class,
            AssignedPackagesSeeder::class,
        ]);
    }
}